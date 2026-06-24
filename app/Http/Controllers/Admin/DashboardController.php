<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\Meal;
use App\Models\User;
use App\Models\WaterLog;
use App\Models\WeightLog;
use App\Models\Workout;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalActiveUsers = User::where('is_active', true)->count();
        $totalBlockedUsers = User::where('is_active', false)->count();

        $totalExercises = Exercise::count();
        $totalCategories = ExerciseCategory::count();
        $totalWorkouts = Workout::count();

        $exercisesWithPhoto = Exercise::whereNotNull('image_path')->count();
        $exercisesWithoutPhoto = Exercise::whereNull('image_path')->count();
        $emptyCategories = ExerciseCategory::doesntHave('exercises')->count();

        $photoProgress = $totalExercises > 0
            ? round(($exercisesWithPhoto / $totalExercises) * 100)
            : 0;

        $catalogHealth = max(0, min(100, round(
            ($photoProgress * 0.75) +
            (($totalCategories > 0 ? (($totalCategories - $emptyCategories) / $totalCategories) * 100 : 0) * 0.25)
        )));

        $last30Days = now()->subDays(30);

        $newUsersLast30Days = User::where('created_at', '>=', $last30Days)->count();
        $newExercisesLast30Days = Exercise::where('created_at', '>=', $last30Days)->count();

        $photosUploadedLast30Days = AdminLog::whereIn('action', [
            'upload_photo',
            'update_photo',
        ])
            ->where('created_at', '>=', $last30Days)
            ->count();

        $adminActionsLast30Days = AdminLog::where('created_at', '>=', $last30Days)->count();

        $workoutsLast30Days = Workout::where('created_at', '>=', $last30Days)->count();
        $mealsLast30Days = Meal::where('created_at', '>=', $last30Days)->count();
        $waterLogsLast30Days = WaterLog::where('created_at', '>=', $last30Days)->count();
        $weightLogsLast30Days = WeightLog::where('created_at', '>=', $last30Days)->count();

        $recentLogs = AdminLog::with('admin')
            ->latest()
            ->take(8)
            ->get();

        $latestExercisesWithoutPhoto = Exercise::with('category')
            ->whereNull('image_path')
            ->orderBy('name')
            ->take(6)
            ->get();

        $latestBlockedUsers = User::where('is_active', false)
            ->latest()
            ->take(5)
            ->get();

        $topCategories = ExerciseCategory::withCount('exercises')
            ->orderByDesc('exercises_count')
            ->take(5)
            ->get();

        $activityLabels = [];
        $dailyUsers = [];
        $dailyWorkouts = [];
        $dailyAdminActions = [];

        $usersByDay = User::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->pluck('total', 'date');

        $workoutsByDay = Workout::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->pluck('total', 'date');

        $adminActionsByDay = AdminLog::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->pluck('total', 'date');

        for ($day = 29; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $key = $date->toDateString();

            $activityLabels[] = $date->format('d/m');
            $dailyUsers[] = (int) ($usersByDay[$key] ?? 0);
            $dailyWorkouts[] = (int) ($workoutsByDay[$key] ?? 0);
            $dailyAdminActions[] = (int) ($adminActionsByDay[$key] ?? 0);
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAdmins',
            'totalActiveUsers',
            'totalBlockedUsers',
            'totalExercises',
            'totalCategories',
            'totalWorkouts',
            'exercisesWithPhoto',
            'exercisesWithoutPhoto',
            'emptyCategories',
            'photoProgress',
            'catalogHealth',
            'newUsersLast30Days',
            'newExercisesLast30Days',
            'photosUploadedLast30Days',
            'adminActionsLast30Days',
            'workoutsLast30Days',
            'mealsLast30Days',
            'waterLogsLast30Days',
            'weightLogsLast30Days',
            'recentLogs',
            'latestExercisesWithoutPhoto',
            'latestBlockedUsers',
            'topCategories',
            'activityLabels',
            'dailyUsers',
            'dailyWorkouts',
            'dailyAdminActions'
        ));
    }
}
