<?php

use App\Http\Controllers\BodyProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeightLogController;
use App\Http\Controllers\WaterLogController;
use App\Http\Controllers\NutritionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkoutController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/body-profile/create', [BodyProfileController::class, 'create'])
        ->name('body-profile.create');

    Route::post('/body-profile', [BodyProfileController::class, 'store'])
        ->name('body-profile.store');

    Route::get('/weights/create', [WeightLogController::class, 'create'])
        ->name('weights.create');

    Route::post('/weights', [WeightLogController::class, 'store'])
        ->name('weights.store');

    Route::get('/water', [WaterLogController::class, 'index'])
        ->name('water.index');

    Route::post('/water', [WaterLogController::class, 'store'])
        ->name('water.store');

    Route::delete('/water/{waterLog}', [WaterLogController::class, 'destroy'])
        ->name('water.destroy');
    Route::get('/nutrition', [NutritionController::class, 'index'])
        ->name('nutrition.index');

    Route::post('/nutrition', [NutritionController::class, 'store'])
        ->name('nutrition.store');

    Route::get('/nutrition/search-foods', [NutritionController::class, 'searchFoods'])
    ->name('nutrition.search-foods');

    Route::delete('/nutrition/{meal}', [NutritionController::class, 'destroy'])
        ->name('nutrition.destroy');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');

    Route::get('/workouts', [WorkoutController::class, 'index'])
    ->name('workouts.index');

    Route::post('/workouts/add-exercise', [WorkoutController::class, 'addExercise'])
    ->name('workouts.add-exercise');

Route::post('/workouts/remove-exercise', [WorkoutController::class, 'removeExercise'])
    ->name('workouts.remove-exercise');

    Route::post('/workouts/save', [WorkoutController::class, 'saveWorkout'])
    ->name('workouts.save');

    Route::get('/workouts/history', [WorkoutController::class, 'history'])
    ->name('workouts.history');
});

Route::get('/settings', function () {
    return view('settings.index');
})->middleware('auth')->name('settings');

require __DIR__ . '/auth.php';
