<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\DailyNotificationService;
use App\Models\MealLog;
use App\Models\WaterLog;
use App\Models\WeightLog;
use App\Models\Workout;
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

        $metrics = BodyMetrics::for($profile, (float) ($weightLogs->last()?->weight ?? 80));
        $latestWeight = $metrics['weight'];
        $startWeight = $metrics['start_weight'];
        $goalWeight = $metrics['goal_weight'];
        $weightLost = $metrics['weight_lost'];
        $remainingWeight = $metrics['remaining_weight'];
        $imc = $metrics['bmi'];
        $progressPercent = $metrics['progress_percent'];

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

        $nutritionGoals = $metrics['nutrition_goals'];

        $waterToday = WaterLog::where('user_id', $user->id)
            ->where('recorded_at', $today)
            ->sum('amount_ml');

        $waterGoal = $metrics['water_goal'];

        $waterPercent = $waterGoal > 0
            ? min(100, ($waterToday / $waterGoal) * 100)
            : 0;

        $todayWorkouts = Workout::where('user_id', $user->id)
            ->where('workout_date', $today)
            ->count();

        $lastWorkout = Workout::where('user_id', $user->id)
            ->latest('workout_date')
            ->latest()
            ->first();

        $workoutsLast30Days = Workout::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $mealsToday = MealLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->count();

        $proteinPercent = $nutritionGoals['protein'] > 0
            ? min(100, (($todayNutrition->protein ?? 0) / $nutritionGoals['protein']) * 100)
            : 0;

        $caloriesPercent = $nutritionGoals['calories'] > 0
            ? min(100, (($todayNutrition->calories ?? 0) / $nutritionGoals['calories']) * 100)
            : 0;

        $dailyInsights = BodyMetrics::dailyInsights($metrics, [
            'protein' => $todayNutrition->protein ?? 0,
            'calories' => $todayNutrition->calories ?? 0,
            'water' => $waterToday,
            'workouts' => $todayWorkouts,
        ]);

        app(DailyNotificationService::class)->sync($user, $metrics, [
            'protein' => $todayNutrition->protein ?? 0,
            'calories' => $todayNutrition->calories ?? 0,
            'water' => $waterToday,
            'workouts' => $todayWorkouts,
        ]);

        return view('dashboard', compact(
            'profile',
            'latestWeight',
            'startWeight',
            'goalWeight',
            'weightLost',
            'remainingWeight',
            'imc',
            'progressPercent',
            'chartWeights',
            'chartDates',
            'todayNutrition',
            'nutritionGoals',
            'waterToday',
            'waterGoal',
            'waterPercent',
            'todayWorkouts',
            'lastWorkout',
            'workoutsLast30Days',
            'mealsToday',
            'proteinPercent',
            'caloriesPercent',
            'metrics',
            'dailyInsights'
        ));
    }
}
