<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Meal;
use App\Models\MealLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class NutritionController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $user = Auth::user();
        $profile = $user->profile;

        $foods = Food::orderBy('name')->get();

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

        $currentWeight = $profile?->current_weight ?? 0;

        $nutritionGoals = [
            'protein' => $currentWeight > 0 ? round($currentWeight * 2.0) : 180,
            'carbs' => $currentWeight > 0 ? round($currentWeight * 2.5) : 250,
            'fat' => $currentWeight > 0 ? round($currentWeight * 0.8) : 80,
            'calories' => $currentWeight > 0 ? round($currentWeight * 25) : 2500,
        ];

        return view('nutrition.index', compact(
            'foods',
            'meals',
            'totals',
            'nutritionGoals'
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

    public function searchFoods(Request $request)
{
    $query = trim($request->get('q', ''));

    if (strlen($query) < 2) {
        return response()->json([]);
    }

    $localFoods = Food::where('name', 'like', "%{$query}%")
        ->limit(10)
        ->get();

    if ($localFoods->count() > 0) {
        return response()->json($localFoods->map(function ($food) {
            return [
                'id' => $food->id,
                'name' => $food->name,
                'protein_per_100g' => $food->protein_per_100g,
                'carbs_per_100g' => $food->carbs_per_100g,
                'fat_per_100g' => $food->fat_per_100g,
                'calories_per_100g' => $food->calories_per_100g,
                'unit_type' => $food->unit_type,
                'grams_per_unit' => $food->grams_per_unit,
            ];
        }));
    }

   try {
    $response = Http::withoutVerifying()
        ->timeout(8)
        ->get('https://world.openfoodfacts.org/cgi/search.pl', [
            'search_terms' => $query,
            'search_simple' => 1,
            'action' => 'process',
            'json' => 1,
            'page_size' => 5,
            'fields' => 'product_name,nutriments',
        ]);
} catch (\Exception $e) {
    return response()->json([]);
}

    if (!$response->successful()) {
        return response()->json([]);
    }

    $products = collect($response->json('products', []));

    $createdFoods = $products
    ->filter(function ($product) {
        $nutriments = $product['nutriments'] ?? [];

        return !empty($product['product_name'])
            && isset($nutriments['energy-kcal_100g']);
    })
    ->map(function ($product) {
        $nutriments = $product['nutriments'] ?? [];

        $name = html_entity_decode(trim($product['product_name']), ENT_QUOTES, 'UTF-8');

        return Food::firstOrCreate(
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
    })
    ->take(5)
    ->values();

    return response()->json($createdFoods->map(function ($food) {
        return [
            'id' => $food->id,
            'name' => $food->name,
            'protein_per_100g' => $food->protein_per_100g,
            'carbs_per_100g' => $food->carbs_per_100g,
            'fat_per_100g' => $food->fat_per_100g,
            'calories_per_100g' => $food->calories_per_100g,
            'unit_type' => $food->unit_type,
            'grams_per_unit' => $food->grams_per_unit,
        ];
    }));
}
}
