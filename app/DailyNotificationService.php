<?php

namespace App;

use App\Models\User;
use App\Models\MealLog;
use App\Models\UserNotification;
use App\Models\WaterLog;
use App\Models\WeeklyCheckIn;
use App\Models\Workout;

class DailyNotificationService
{
    public function sync(User $user, array $metrics, array $todayTotals): void
    {
        $goals = $metrics['nutrition_goals'];
        $proteinPercent = (($todayTotals['protein'] ?? 0) / max(1, $goals['protein'])) * 100;
        $waterPercent = (($todayTotals['water'] ?? 0) / max(1, $metrics['water_goal'])) * 100;
        $caloriesPercent = (($todayTotals['calories'] ?? 0) / max(1, $goals['calories'])) * 100;

        if ($proteinPercent < 70) {
            $this->createOncePerDay($user, [
                'title' => 'Proteina abaixo da meta',
                'message' => 'Voce esta com ' . number_format($proteinPercent, 0, ',', '.') . '% da proteina diaria.',
                'type' => 'warning',
                'icon' => 'bi-egg-fried',
                'link_url' => route('nutrition.index'),
            ]);
        }

        if ($waterPercent < 70) {
            $this->createOncePerDay($user, [
                'title' => 'Hidratacao pendente',
                'message' => 'Voce esta com ' . number_format($waterPercent, 0, ',', '.') . '% da meta de agua.',
                'type' => 'warning',
                'icon' => 'bi-droplet',
                'link_url' => route('water.index'),
            ]);
        }

        if ($caloriesPercent < 55) {
            $this->createOncePerDay($user, [
                'title' => 'Calorias ainda baixas',
                'message' => 'Voce registrou ' . number_format($caloriesPercent, 0, ',', '.') . '% das calorias alvo.',
                'type' => 'info',
                'icon' => 'bi-fire',
                'link_url' => route('nutrition.index'),
            ]);
        }

        if (($todayTotals['workouts'] ?? 0) < 1) {
            $this->createOncePerDay($user, [
                'title' => 'Treino nao registrado',
                'message' => 'Se hoje for dia de treino, registre sua sessao para manter o historico.',
                'type' => 'info',
                'icon' => 'bi-activity',
                'link_url' => route('workouts.index'),
            ]);
        }

        $hasRecentCheckIn = WeeklyCheckIn::where('user_id', $user->id)
            ->where('check_in_date', '>=', now()->subDays(7)->toDateString())
            ->exists();

        if (!$hasRecentCheckIn) {
            $this->createOncePerWeek($user, [
                'title' => 'Check-in semanal pendente',
                'message' => 'Registre peso, foto e percepcao da semana para acompanhar sua evolucao.',
                'type' => 'info',
                'icon' => 'bi-clipboard2-pulse',
                'link_url' => route('check-ins.index'),
            ]);
        }

        $weeklySummary = $this->weeklySummary($user, $metrics);

        if ($weeklySummary['has_activity']) {
            $this->createOrUpdateOncePerWeek($user, [
                'title' => 'Resumo semanal pronto',
                'message' => 'Sua semana fechou com ' . number_format($weeklySummary['score'], 0, ',', '.') . '% de consistencia.',
                'type' => $weeklySummary['score'] >= 75 ? 'success' : 'info',
                'icon' => 'bi-calendar2-week',
                'link_url' => route('weekly-summary.index'),
            ]);
        }
    }

    private function createOncePerDay(User $user, array $data): void
    {
        $exists = UserNotification::where('user_id', $user->id)
            ->where('title', $data['title'])
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if (!$exists) {
            UserNotification::create([
                'user_id' => $user->id,
                ...$data,
            ]);
        }
    }

    private function createOncePerWeek(User $user, array $data): void
    {
        $exists = UserNotification::where('user_id', $user->id)
            ->where('title', $data['title'])
            ->where('created_at', '>=', now()->startOfWeek())
            ->exists();

        if (!$exists) {
            UserNotification::create([
                'user_id' => $user->id,
                ...$data,
            ]);
        }
    }

    private function createOrUpdateOncePerWeek(User $user, array $data): void
    {
        $notification = UserNotification::where('user_id', $user->id)
            ->where('title', $data['title'])
            ->where('created_at', '>=', now()->startOfWeek())
            ->first();

        if ($notification) {
            $notification->update($data);
            return;
        }

        UserNotification::create([
            'user_id' => $user->id,
            ...$data,
        ]);
    }

    private function weeklySummary(User $user, array $metrics): array
    {
        $start = now()->subDays(6)->toDateString();
        $end = now()->toDateString();
        $goals = $metrics['nutrition_goals'];

        $nutritionByDay = MealLog::where('user_id', $user->id)
            ->whereBetween('meal_date', [$start, $end])
            ->selectRaw('meal_date, COALESCE(SUM(protein), 0) as protein, COALESCE(SUM(calories), 0) as calories')
            ->groupBy('meal_date')
            ->get()
            ->keyBy(fn ($item) => $item->meal_date->toDateString());

        $waterByDay = WaterLog::where('user_id', $user->id)
            ->whereBetween('recorded_at', [$start, $end])
            ->selectRaw('recorded_at, COALESCE(SUM(amount_ml), 0) as total')
            ->groupBy('recorded_at')
            ->get()
            ->keyBy('recorded_at');

        $workoutsByDay = Workout::where('user_id', $user->id)
            ->whereBetween('workout_date', [$start, $end])
            ->selectRaw('workout_date, COUNT(*) as total')
            ->groupBy('workout_date')
            ->get()
            ->keyBy(fn ($item) => $item->workout_date->toDateString());

        $scores = collect(range(6, 0))->map(function ($offset) use ($nutritionByDay, $waterByDay, $workoutsByDay, $goals, $metrics) {
            $date = now()->subDays($offset)->toDateString();
            $nutrition = $nutritionByDay->get($date);
            $water = $waterByDay->get($date);
            $workouts = (int) ($workoutsByDay->get($date)->total ?? 0);
            $protein = (float) ($nutrition->protein ?? 0);
            $calories = (float) ($nutrition->calories ?? 0);
            $waterTotal = (float) ($water->total ?? 0);

            return [
                'has_activity' => $protein > 0 || $calories > 0 || $waterTotal > 0 || $workouts > 0,
                'score' => array_sum([
                    min(100, ($protein / max(1, $goals['protein'])) * 100),
                    min(100, ($calories / max(1, $goals['calories'])) * 100),
                    min(100, ($waterTotal / max(1, $metrics['water_goal'])) * 100),
                    $workouts > 0 ? 100 : 0,
                ]) / 4,
            ];
        });

        return [
            'has_activity' => $scores->contains(fn ($day) => $day['has_activity']),
            'score' => $scores->avg('score') ?: 0,
        ];
    }
}
