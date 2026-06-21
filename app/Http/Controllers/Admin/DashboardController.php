<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\User;
use App\Models\Workout;

class DashboardController extends Controller
{
  public function index()
{
    $totalUsers = User::count();
    $totalAdmins = User::where('is_admin', true)->count();

    $totalExercises = Exercise::count();
    $totalCategories = ExerciseCategory::count();
    $totalWorkouts = Workout::count();

    $exercisesWithPhoto = Exercise::whereNotNull('image_path')->count();
    $exercisesWithoutPhoto = Exercise::whereNull('image_path')->count();

    $photoProgress = $totalExercises > 0
        ? round(($exercisesWithPhoto / $totalExercises) * 100)
        : 0;

    $latestLogs = AdminLog::with('admin')
        ->latest()
        ->take(5)
        ->get();

    $latestExercisesWithoutPhoto = Exercise::with('category')
        ->whereNull('image_path')
        ->orderBy('name')
        ->take(8)
        ->get();

    return view('admin.dashboard', compact(
        'totalUsers',
        'totalAdmins',
        'totalExercises',
        'totalCategories',
        'totalWorkouts',
        'exercisesWithPhoto',
        'exercisesWithoutPhoto',
        'photoProgress',
        'latestLogs',
        'latestExercisesWithoutPhoto'
    ));
}
}
