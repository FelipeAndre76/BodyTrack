<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BodyProfileController;
use App\Http\Controllers\WeightLogController;
use App\Http\Controllers\WaterLogController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\WorkoutController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\ExerciseCategoryController;
use App\Http\Controllers\Admin\ExercisePhotoController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('exercises', ExerciseController::class);

        Route::resource('categories', ExerciseCategoryController::class);

        Route::delete('/categories/{category}/force-delete', [ExerciseCategoryController::class, 'forceDelete'])
            ->name('categories.force-delete');

        Route::get('/photos', [ExercisePhotoController::class, 'index'])
            ->name('photos.index');

        Route::post('/photos/{exercise}', [ExercisePhotoController::class, 'update'])
            ->name('photos.update');

        Route::delete('/photos/{exercise}', [ExercisePhotoController::class, 'destroy'])
            ->name('photos.destroy');

        Route::resource('users', UserController::class)
            ->only(['index', 'destroy']);

        Route::patch('/users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])
            ->name('users.toggle-admin');

        Route::patch('/users/{user}/toggle-status',[UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/logs', [\App\Http\Controllers\Admin\AdminLogController::class, 'index'])->name('logs.index');

        Route::get('/logs/export', [\App\Http\Controllers\Admin\AdminLogController::class, 'export'])->name('logs.export');
    });

/*
|--------------------------------------------------------------------------
| User App
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Body Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/body-profile/create', [BodyProfileController::class, 'create'])
        ->name('body-profile.create');

    Route::post('/body-profile', [BodyProfileController::class, 'store'])
        ->name('body-profile.store');

    /*
    |--------------------------------------------------------------------------
    | Weights
    |--------------------------------------------------------------------------
    */

    Route::get('/weights/create', [WeightLogController::class, 'create'])
        ->name('weights.create');

    Route::post('/weights', [WeightLogController::class, 'store'])
        ->name('weights.store');

    /*
    |--------------------------------------------------------------------------
    | Water
    |--------------------------------------------------------------------------
    */

    Route::get('/water', [WaterLogController::class, 'index'])
        ->name('water.index');

    Route::post('/water', [WaterLogController::class, 'store'])
        ->name('water.store');

    Route::delete('/water/{waterLog}', [WaterLogController::class, 'destroy'])
        ->name('water.destroy');

    /*
    |--------------------------------------------------------------------------
    | Nutrition
    |--------------------------------------------------------------------------
    */

    Route::get('/nutrition', [NutritionController::class, 'index'])
        ->name('nutrition.index');

    Route::post('/nutrition', [NutritionController::class, 'store'])
        ->name('nutrition.store');

    Route::get('/nutrition/search-foods', [NutritionController::class, 'searchFoods'])
        ->name('nutrition.search-foods');

    Route::delete('/nutrition/{meal}', [NutritionController::class, 'destroy'])
        ->name('nutrition.destroy');

    /*
    |--------------------------------------------------------------------------
    | Workouts
    |--------------------------------------------------------------------------
    */

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

require __DIR__ . '/auth.php';
