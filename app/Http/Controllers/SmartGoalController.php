<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\MealLog;
use App\Models\WaterLog;
use App\Models\WeightLog;
use App\Models\Workout;
use Illuminate\Support\Facades\Auth;

class SmartGoalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $today = now()->toDateString();
        $periodStart = now()->subDays(6)->toDateString();

        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderBy('recorded_at')
            ->get();

        $metrics = BodyMetrics::for($profile, (float) ($weightLogs->last()?->weight ?? 80));
        $nutritionGoals = $metrics['nutrition_goals'];

        $todayNutrition = MealLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->selectRaw('COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->first();

        $weeklyNutrition = MealLog::where('user_id', $user->id)
            ->where('meal_date', '>=', $periodStart)
            ->selectRaw('COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(carbs), 0) as carbs, COALESCE(SUM(fat), 0) as fat, COALESCE(SUM(calories), 0) as calories')
            ->first();

        $waterToday = WaterLog::where('user_id', $user->id)
            ->where('recorded_at', $today)
            ->sum('amount_ml');

        $weeklyWater = WaterLog::where('user_id', $user->id)
            ->where('recorded_at', '>=', $periodStart)
            ->sum('amount_ml');

        $weeklyWorkouts = Workout::where('user_id', $user->id)
            ->where('workout_date', '>=', $periodStart)
            ->count();

        $goalCards = [
            [
                'icon' => 'bi-egg-fried',
                'label' => 'Proteina',
                'value' => (float) ($todayNutrition->protein ?? 0),
                'goal' => $nutritionGoals['protein'],
                'average' => ((float) ($weeklyNutrition->protein ?? 0)) / 7,
                'suffix' => 'g',
                'note' => $metrics['protein_per_meal'] . 'g por refeicao, em media.',
            ],
            [
                'icon' => 'bi-fire',
                'label' => 'Calorias',
                'value' => (float) ($todayNutrition->calories ?? 0),
                'goal' => $nutritionGoals['calories'],
                'average' => ((float) ($weeklyNutrition->calories ?? 0)) / 7,
                'suffix' => 'kcal',
                'note' => 'Manutencao estimada em ' . number_format((float) $metrics['maintenance_calories'], 0, ',', '.') . ' kcal.',
            ],
            [
                'icon' => 'bi-droplet',
                'label' => 'Agua',
                'value' => (float) $waterToday,
                'goal' => $metrics['water_goal'],
                'average' => ((float) $weeklyWater) / 7,
                'suffix' => 'ml',
                'note' => 'Em dia de treino, mirar ' . number_format((float) $metrics['training_water_goal'], 0, ',', '.') . 'ml.',
            ],
            [
                'icon' => 'bi-activity',
                'label' => 'Treinos',
                'value' => (float) $weeklyWorkouts,
                'goal' => 3,
                'average' => (float) $weeklyWorkouts,
                'suffix' => '/semana',
                'note' => 'Base saudavel: 3 ou mais sessoes por semana.',
            ],
        ];

        $goalCards = collect($goalCards)->map(function ($card) {
            $percent = $card['goal'] > 0 ? min(100, ($card['value'] / $card['goal']) * 100) : 0;
            $card['percent'] = $percent;
            $card['missing'] = max(0, $card['goal'] - $card['value']);
            $card['tone'] = $percent >= 90 ? 'success' : ($percent >= 60 ? 'neutral' : 'warning');

            return $card;
        });

        $weightTrend = $this->weightTrend($weightLogs);
        $dailyInsights = BodyMetrics::dailyInsights($metrics, [
            'protein' => $todayNutrition->protein ?? 0,
            'calories' => $todayNutrition->calories ?? 0,
            'water' => $waterToday,
            'workouts' => Workout::where('user_id', $user->id)->where('workout_date', $today)->count(),
        ]);

        $strategies = $this->strategies($metrics, $goalCards, $weightTrend);

        return view('smart-goals', compact(
            'metrics',
            'nutritionGoals',
            'goalCards',
            'weightTrend',
            'dailyInsights',
            'strategies'
        ));
    }

    private function weightTrend($weightLogs): array
    {
        $recent = $weightLogs->take(-2)->values();

        if ($recent->count() < 2) {
            return [
                'label' => 'Aguardando dados',
                'value' => null,
                'description' => 'Registre pelo menos duas pesagens para calcular tendencia.',
                'tone' => 'neutral',
            ];
        }

        $first = $recent->first();
        $latest = $recent->last();
        $diff = (float) $latest->weight - (float) $first->weight;
        $days = max(1, $first->recorded_at->diffInDays($latest->recorded_at));
        $weeklyRate = ($diff / $days) * 7;

        return [
            'label' => $weeklyRate < 0 ? 'Perda semanal' : 'Ganho semanal',
            'value' => $weeklyRate,
            'description' => 'Ritmo estimado com base nas duas ultimas pesagens.',
            'tone' => abs($weeklyRate) <= 1 ? 'success' : 'warning',
        ];
    }

    private function strategies(array $metrics, $goalCards, array $weightTrend): array
    {
        $proteinCard = $goalCards->firstWhere('label', 'Proteina');
        $waterCard = $goalCards->firstWhere('label', 'Agua');
        $calorieCard = $goalCards->firstWhere('label', 'Calorias');
        $strategies = [];

        if (($proteinCard['percent'] ?? 0) < 80) {
            $strategies[] = [
                'icon' => 'bi-egg-fried',
                'title' => 'Distribuir proteina',
                'text' => 'Inclua uma fonte de proteina em cada refeicao para chegar perto de ' . $metrics['protein_per_meal'] . 'g por refeicao.',
            ];
        }

        if (($waterCard['percent'] ?? 0) < 80) {
            $strategies[] = [
                'icon' => 'bi-droplet',
                'title' => 'Hidratacao em blocos',
                'text' => 'Divida a meta de agua em 4 blocos no dia para evitar tentar compensar tudo a noite.',
            ];
        }

        if (($calorieCard['percent'] ?? 0) < 70) {
            $strategies[] = [
                'icon' => 'bi-fire',
                'title' => 'Energia minima',
                'text' => 'Se a meta calorica ficar muito baixa no dia, priorize refeicoes completas antes de cortar mais calorias.',
            ];
        }

        if (($weightTrend['tone'] ?? 'neutral') === 'warning') {
            $strategies[] = [
                'icon' => 'bi-speedometer2',
                'title' => 'Ritmo corporal',
                'text' => 'A tendencia esta mais agressiva. Compare com energia, sono e desempenho antes de ajustar a dieta.',
            ];
        }

        if (!$strategies) {
            $strategies[] = [
                'icon' => 'bi-check2-circle',
                'title' => 'Plano alinhado',
                'text' => 'As metas principais estao bem encaminhadas. Continue registrando para manter a leitura precisa.',
            ];
        }

        return array_slice($strategies, 0, 4);
    }
}
