<?php

namespace App;

use App\Models\Profile;
use Carbon\Carbon;

class BodyMetrics
{
    public static function for(?Profile $profile, float $fallbackWeight = 80): array
    {
        $weight = (float) ($profile?->current_weight ?? $fallbackWeight);
        $startWeight = (float) ($profile?->start_weight ?? $weight);
        $goalWeight = (float) ($profile?->goal_weight ?? $weight);
        $height = (float) ($profile?->height ?? 0);
        $heightCm = $height > 0 ? $height * 100 : 0;
        $age = self::age($profile);
        $gender = $profile?->gender ?: 'not_informed';
        $activityLevel = $profile?->activity_level ?: 'light';
        $nutritionGoal = $profile?->nutrition_goal ?: $profile?->goal ?: 'weight_loss';
        $mealsPerDay = (int) ($profile?->meals_per_day ?: 4);

        $bmi = $height > 0 ? $weight / ($height * $height) : 0;
        $bmiCategory = self::bmiCategory($bmi);
        $weightLost = max(0, $startWeight - $weight);
        $remainingWeight = max(0, $weight - $goalWeight);
        $progressPercent = self::progressPercent($startWeight, $weight, $goalWeight);
        $bmr = self::bmr($weight, $heightCm, $age, $gender);
        $maintenanceCalories = $bmr > 0 ? round($bmr * self::activityFactor($activityLevel)) : round($weight * 30);
        $calorieTarget = self::calorieTarget($maintenanceCalories, $nutritionGoal);
        $proteinFactor = self::proteinFactor($nutritionGoal, $activityLevel);
        $fatFactor = self::fatFactor($nutritionGoal);
        $protein = max(0, round($weight * $proteinFactor));
        $fat = max(0, round($weight * $fatFactor));
        $remainingCalories = max(0, $calorieTarget - ($protein * 4) - ($fat * 9));
        $carbs = max(0, round($remainingCalories / 4));
        $waterGoal = round($weight * 35);
        $calculatedGoals = [
            'protein' => $protein,
            'carbs' => $carbs,
            'fat' => $fat,
            'calories' => $calorieTarget,
            'water' => $waterGoal,
        ];

        $goalCalories = self::customOrCalculated($profile?->custom_calories_goal, $calculatedGoals['calories']);
        $goalProtein = self::customOrCalculated($profile?->custom_protein_goal, $calculatedGoals['protein']);
        $goalFat = self::customOrCalculated($profile?->custom_fat_goal, $calculatedGoals['fat']);
        $recalculatedCarbs = max(0, round(($goalCalories - ($goalProtein * 4) - ($goalFat * 9)) / 4));
        $goalCarbs = self::customOrCalculated($profile?->custom_carbs_goal, $recalculatedCarbs);

        $nutritionGoals = [
            'protein' => $goalProtein,
            'carbs' => $goalCarbs,
            'fat' => $goalFat,
            'calories' => $goalCalories,
        ];

        $waterGoal = self::customOrCalculated($profile?->custom_water_goal, $waterGoal);
        $trainingWaterGoal = $waterGoal + 750;
        $proteinPerMeal = $mealsPerDay > 0 ? round($nutritionGoals['protein'] / $mealsPerDay) : $nutritionGoals['protein'];
        $proteinDoseMin = max(20, round($weight * 0.25));
        $proteinDoseMax = min(45, max($proteinDoseMin, round($weight * 0.35)));
        $calorieBalance = $nutritionGoals['calories'] - $maintenanceCalories;
        $calorieDeficit = max(0, $maintenanceCalories - $nutritionGoals['calories']);
        $calorieSurplus = max(0, $nutritionGoals['calories'] - $maintenanceCalories);
        $calorieBalanceLabel = match (true) {
            $calorieDeficit > 0 => 'Deficit calorico',
            $calorieSurplus > 0 => 'Superavit calorico',
            default => 'Manutencao calorica',
        };
        $estimatedWeeklyWeightChange = $calorieBalance !== 0
            ? ($calorieBalance * 7) / 7700
            : 0;

        return [
            'weight' => $weight,
            'start_weight' => $startWeight,
            'goal_weight' => $goalWeight,
            'height' => $height,
            'age' => $age,
            'gender' => $gender,
            'activity_level' => $activityLevel,
            'nutrition_goal' => $nutritionGoal,
            'meals_per_day' => $mealsPerDay,
            'bmi' => $bmi,
            'bmi_category' => $bmiCategory,
            'weight_lost' => $weightLost,
            'remaining_weight' => $remainingWeight,
            'progress_percent' => $progressPercent,
            'bmr' => $bmr,
            'maintenance_calories' => $maintenanceCalories,
            'calorie_target' => $calorieTarget,
            'calorie_balance' => $calorieBalance,
            'calorie_deficit' => $calorieDeficit,
            'calorie_surplus' => $calorieSurplus,
            'calorie_balance_label' => $calorieBalanceLabel,
            'estimated_weekly_weight_change' => $estimatedWeeklyWeightChange,
            'water_goal' => $waterGoal,
            'training_water_goal' => $trainingWaterGoal,
            'protein_factor' => $proteinFactor,
            'fat_factor' => $fatFactor,
            'protein_per_meal' => $proteinPerMeal,
            'protein_dose_min' => $proteinDoseMin,
            'protein_dose_max' => $proteinDoseMax,
            'calculated_goals' => $calculatedGoals,
            'has_custom_goals' => self::hasCustomGoals($profile),
            'nutrition_goals' => $nutritionGoals,
        ];
    }

    public static function bmiCategory(float $bmi): array
    {
        if ($bmi <= 0) {
            return ['label' => 'Nao calculado', 'tone' => 'neutral'];
        }

        return match (true) {
            $bmi < 18.5 => ['label' => 'Abaixo do peso', 'tone' => 'warning'],
            $bmi < 25 => ['label' => 'Peso saudavel', 'tone' => 'success'],
            $bmi < 30 => ['label' => 'Sobrepeso', 'tone' => 'warning'],
            $bmi < 35 => ['label' => 'Obesidade classe 1', 'tone' => 'danger'],
            $bmi < 40 => ['label' => 'Obesidade classe 2', 'tone' => 'danger'],
            default => ['label' => 'Obesidade classe 3', 'tone' => 'danger'],
        };
    }

    public static function dailyInsights(array $metrics, array $todayTotals): array
    {
        $goals = $metrics['nutrition_goals'];
        $proteinMissing = max(0, $goals['protein'] - (float) ($todayTotals['protein'] ?? 0));
        $caloriesMissing = max(0, $goals['calories'] - (float) ($todayTotals['calories'] ?? 0));
        $waterMissing = max(0, $metrics['water_goal'] - (float) ($todayTotals['water'] ?? 0));
        $insights = [];

        if ($proteinMissing > 0) {
            $insights[] = [
                'icon' => 'bi-egg-fried',
                'title' => 'Completar proteina',
                'description' => 'Faltam ' . number_format($proteinMissing, 0, ',', '.') . 'g para bater a meta de hoje.',
                'action' => 'Registrar refeicao',
                'route' => 'nutrition.index',
                'tone' => $proteinMissing > $metrics['protein_per_meal'] ? 'warning' : 'success',
            ];
        }

        if ($waterMissing > 0) {
            $insights[] = [
                'icon' => 'bi-droplet',
                'title' => 'Atualizar hidratacao',
                'description' => 'Faltam ' . number_format($waterMissing, 0, ',', '.') . 'ml para sua meta base.',
                'action' => 'Registrar agua',
                'route' => 'water.index',
                'tone' => $waterMissing > 1000 ? 'warning' : 'success',
            ];
        }

        if ($caloriesMissing > 0) {
            $insights[] = [
                'icon' => 'bi-fire',
                'title' => 'Energia do dia',
                'description' => 'Ainda faltam ' . number_format($caloriesMissing, 0, ',', '.') . ' kcal para o alvo calculado.',
                'action' => 'Ver nutricao',
                'route' => 'nutrition.index',
                'tone' => 'neutral',
            ];
        }

        if (($todayTotals['workouts'] ?? 0) < 1) {
            $insights[] = [
                'icon' => 'bi-activity',
                'title' => 'Treino pendente',
                'description' => 'Nenhum treino registrado hoje. Se for dia de treino, salve sua sessao.',
                'action' => 'Abrir treinos',
                'route' => 'workouts.index',
                'tone' => 'neutral',
            ];
        }

        if (!$insights) {
            $insights[] = [
                'icon' => 'bi-check2-circle',
                'title' => 'Dia bem alinhado',
                'description' => 'Metas principais estao dentro do planejado para hoje.',
                'action' => 'Ver evolucao',
                'route' => 'evolution.index',
                'tone' => 'success',
            ];
        }

        return array_slice($insights, 0, 4);
    }

    private static function age(?Profile $profile): int
    {
        if (!$profile?->birth_date) {
            return 30;
        }

        return max(1, Carbon::parse($profile->birth_date)->age);
    }

    private static function customOrCalculated(mixed $customGoal, int $calculatedGoal): int
    {
        $customGoal = (int) ($customGoal ?? 0);

        return $customGoal > 0 ? $customGoal : $calculatedGoal;
    }

    private static function hasCustomGoals(?Profile $profile): bool
    {
        if (!$profile) {
            return false;
        }

        return collect([
            $profile->custom_protein_goal,
            $profile->custom_carbs_goal,
            $profile->custom_fat_goal,
            $profile->custom_calories_goal,
            $profile->custom_water_goal,
        ])->contains(fn ($value) => (int) ($value ?? 0) > 0);
    }

    private static function bmr(float $weight, float $heightCm, int $age, string $gender): int
    {
        if ($weight <= 0 || $heightCm <= 0) {
            return 0;
        }

        $base = (10 * $weight) + (6.25 * $heightCm) - (5 * $age);
        $sexAdjustment = match ($gender) {
            'male' => 5,
            'female' => -161,
            default => -78,
        };

        return (int) round($base + $sexAdjustment);
    }

    private static function activityFactor(string $activityLevel): float
    {
        return match ($activityLevel) {
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'high' => 1.725,
            'athlete' => 1.9,
            default => 1.375,
        };
    }

    private static function calorieTarget(int $maintenanceCalories, string $nutritionGoal): int
    {
        return match ($nutritionGoal) {
            'weight_loss' => max(1200, $maintenanceCalories - 450),
            'muscle_gain' => $maintenanceCalories + 300,
            'body_recomposition' => max(1200, $maintenanceCalories - 150),
            default => $maintenanceCalories,
        };
    }

    private static function proteinFactor(string $nutritionGoal, string $activityLevel): float
    {
        return match ($nutritionGoal) {
            'weight_loss' => 1.8,
            'muscle_gain' => 2.0,
            'body_recomposition' => 2.0,
            default => $activityLevel === 'sedentary' ? 1.0 : 1.6,
        };
    }

    private static function fatFactor(string $nutritionGoal): float
    {
        return match ($nutritionGoal) {
            'weight_loss' => 0.7,
            'muscle_gain' => 0.9,
            'body_recomposition' => 0.8,
            default => 0.8,
        };
    }

    private static function progressPercent(float $startWeight, float $currentWeight, float $goalWeight): float
    {
        if ($startWeight <= 0 || $goalWeight <= 0 || $startWeight == $goalWeight) {
            return 0;
        }

        $progress = (($startWeight - $currentWeight) / ($startWeight - $goalWeight)) * 100;

        return max(0, min(100, $progress));
    }
}
