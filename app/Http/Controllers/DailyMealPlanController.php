<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\MealLog;
use App\Models\WeightLog;
use Illuminate\Support\Facades\Auth;

class DailyMealPlanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $today = now()->toDateString();

        $latestWeight = WeightLog::where('user_id', $user->id)
            ->latest('recorded_at')
            ->value('weight');

        $metrics = BodyMetrics::for($profile, (float) ($latestWeight ?? 80));
        $nutritionGoals = $metrics['nutrition_goals'];
        $mealTypes = $this->mealTypes((int) $metrics['meals_per_day']);

        $todayByMeal = MealLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->selectRaw('meal_type, COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->groupBy('meal_type')
            ->get()
            ->keyBy('meal_type');

        $mealPlan = collect($mealTypes)->map(function ($meal) use ($nutritionGoals, $todayByMeal) {
            $registered = $todayByMeal->get($meal['type']);
            $target = [
                'protein' => round($nutritionGoals['protein'] * $meal['weight']),
                'carbs' => round($nutritionGoals['carbs'] * $meal['weight']),
                'fat' => round($nutritionGoals['fat'] * $meal['weight']),
                'calories' => round($nutritionGoals['calories'] * $meal['weight']),
            ];

            $current = [
                'protein' => (float) ($registered->protein ?? 0),
                'carbs' => (float) ($registered->carbs ?? 0),
                'fat' => (float) ($registered->fat ?? 0),
                'calories' => (float) ($registered->calories ?? 0),
            ];

            $meal['target'] = $target;
            $meal['current'] = $current;
            $meal['percent'] = min(100, ($current['calories'] / max(1, $target['calories'])) * 100);
            $meal['missing'] = [
                'protein' => max(0, $target['protein'] - $current['protein']),
                'carbs' => max(0, $target['carbs'] - $current['carbs']),
                'fat' => max(0, $target['fat'] - $current['fat']),
                'calories' => max(0, $target['calories'] - $current['calories']),
            ];

            return $meal;
        });

        $dailyTotals = [
            'protein' => $mealPlan->sum(fn ($meal) => $meal['current']['protein']),
            'carbs' => $mealPlan->sum(fn ($meal) => $meal['current']['carbs']),
            'fat' => $mealPlan->sum(fn ($meal) => $meal['current']['fat']),
            'calories' => $mealPlan->sum(fn ($meal) => $meal['current']['calories']),
        ];

        $dailyPercents = [
            'protein' => min(100, ($dailyTotals['protein'] / max(1, $nutritionGoals['protein'])) * 100),
            'carbs' => min(100, ($dailyTotals['carbs'] / max(1, $nutritionGoals['carbs'])) * 100),
            'fat' => min(100, ($dailyTotals['fat'] / max(1, $nutritionGoals['fat'])) * 100),
            'calories' => min(100, ($dailyTotals['calories'] / max(1, $nutritionGoals['calories'])) * 100),
        ];

        $suggestions = $this->suggestions($mealPlan, $dailyPercents, $metrics);

        return view('daily-meal-plan', compact(
            'metrics',
            'nutritionGoals',
            'mealPlan',
            'dailyTotals',
            'dailyPercents',
            'suggestions'
        ));
    }

    private function mealTypes(int $mealsPerDay): array
    {
        return match (true) {
            $mealsPerDay <= 3 => [
                ['type' => 'breakfast', 'label' => 'Cafe da manha', 'icon' => 'bi-sun-fill', 'weight' => .25],
                ['type' => 'lunch', 'label' => 'Almoco', 'icon' => 'bi-egg-fried', 'weight' => .40],
                ['type' => 'dinner', 'label' => 'Jantar', 'icon' => 'bi-moon-stars-fill', 'weight' => .35],
            ],
            $mealsPerDay === 4 => [
                ['type' => 'breakfast', 'label' => 'Cafe da manha', 'icon' => 'bi-sun-fill', 'weight' => .22],
                ['type' => 'lunch', 'label' => 'Almoco', 'icon' => 'bi-egg-fried', 'weight' => .35],
                ['type' => 'snack', 'label' => 'Lanche', 'icon' => 'bi-cup-hot', 'weight' => .16],
                ['type' => 'dinner', 'label' => 'Jantar', 'icon' => 'bi-moon-stars-fill', 'weight' => .27],
            ],
            default => [
                ['type' => 'breakfast', 'label' => 'Cafe da manha', 'icon' => 'bi-sun-fill', 'weight' => .20],
                ['type' => 'lunch', 'label' => 'Almoco', 'icon' => 'bi-egg-fried', 'weight' => .32],
                ['type' => 'snack', 'label' => 'Lanche', 'icon' => 'bi-cup-hot', 'weight' => .14],
                ['type' => 'dinner', 'label' => 'Jantar', 'icon' => 'bi-moon-stars-fill', 'weight' => .24],
                ['type' => 'supper', 'label' => 'Ceia', 'icon' => 'bi-stars', 'weight' => .10],
            ],
        };
    }

    private function suggestions($mealPlan, array $dailyPercents, array $metrics): array
    {
        $nextMeal = $mealPlan->first(fn ($meal) => $meal['percent'] < 50) ?? $mealPlan->last();
        $suggestions = [];

        if ($dailyPercents['protein'] < 80) {
            $suggestions[] = [
                'icon' => 'bi-egg-fried',
                'title' => 'Priorize proteina',
                'text' => 'Na proxima refeicao, mire perto de ' . $metrics['protein_per_meal'] . 'g de proteina.',
            ];
        }

        if ($dailyPercents['calories'] < 60) {
            $suggestions[] = [
                'icon' => 'bi-fire',
                'title' => 'Distribua energia',
                'text' => 'Evite deixar muitas calorias para o final do dia. A proxima refeicao sugerida e ' . $nextMeal['label'] . '.',
            ];
        }

        $suggestions[] = [
            'icon' => 'bi-clock-history',
            'title' => 'Use o plano como trilho',
            'text' => 'Os valores nao precisam ser exatos. Ficar perto das metas por refeicao ja melhora a consistencia.',
        ];

        return array_slice($suggestions, 0, 3);
    }
}
