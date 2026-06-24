<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\Food;
use App\Models\Meal;
use App\Models\MealLog;
use App\Models\NutritionScanLog;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class NutritionController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $user = Auth::user();
        $profile = $user->profile;

        $foods = Food::orderBy('name')->get();

        $scanLogs = NutritionScanLog::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $meals = Meal::with(['items.food'])
            ->where('user_id', $user->id)
            ->where('meal_date', $today)
            ->latest()
            ->get();

        $mealLogs = MealLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->get();

        $totals = [
            'protein' => $mealLogs->sum('protein'),
            'carbs' => $mealLogs->sum('carbs'),
            'fat' => $mealLogs->sum('fat'),
            'calories' => $mealLogs->sum('calories'),
        ];

        $metrics = BodyMetrics::for($profile);
        $nutritionGoals = $metrics['nutrition_goals'];
        $mealPlanByType = $this->mealPlanByType((int) $metrics['meals_per_day'], $nutritionGoals, $mealLogs);

        return view('nutrition.index', compact(
            'foods',
            'meals',
            'scanLogs',
            'totals',
            'nutritionGoals',
            'metrics',
            'mealPlanByType'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,snack,dinner,supper',
            'foods' => 'required|array|min:1',
            'foods.*.food_id' => 'required|exists:foods,id',
            'foods.*.quantity' => 'required|numeric|min:0.1',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('meal-photos', 'public');
        }

        $meal = Meal::create([
            'user_id' => Auth::id(),
            'meal_type' => $validated['meal_type'],
            'meal_date' => now()->toDateString(),
            'photo_path' => $photoPath,
        ]);

        foreach ($validated['foods'] as $foodItem) {
            $food = Food::findOrFail($foodItem['food_id']);
            $quantityInGrams = $foodItem['quantity'];

            if ($food->unit_type === 'unit') {
                $quantityInGrams = $foodItem['quantity'] * $food->grams_per_unit;
            }

            $factor = $quantityInGrams / 100;

            MealLog::create([
                'user_id' => Auth::id(),
                'meal_id' => $meal->id,
                'food_id' => $food->id,
                'quantity' => $foodItem['quantity'],
                'meal_type' => $validated['meal_type'],
                'protein' => $food->protein_per_100g * $factor,
                'carbs' => $food->carbs_per_100g * $factor,
                'fat' => $food->fat_per_100g * $factor,
                'calories' => $food->calories_per_100g * $factor,
                'meal_date' => now()->toDateString(),
                'photo_path' => $photoPath,
            ]);
        }

        return redirect()->route('nutrition.index')
            ->with('success', 'Refeição registrada com sucesso!');
    }

    public function destroy(Meal $meal)
    {
        if ($meal->user_id !== Auth::id()) {
            abort(403);
        }

        $meal->delete();

        return redirect()->route('nutrition.index')
            ->with('success', 'Refeição removida com sucesso!');
    }

    public function scanLabel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label_photo' => 'required|image|max:1100',
        ]);

        $path = $request->file('label_photo')->store('nutrition-labels', 'public');
        $absolutePath = Storage::disk('public')->path($path);

        $process = new Process([
            config('services.tesseract.path'),
            $absolutePath,
            'stdout',
            '-l',
            'por+eng',
            '--psm',
            '4',
        ]);

        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Não foi possível ler a imagem. Verifique se o Tesseract está instalado corretamente.',
                ], 422);
            }

            return back()->withErrors([
                'label_photo' => 'Não foi possível ler a imagem. Verifique se o Tesseract está instalado corretamente.',
            ]);
        }

        $text = $process->getOutput();
        $parsed = $this->parseNutritionLabelText($text);
        $scannedFood = [
            'name' => $validated['name'],
            'label_photo_path' => $path,
            'raw_text' => $text,
            ...$parsed,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'food' => $scannedFood,
            ]);
        }

        return back()->with([
            'scanned_food' => $scannedFood,
        ]);
    }

    public function storeScannedFood(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serving_size' => 'required|numeric|min:1',
            'calories' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'raw_text' => 'nullable|string',
            'label_photo_path' => 'nullable|string|max:255',
        ]);

        $factor = 100 / $validated['serving_size'];

        $food = Food::updateOrCreate(
            [
                'name' => $validated['name'],
                'user_id' => Auth::id(),
            ],
            [
                'source' => 'ocr',
                'label_photo_path' => $validated['label_photo_path'] ?? null,
                'calories_per_100g' => round($validated['calories'] * $factor, 2),
                'protein_per_100g' => round($validated['protein'] * $factor, 2),
                'carbs_per_100g' => round($validated['carbs'] * $factor, 2),
                'fat_per_100g' => round($validated['fat'] * $factor, 2),
                'unit_type' => 'grams',
                'grams_per_unit' => null,
            ]
        );

        $scanLog = NutritionScanLog::create([
            'user_id' => Auth::id(),
            'food_id' => $food->id,
            'food_name' => $validated['name'],
            'label_photo_path' => $validated['label_photo_path'] ?? null,
            'serving_size' => $validated['serving_size'],
            'calories' => $validated['calories'],
            'protein' => $validated['protein'],
            'carbs' => $validated['carbs'],
            'fat' => $validated['fat'],
            'calories_per_100g' => round($validated['calories'] * $factor, 2),
            'protein_per_100g' => round($validated['protein'] * $factor, 2),
            'carbs_per_100g' => round($validated['carbs'] * $factor, 2),
            'fat_per_100g' => round($validated['fat'] * $factor, 2),
            'raw_text' => $validated['raw_text'] ?? null,
        ]);

        $this->notifyUser(
            'Alimento cadastrado',
            "{$validated['name']} foi salvo na sua lista de alimentos.",
            'bi-check-circle',
            route('foods.mine')
        );

        if ($request->expectsJson()) {
            $unreadNotifications = UserNotification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'message' => 'Alimento cadastrado com sucesso!',
                'food' => $this->formatFood($food, 'Local'),
                'unread_notifications' => $unreadNotifications,
                'notification' => [
                    'title' => 'Alimento cadastrado',
                    'message' => "{$validated['name']} foi salvo na sua lista de alimentos.",
                    'icon' => 'bi-check-circle',
                    'link_url' => route('foods.mine'),
                ],
                'scan_log' => [
                    'food_name' => $scanLog->food_name,
                    'serving_size' => (float) $scanLog->serving_size,
                    'calories' => (float) $scanLog->calories,
                    'protein' => (float) $scanLog->protein,
                    'carbs' => (float) $scanLog->carbs,
                    'fat' => (float) $scanLog->fat,
                    'calories_per_100g' => (float) $scanLog->calories_per_100g,
                    'created_at' => $scanLog->created_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return redirect()->route('nutrition.index')
            ->with('success', 'Alimento cadastrado com sucesso!');
    }

    public function searchFoods(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $foods = collect()
            ->merge($this->searchLocalFoods($query))
            ->merge($this->searchUsdaFoods($query))
            ->merge($this->searchOpenFoodFacts($query));

        $foods = $foods
            ->filter(fn ($food) => !empty($food['name']))
            ->unique(fn ($food) => Str::lower($food['name']))
            ->take(20)
            ->values();

        return response()->json($foods);
    }

    private function parseNutritionLabelText(string $text): array
    {
        $normalized = $this->normalizeOcrText($text);

        $servingSize = $this->extractNumber($normalized, [
            '/porcao\s*:?\s*([\d,.]+)\s*(?:g|ml|mi)/u',
            '/([\d,.]+)\s*(?:g|ml|mi)\s*\(/u',
        ], 100);

        return [
            'serving_size' => $servingSize,
            'calories' => $this->extractNutrientFromLines($normalized, ['valor energetico', 'calorias', 'energia'], $servingSize, false),
            'protein' => $this->extractNutrientFromLines($normalized, ['proteinas', 'proteina', 'protein'], $servingSize),
            'carbs' => $this->extractNutrientFromLines($normalized, ['carboidratos', 'carboidrato', 'carbohydrate'], $servingSize),
            'fat' => $this->extractNutrientFromLines($normalized, ['gorduras totais', 'gordura total', 'total fat'], $servingSize),
        ];
    }

    private function normalizeOcrText(string $text): string
    {
        $text = mb_strtolower($text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $text = strtr($text, [
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'é' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ]);

        return preg_replace('/[ \t]+/', ' ', $text);
    }

    private function extractNutrientFromLines(string $text, array $keywords, float $servingSize, bool $isMacro = true): float
    {
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $hasKeyword = false;

            foreach ($keywords as $keyword) {
                if (str_contains($line, $keyword)) {
                    $hasKeyword = true;
                    break;
                }
            }

            if (!$hasKeyword) {
                continue;
            }

            $numbers = $this->extractNumbersFromLine($line);

            if (empty($numbers)) {
                continue;
            }

            $value = count($numbers) >= 2 ? $numbers[1] : $numbers[0];
            $firstColumnValue = $numbers[0];
            $dailyValue = count($numbers) >= 3 ? end($numbers) : null;

            if (!$isMacro) {
                return $value;
            }

            $expectedFromFirstColumn = $servingSize > 0
                ? $firstColumnValue * ($servingSize / 100)
                : null;

            if ($expectedFromFirstColumn && $value > 100 && $firstColumnValue > 0) {
                return round($expectedFromFirstColumn, 2);
            }

            if ($expectedFromFirstColumn && $firstColumnValue < 20 && $value > ($expectedFromFirstColumn * 3)) {
                return round($expectedFromFirstColumn, 2);
            }

            if ($dailyValue !== null && $dailyValue <= 30 && $value > ($dailyValue * 2.5) && $value < 100) {
                return round($value / 10, 2);
            }

            return $value;
        }

        return 0;
    }

    private function extractNumbersFromLine(string $line): array
    {
        preg_match_all('/(?<!\d)\d+(?:[,.]\d+)?(?!\d)/u', $line, $matches);

        return collect($matches[0])
            ->map(fn ($number) => (float) str_replace(',', '.', $number))
            ->filter(fn ($number) => $number >= 0)
            ->values()
            ->all();
    }

    private function extractNumber(string $text, array $patterns, float $default = 0): float
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return (float) str_replace(',', '.', $matches[1]);
            }
        }

        return $default;
    }

    private function searchLocalFoods(string $query)
    {
        return Food::where('name', 'like', "%{$query}%")
            ->where(function ($queryBuilder) {
                $queryBuilder->whereNull('user_id')
                    ->orWhere('user_id', Auth::id());
            })
            ->limit(10)
            ->get()
            ->map(fn ($food) => $this->formatFood($food, 'Local'));
    }

    private function searchUsdaFoods(string $query)
    {
        $apiKey = config('services.usda.fdc_api_key');

        if (!$apiKey) {
            return collect();
        }

        $translatedQuery = $this->translateFoodQuery($query);

        try {
            $response = Http::timeout(8)->get('https://api.nal.usda.gov/fdc/v1/foods/search', [
                'api_key' => $apiKey,
                'query' => $translatedQuery,
                'pageSize' => 10,
                'dataType' => [
                    'Foundation',
                    'SR Legacy',
                    'Survey (FNDDS)',
                ],
            ]);
        } catch (\Throwable $e) {
            return collect();
        }

        if (!$response->successful()) {
            return collect();
        }

        return collect($response->json('foods', []))
            ->map(function ($item) {
                $nutrients = collect($item['foodNutrients'] ?? []);

                $protein = $this->nutrientValue($nutrients, ['Protein']);
                $carbs = $this->nutrientValue($nutrients, ['Carbohydrate, by difference']);
                $fat = $this->nutrientValue($nutrients, ['Total lipid (fat)']);
                $calories = $this->nutrientValue($nutrients, ['Energy'], ['KCAL', 'kcal']);

                if ($calories <= 0) {
                    return null;
                }

                $name = Str::title(Str::lower($item['description'] ?? ''));

                $food = Food::firstOrCreate(
                    ['name' => $name],
                    [
                        'protein_per_100g' => $protein,
                        'carbs_per_100g' => $carbs,
                        'fat_per_100g' => $fat,
                        'calories_per_100g' => $calories,
                        'unit_type' => 'grams',
                        'grams_per_unit' => null,
                    ]
                );

                return $this->formatFood($food, 'USDA');
            })
            ->filter()
            ->values();
    }

    private function searchOpenFoodFacts(string $query)
    {
        try {
            $response = Http::timeout(8)->get('https://world.openfoodfacts.org/cgi/search.pl', [
                'search_terms' => $query,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => 8,
                'fields' => 'product_name,brands,nutriments',
            ]);
        } catch (\Throwable $e) {
            return collect();
        }

        if (!$response->successful()) {
            return collect();
        }

        return collect($response->json('products', []))
            ->filter(function ($product) {
                $nutriments = $product['nutriments'] ?? [];

                return !empty($product['product_name'])
                    && isset($nutriments['energy-kcal_100g']);
            })
            ->map(function ($product) {
                $nutriments = $product['nutriments'] ?? [];
                $brand = trim($product['brands'] ?? '');
                $productName = html_entity_decode(trim($product['product_name']), ENT_QUOTES, 'UTF-8');
                $name = $brand ? "{$productName} - {$brand}" : $productName;

                $food = Food::firstOrCreate(
                    ['name' => $name],
                    [
                        'protein_per_100g' => $nutriments['proteins_100g'] ?? 0,
                        'carbs_per_100g' => $nutriments['carbohydrates_100g'] ?? 0,
                        'fat_per_100g' => $nutriments['fat_100g'] ?? 0,
                        'calories_per_100g' => $nutriments['energy-kcal_100g'] ?? 0,
                        'unit_type' => 'grams',
                        'grams_per_unit' => null,
                    ]
                );

                return $this->formatFood($food, 'Open Food Facts');
            })
            ->values();
    }

    private function nutrientValue($nutrients, array $names, array $units = []): float
    {
        $nutrient = $nutrients->first(function ($nutrient) use ($names, $units) {
            $name = $nutrient['nutrientName'] ?? '';
            $unit = $nutrient['unitName'] ?? '';

            $nameMatches = in_array($name, $names, true);
            $unitMatches = empty($units) || in_array($unit, $units, true);

            return $nameMatches && $unitMatches;
        });

        return (float) ($nutrient['value'] ?? 0);
    }

    private function formatFood(Food $food, string $source): array
    {
        return [
            'id' => $food->id,
            'name' => $food->name,
            'source' => $source,
            'protein_per_100g' => $food->protein_per_100g,
            'carbs_per_100g' => $food->carbs_per_100g,
            'fat_per_100g' => $food->fat_per_100g,
            'calories_per_100g' => $food->calories_per_100g,
            'unit_type' => $food->unit_type,
            'grams_per_unit' => $food->grams_per_unit,
        ];
    }

    private function translateFoodQuery(string $query): string
    {
        $dictionary = [
            'arroz' => 'rice',
            'feijao' => 'beans',
            'feijão' => 'beans',
            'frango' => 'chicken',
            'peito de frango' => 'chicken breast',
            'carne' => 'beef',
            'patinho' => 'beef lean',
            'ovo' => 'egg',
            'ovos' => 'eggs',
            'banana' => 'banana',
            'maca' => 'apple',
            'maçã' => 'apple',
            'batata' => 'potato',
            'batata doce' => 'sweet potato',
            'aveia' => 'oats',
            'leite' => 'milk',
            'queijo' => 'cheese',
            'iogurte' => 'yogurt',
            'pao' => 'bread',
            'pão' => 'bread',
            'macarrao' => 'pasta',
            'macarrão' => 'pasta',
            'salmao' => 'salmon',
            'salmão' => 'salmon',
            'atum' => 'tuna',
            'tilapia' => 'tilapia',
            'brócolis' => 'broccoli',
            'brocolis' => 'broccoli',
        ];

        $normalized = Str::lower(trim($query));

        return $dictionary[$normalized] ?? $query;
    }

    private function notifyUser(string $title, string $message, string $icon = 'bi-bell', ?string $linkUrl = null): void
    {
        UserNotification::create([
            'user_id' => Auth::id(),
            'title' => $title,
            'message' => $message,
            'type' => 'success',
            'icon' => $icon,
            'link_url' => $linkUrl,
        ]);
    }

    private function mealPlanByType(int $mealsPerDay, array $nutritionGoals, $mealLogs): array
    {
        $mealTypes = match (true) {
            $mealsPerDay <= 3 => [
                ['type' => 'breakfast', 'label' => 'Cafe da manha', 'weight' => .25],
                ['type' => 'lunch', 'label' => 'Almoco', 'weight' => .40],
                ['type' => 'dinner', 'label' => 'Jantar', 'weight' => .35],
            ],
            $mealsPerDay === 4 => [
                ['type' => 'breakfast', 'label' => 'Cafe da manha', 'weight' => .22],
                ['type' => 'lunch', 'label' => 'Almoco', 'weight' => .35],
                ['type' => 'snack', 'label' => 'Lanche', 'weight' => .16],
                ['type' => 'dinner', 'label' => 'Jantar', 'weight' => .27],
            ],
            default => [
                ['type' => 'breakfast', 'label' => 'Cafe da manha', 'weight' => .20],
                ['type' => 'lunch', 'label' => 'Almoco', 'weight' => .32],
                ['type' => 'snack', 'label' => 'Lanche', 'weight' => .14],
                ['type' => 'dinner', 'label' => 'Jantar', 'weight' => .24],
                ['type' => 'supper', 'label' => 'Ceia', 'weight' => .10],
            ],
        };

        $fallbackWeight = 1 / max(1, $mealsPerDay);
        $allMealTypes = [
            'breakfast' => 'Cafe da manha',
            'lunch' => 'Almoco',
            'snack' => 'Lanche',
            'dinner' => 'Jantar',
            'supper' => 'Ceia',
        ];
        $plannedTypes = collect($mealTypes)->pluck('type')->all();

        foreach ($allMealTypes as $type => $label) {
            if (!in_array($type, $plannedTypes, true)) {
                $mealTypes[] = [
                    'type' => $type,
                    'label' => $label,
                    'weight' => $fallbackWeight,
                ];
            }
        }

        $logsByType = $mealLogs->groupBy('meal_type');

        return collect($mealTypes)->mapWithKeys(function ($meal) use ($nutritionGoals, $logsByType) {
            $registered = $logsByType->get($meal['type'], collect());
            $target = [
                'protein' => round($nutritionGoals['protein'] * $meal['weight']),
                'carbs' => round($nutritionGoals['carbs'] * $meal['weight']),
                'fat' => round($nutritionGoals['fat'] * $meal['weight']),
                'calories' => round($nutritionGoals['calories'] * $meal['weight']),
            ];
            $current = [
                'protein' => (float) $registered->sum('protein'),
                'carbs' => (float) $registered->sum('carbs'),
                'fat' => (float) $registered->sum('fat'),
                'calories' => (float) $registered->sum('calories'),
            ];

            return [
                $meal['type'] => [
                    'label' => $meal['label'],
                    'target' => $target,
                    'current' => $current,
                ],
            ];
        })->all();
    }
}
