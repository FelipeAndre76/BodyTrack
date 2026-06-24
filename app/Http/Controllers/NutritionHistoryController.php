<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\Food;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NutritionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        $metrics = BodyMetrics::for($profile);
        $nutritionGoals = $metrics['nutrition_goals'];

        $filters = [
            'start_date' => $request->input('start_date', now()->subDays(6)->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'meal_type' => $request->input('meal_type'),
            'food_id' => $request->input('food_id'),
        ];

        $mealsQuery = Meal::with(['items.food'])
            ->where('user_id', $user->id)
            ->whereBetween('meal_date', [$filters['start_date'], $filters['end_date']])
            ->latest('meal_date')
            ->latest();

        if ($filters['meal_type']) {
            $mealsQuery->where('meal_type', $filters['meal_type']);
        }

        if ($filters['food_id']) {
            $mealsQuery->whereHas('items', fn ($query) => $query->where('food_id', $filters['food_id']));
        }

        $meals = $mealsQuery->get();
        $items = $meals->flatMap->items;
        $totals = [
            'protein' => $items->sum('protein'),
            'carbs' => $items->sum('carbs'),
            'fat' => $items->sum('fat'),
            'calories' => $items->sum('calories'),
        ];

        $days = max(1, now()->parse($filters['start_date'])->diffInDays(now()->parse($filters['end_date'])) + 1);
        $averages = [
            'protein' => $totals['protein'] / $days,
            'carbs' => $totals['carbs'] / $days,
            'fat' => $totals['fat'] / $days,
            'calories' => $totals['calories'] / $days,
        ];

        $mealTypes = [
            'breakfast' => ['label' => 'Cafe da manha', 'icon' => 'bi-sun-fill'],
            'lunch' => ['label' => 'Almoco', 'icon' => 'bi-egg-fried'],
            'snack' => ['label' => 'Lanche', 'icon' => 'bi-cup-hot'],
            'dinner' => ['label' => 'Jantar', 'icon' => 'bi-moon-stars-fill'],
            'supper' => ['label' => 'Ceia', 'icon' => 'bi-stars'],
        ];

        $foods = Food::orderBy('name')->get();
        $dailyTotals = $meals
            ->groupBy(fn ($meal) => $meal->meal_date->format('Y-m-d'))
            ->map(function ($dayMeals) {
                $dayItems = $dayMeals->flatMap->items;

                return [
                    'protein' => $dayItems->sum('protein'),
                    'carbs' => $dayItems->sum('carbs'),
                    'fat' => $dayItems->sum('fat'),
                    'calories' => $dayItems->sum('calories'),
                ];
            });

        return view('nutrition-history', compact(
            'filters',
            'meals',
            'mealTypes',
            'foods',
            'totals',
            'averages',
            'dailyTotals',
            'nutritionGoals',
            'metrics',
            'days'
        ));
    }
}
