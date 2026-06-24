<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\MealLog;
use App\Models\WaterLog;
use App\Models\WeightLog;
use App\Models\WeeklyCheckIn;
use App\Models\Workout;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvolutionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $today = now()->toDateString();

        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderBy('recorded_at')
            ->get();

        $metrics = BodyMetrics::for($profile, (float) ($weightLogs->last()?->weight ?? 80));
        $latestWeight = $metrics['weight'];
        $startWeight = $metrics['start_weight'];
        $goalWeight = $metrics['goal_weight'];
        $imc = $metrics['bmi'];
        $weightLost = $metrics['weight_lost'];
        $remainingWeight = $metrics['remaining_weight'];
        $progressPercent = $metrics['progress_percent'];
        $waterGoal = $metrics['water_goal'];
        $nutritionGoals = $metrics['nutrition_goals'];

        $dailyNutrition = MealLog::where('user_id', $user->id)
            ->where('meal_date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('meal_date, COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->groupBy('meal_date')
            ->orderBy('meal_date')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->meal_date)->toDateString());

        $dailyWater = WaterLog::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('recorded_at, COALESCE(SUM(amount_ml), 0) as total')
            ->groupBy('recorded_at')
            ->orderBy('recorded_at')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->recorded_at)->toDateString());

        $dailyWorkouts = Workout::where('user_id', $user->id)
            ->where('workout_date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('workout_date, COUNT(*) as total')
            ->groupBy('workout_date')
            ->orderBy('workout_date')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->workout_date)->toDateString());

        $period = collect(range(13, 0))->map(fn ($day) => now()->subDays($day));
        $chartDates = $period->map(fn ($date) => $date->format('d/m'))->toArray();
        $periodKeys = $period->map(fn ($date) => $date->toDateString());

        $waterSeries = $periodKeys->map(fn ($date) => (int) ($dailyWater[$date]->total ?? 0))->toArray();
        $proteinSeries = $periodKeys->map(fn ($date) => round((float) ($dailyNutrition[$date]->protein ?? 0), 1))->toArray();
        $caloriesSeries = $periodKeys->map(fn ($date) => round((float) ($dailyNutrition[$date]->calories ?? 0), 0))->toArray();
        $workoutSeries = $periodKeys->map(fn ($date) => (int) ($dailyWorkouts[$date]->total ?? 0))->toArray();
        $lastSevenKeys = $periodKeys->slice(-7);
        $weeklyProteinAverage = $lastSevenKeys->avg(fn ($date) => (float) ($dailyNutrition[$date]->protein ?? 0));
        $weeklyCaloriesAverage = $lastSevenKeys->avg(fn ($date) => (float) ($dailyNutrition[$date]->calories ?? 0));
        $weeklyWaterAverage = $lastSevenKeys->avg(fn ($date) => (float) ($dailyWater[$date]->total ?? 0));
        $weeklyWorkouts = $lastSevenKeys->sum(fn ($date) => (int) ($dailyWorkouts[$date]->total ?? 0));
        $weeklyGoals = [
            'protein' => $nutritionGoals['protein'],
            'calories' => $nutritionGoals['calories'],
            'water' => $waterGoal,
            'workouts' => 3,
        ];
        $weeklyAdherence = [
            'protein' => min(100, ($weeklyProteinAverage / max(1, $weeklyGoals['protein'])) * 100),
            'calories' => min(100, ($weeklyCaloriesAverage / max(1, $weeklyGoals['calories'])) * 100),
            'water' => min(100, ($weeklyWaterAverage / max(1, $weeklyGoals['water'])) * 100),
            'workouts' => min(100, ($weeklyWorkouts / max(1, $weeklyGoals['workouts'])) * 100),
        ];
        $weeklyScore = array_sum($weeklyAdherence) / count($weeklyAdherence);

        $checkIns = WeeklyCheckIn::where('user_id', $user->id)
            ->select([
                'id',
                'user_id',
                'check_in_date',
                'weight',
                'energy_level',
                'mood_level',
                'sleep_quality',
                'notes',
                'photo_path',
                'photo_mime',
                'photo_size',
                'created_at',
                'updated_at',
            ])
            ->latest('check_in_date')
            ->latest()
            ->get();

        $latestCheckIn = $checkIns->first();
        $checkInAverages = [
            'energy' => round($checkIns->take(4)->avg('energy_level') ?: 0, 1),
            'mood' => round($checkIns->take(4)->avg('mood_level') ?: 0, 1),
            'sleep' => round($checkIns->take(4)->avg('sleep_quality') ?: 0, 1),
        ];

        $chartWeights = $weightLogs->map(fn ($log) => (float) $log->weight)->toArray();
        $chartWeightDates = $weightLogs->map(fn ($log) => $log->recorded_at->format('d/m'))->toArray();

        $todayNutrition = MealLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->selectRaw('COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->first();

        $waterToday = WaterLog::where('user_id', $user->id)
            ->where('recorded_at', $today)
            ->sum('amount_ml');

        $todayWorkouts = Workout::where('user_id', $user->id)
            ->where('workout_date', $today)
            ->count();

        $workoutsLast14Days = array_sum($workoutSeries);
        $workoutVolume = Workout::where('workouts.user_id', $user->id)
            ->join('workout_items', 'workout_items.workout_id', '=', 'workouts.id')
            ->where('workouts.workout_date', '>=', now()->subDays(13)->toDateString())
            ->select(DB::raw('COALESCE(SUM(workout_items.weight * workout_items.sets * workout_items.reps), 0) as total'))
            ->value('total');

        $dailyInsights = BodyMetrics::dailyInsights($metrics, [
            'protein' => $todayNutrition->protein ?? 0,
            'calories' => $todayNutrition->calories ?? 0,
            'water' => $waterToday,
            'workouts' => $todayWorkouts,
        ]);

        $recentEvents = collect()
            ->merge($weightLogs->sortByDesc('recorded_at')->take(4)->map(fn ($log) => [
                'icon' => 'bi-speedometer2',
                'title' => 'Pesagem registrada',
                'description' => number_format((float) $log->weight, 1, ',', '.') . 'kg',
                'date' => $log->recorded_at->format('d/m/Y'),
                'sort_date' => $log->recorded_at->toDateString(),
            ]))
            ->merge(Workout::where('user_id', $user->id)->latest('workout_date')->take(4)->get()->map(fn ($workout) => [
                'icon' => 'bi-activity',
                'title' => $workout->name,
                'description' => 'Treino concluido',
                'date' => $workout->workout_date->format('d/m/Y'),
                'sort_date' => $workout->workout_date->toDateString(),
            ]))
            ->merge($checkIns->take(4)->map(fn ($checkIn) => [
                'icon' => 'bi-clipboard2-pulse',
                'title' => 'Check-in semanal',
                'description' => $checkIn->weight
                    ? number_format((float) $checkIn->weight, 1, ',', '.') . 'kg registrado'
                    : 'Registro de percepcao semanal',
                'date' => $checkIn->check_in_date->format('d/m/Y'),
                'sort_date' => $checkIn->check_in_date->toDateString(),
            ]))
            ->sortByDesc('sort_date')
            ->take(6)
            ->values();

        return view('evolution', compact(
            'latestWeight',
            'startWeight',
            'goalWeight',
            'weightLost',
            'remainingWeight',
            'progressPercent',
            'imc',
            'waterGoal',
            'waterToday',
            'nutritionGoals',
            'todayNutrition',
            'workoutsLast14Days',
            'workoutVolume',
            'chartDates',
            'chartWeights',
            'chartWeightDates',
            'waterSeries',
            'proteinSeries',
            'caloriesSeries',
            'workoutSeries',
            'recentEvents',
            'metrics',
            'dailyInsights',
            'weeklyProteinAverage',
            'weeklyCaloriesAverage',
            'weeklyWaterAverage',
            'weeklyWorkouts',
            'weeklyGoals',
            'weeklyAdherence',
            'weeklyScore',
            'latestCheckIn',
            'checkInAverages'
        ));
    }

    public function report()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $today = now()->toDateString();

        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderBy('recorded_at')
            ->get();

        $metrics = BodyMetrics::for($profile, (float) ($weightLogs->last()?->weight ?? 80));
        $nutritionGoals = $metrics['nutrition_goals'];

        $periodStart = now()->subDays(6)->toDateString();

        $nutritionTotals = MealLog::where('user_id', $user->id)
            ->where('meal_date', '>=', $periodStart)
            ->selectRaw('COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->first();

        $waterTotal = WaterLog::where('user_id', $user->id)
            ->where('recorded_at', '>=', $periodStart)
            ->sum('amount_ml');

        $workouts = Workout::where('user_id', $user->id)
            ->where('workout_date', '>=', $periodStart)
            ->latest('workout_date')
            ->get();

        $checkIns = WeeklyCheckIn::where('user_id', $user->id)
            ->select([
                'id',
                'user_id',
                'check_in_date',
                'weight',
                'energy_level',
                'mood_level',
                'sleep_quality',
                'notes',
                'photo_mime',
                'photo_size',
                'photo_data',
                'photo_path',
                'created_at',
                'updated_at',
            ])
            ->latest('check_in_date')
            ->latest()
            ->limit(4)
            ->get();

        $photoCheckIns = $checkIns->filter(fn ($checkIn) => $checkIn->hasPhoto())->values();
        $firstPhoto = $photoCheckIns->last();
        $latestPhoto = $photoCheckIns->first();
        $weightDiff = null;

        if ($firstPhoto && $latestPhoto && $firstPhoto->id !== $latestPhoto->id && $firstPhoto->weight && $latestPhoto->weight) {
            $weightDiff = (float) $latestPhoto->weight - (float) $firstPhoto->weight;
        }

        $report = [
            'nutrition_averages' => [
                'protein' => ($nutritionTotals->protein ?? 0) / 7,
                'carbs' => ($nutritionTotals->carbs ?? 0) / 7,
                'fat' => ($nutritionTotals->fat ?? 0) / 7,
                'calories' => ($nutritionTotals->calories ?? 0) / 7,
            ],
            'water_average' => $waterTotal / 7,
            'workouts_count' => $workouts->count(),
            'weight_points' => $weightLogs->take(-8),
        ];

        $pdf = Pdf::loadView('evolution-report-pdf', compact(
            'user',
            'profile',
            'metrics',
            'nutritionGoals',
            'report',
            'workouts',
            'checkIns',
            'firstPhoto',
            'latestPhoto',
            'weightDiff',
            'today'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('bodytrack-relatorio-evolucao-' . now()->format('Y-m-d') . '.pdf');
    }
}
