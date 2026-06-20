<?php

namespace App\Http\Controllers;

use App\Models\WeightLog;
use App\Models\MealLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $profile = $user->profile;

        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderBy('recorded_at')
            ->get();

        $latestWeight = $weightLogs->last()?->weight ?? $profile?->current_weight ?? 0;

        $startWeight = $profile?->start_weight ?? 0;
        $goalWeight = $profile?->goal_weight ?? 0;
        $height = $profile?->height ?? 1;

        $weightLost = $startWeight - $latestWeight;
        $remainingWeight = $latestWeight - $goalWeight;
        $imc = $height > 0 ? $latestWeight / ($height * $height) : 0;

        $chartWeights = $weightLogs->pluck('weight')->toArray();

        $chartDates = $weightLogs->map(function ($log) {
            return date('d/m', strtotime($log->recorded_at));
        })->toArray();

        $today = now()->toDateString();

        $todayNutrition = MealLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->selectRaw('
                COALESCE(SUM(protein), 0) as protein,
                COALESCE(SUM(carbs), 0) as carbs,
                COALESCE(SUM(fat), 0) as fat,
                COALESCE(SUM(calories), 0) as calories
            ')
            ->first();

        $currentWeight = $latestWeight > 0 ? $latestWeight : 80;

        $nutritionGoals = [
            'protein' => round($currentWeight * 2),
            'carbs' => round($currentWeight * 2.5),
            'fat' => round($currentWeight * 0.8),
            'calories' => round($currentWeight * 25),
        ];

        return view('dashboard', compact(
            'profile',
            'latestWeight',
            'startWeight',
            'goalWeight',
            'weightLost',
            'remainingWeight',
            'imc',
            'chartWeights',
            'chartDates',
            'todayNutrition',
            'nutritionGoals'
        ));
    }
}
