<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\MealLog;
use App\Models\WaterLog;
use App\Models\WeightLog;
use App\Models\WeeklyCheckIn;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WeeklySummaryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderBy('recorded_at')
            ->get();

        $metrics = BodyMetrics::for($profile, (float) ($weightLogs->last()?->weight ?? 80));
        $nutritionGoals = $metrics['nutrition_goals'];

        $nutritionByDay = MealLog::where('user_id', $user->id)
            ->whereBetween('meal_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('meal_date, COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->groupBy('meal_date')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->meal_date)->toDateString());

        $waterByDay = WaterLog::where('user_id', $user->id)
            ->whereBetween('recorded_at', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('recorded_at, COALESCE(SUM(amount_ml), 0) as total')
            ->groupBy('recorded_at')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->recorded_at)->toDateString());

        $workoutsByDay = Workout::where('user_id', $user->id)
            ->whereBetween('workout_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('workout_date, COUNT(*) as total')
            ->groupBy('workout_date')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->workout_date)->toDateString());

        $days = collect(range(6, 0))->map(function ($offset) use ($nutritionByDay, $waterByDay, $workoutsByDay, $nutritionGoals, $metrics) {
            $date = now()->subDays($offset);
            $key = $date->toDateString();
            $nutrition = $nutritionByDay->get($key);
            $water = $waterByDay->get($key);
            $workouts = (int) ($workoutsByDay->get($key)->total ?? 0);
            $protein = (float) ($nutrition->protein ?? 0);
            $calories = (float) ($nutrition->calories ?? 0);
            $waterTotal = (float) ($water->total ?? 0);
            $scoreParts = [
                min(100, ($protein / max(1, $nutritionGoals['protein'])) * 100),
                min(100, ($calories / max(1, $nutritionGoals['calories'])) * 100),
                min(100, ($waterTotal / max(1, $metrics['water_goal'])) * 100),
                $workouts > 0 ? 100 : 0,
            ];

            return [
                'date' => $date,
                'label' => $date->format('d/m'),
                'weekday' => ucfirst($date->translatedFormat('D')),
                'protein' => $protein,
                'carbs' => (float) ($nutrition->carbs ?? 0),
                'fat' => (float) ($nutrition->fat ?? 0),
                'calories' => $calories,
                'water' => $waterTotal,
                'workouts' => $workouts,
                'score' => array_sum($scoreParts) / count($scoreParts),
            ];
        });

        $totals = [
            'protein' => $days->sum('protein'),
            'carbs' => $days->sum('carbs'),
            'fat' => $days->sum('fat'),
            'calories' => $days->sum('calories'),
            'water' => $days->sum('water'),
            'workouts' => $days->sum('workouts'),
        ];

        $averages = [
            'protein' => $totals['protein'] / 7,
            'carbs' => $totals['carbs'] / 7,
            'fat' => $totals['fat'] / 7,
            'calories' => $totals['calories'] / 7,
            'water' => $totals['water'] / 7,
        ];

        $bestDay = $days->sortByDesc('score')->first();
        $weeklyScore = $days->avg('score') ?: 0;
        $weekWeightLogs = $weightLogs->filter(fn ($log) => $log->recorded_at->between($start, $end))->values();
        $startWeight = $weekWeightLogs->first()?->weight ?? $weightLogs->first()?->weight;
        $endWeight = $weekWeightLogs->last()?->weight ?? $weightLogs->last()?->weight;
        $weightDiff = $startWeight && $endWeight ? (float) $endWeight - (float) $startWeight : null;

        $checkIns = WeeklyCheckIn::where('user_id', $user->id)
            ->whereBetween('check_in_date', [$start->toDateString(), $end->toDateString()])
            ->latest('check_in_date')
            ->get();

        $highlights = $this->highlights($weeklyScore, $averages, $nutritionGoals, $metrics, $totals, $weightDiff);
        $chartLabels = $days->pluck('label')->toArray();
        $scoreSeries = $days->map(fn ($day) => round($day['score'], 0))->toArray();
        $proteinSeries = $days->map(fn ($day) => round($day['protein'], 1))->toArray();
        $waterSeries = $days->map(fn ($day) => round($day['water'] / 1000, 1))->toArray();

        return view('weekly-summary', compact(
            'metrics',
            'nutritionGoals',
            'days',
            'totals',
            'averages',
            'bestDay',
            'weeklyScore',
            'weightDiff',
            'checkIns',
            'highlights',
            'chartLabels',
            'scoreSeries',
            'proteinSeries',
            'waterSeries'
        ));
    }

    private function highlights(float $weeklyScore, array $averages, array $nutritionGoals, array $metrics, array $totals, ?float $weightDiff): array
    {
        $items = [];

        $items[] = [
            'icon' => $weeklyScore >= 75 ? 'bi-stars' : 'bi-arrow-repeat',
            'title' => $weeklyScore >= 75 ? 'Boa consistencia semanal' : 'Semana com espaco para ajuste',
            'text' => 'Sua aderencia geral ficou em ' . number_format($weeklyScore, 0, ',', '.') . '%.',
            'tone' => $weeklyScore >= 75 ? 'success' : 'warning',
        ];

        if ($averages['protein'] < ($nutritionGoals['protein'] * .8)) {
            $items[] = [
                'icon' => 'bi-egg-fried',
                'title' => 'Proteina abaixo do alvo',
                'text' => 'Media de ' . number_format($averages['protein'], 1, ',', '.') . 'g por dia. Tente aproximar de ' . $nutritionGoals['protein'] . 'g.',
                'tone' => 'warning',
            ];
        }

        if ($averages['water'] < ($metrics['water_goal'] * .8)) {
            $items[] = [
                'icon' => 'bi-droplet',
                'title' => 'Hidratacao pode subir',
                'text' => 'Media de ' . number_format($averages['water'], 0, ',', '.') . 'ml por dia nesta semana.',
                'tone' => 'neutral',
            ];
        }

        if ($totals['workouts'] >= 3) {
            $items[] = [
                'icon' => 'bi-activity',
                'title' => 'Frequencia de treino batida',
                'text' => $totals['workouts'] . ' treinos registrados nos ultimos 7 dias.',
                'tone' => 'success',
            ];
        }

        if ($weightDiff !== null) {
            $items[] = [
                'icon' => 'bi-speedometer2',
                'title' => 'Peso na semana',
                'text' => ($weightDiff <= 0 ? 'Reducao de ' : 'Aumento de ') . number_format(abs($weightDiff), 1, ',', '.') . 'kg no periodo.',
                'tone' => abs($weightDiff) <= 1 ? 'success' : 'warning',
            ];
        }

        return array_slice($items, 0, 4);
    }
}
