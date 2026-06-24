<?php

use App\Http\Controllers\Admin\AdminLogController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyMealPlanController;
use App\Http\Controllers\BodyProfileController;
use App\Http\Controllers\WeightLogController;
use App\Http\Controllers\WaterLogController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\NutritionHistoryController;
use App\Http\Controllers\UserFoodController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\SmartGoalController;
use App\Http\Controllers\WeeklySummaryController;
use App\Http\Controllers\WeeklyCheckInController;
use App\Http\Controllers\SocialController;

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

        Route::get('/logs/pdf',[AdminLogController::class, 'pdf'])->name('logs.pdf');

        Route::get('/logs/filter', [AdminLogController::class, 'filter'])
    ->name('logs.filter');
    });

/*
|--------------------------------------------------------------------------
| User App
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/evolution', [EvolutionController::class, 'index'])
        ->name('evolution.index');

    Route::get('/evolution/report', [EvolutionController::class, 'report'])
        ->name('evolution.report');

    Route::get('/smart-goals', [SmartGoalController::class, 'index'])
        ->name('smart-goals.index');

    Route::get('/weekly-summary', [WeeklySummaryController::class, 'index'])
        ->name('weekly-summary.index');

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

    Route::get('/check-ins', [WeeklyCheckInController::class, 'index'])
        ->name('check-ins.index');

    Route::get('/check-ins/gallery', [WeeklyCheckInController::class, 'gallery'])
        ->name('check-ins.gallery');

    Route::post('/check-ins', [WeeklyCheckInController::class, 'store'])
        ->name('check-ins.store');

    Route::get('/check-ins/{checkIn}/photo', [WeeklyCheckInController::class, 'photo'])
        ->name('check-ins.photo');

    Route::delete('/check-ins/{checkIn}', [WeeklyCheckInController::class, 'destroy'])
        ->name('check-ins.destroy');

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

    Route::get('/nutrition/history', [NutritionHistoryController::class, 'index'])
        ->name('nutrition.history');

    Route::get('/nutrition/meal-plan', [DailyMealPlanController::class, 'index'])
        ->name('nutrition.meal-plan');

    Route::post('/nutrition', [NutritionController::class, 'store'])
        ->name('nutrition.store');

    Route::get('/nutrition/search-foods', [NutritionController::class, 'searchFoods'])
        ->name('nutrition.search-foods');

    Route::delete('/nutrition/{meal}', [NutritionController::class, 'destroy'])
        ->name('nutrition.destroy');

    Route::post('/nutrition/scan-label', [NutritionController::class, 'scanLabel'])
    ->name('nutrition.scan-label');

Route::post('/nutrition/store-scanned-food', [NutritionController::class, 'storeScannedFood'])
    ->name('nutrition.store-scanned-food');

    Route::get('/my-foods', [UserFoodController::class, 'index'])
        ->name('foods.mine');

    Route::put('/my-foods/{food}', [UserFoodController::class, 'update'])
        ->name('foods.update');

    Route::delete('/my-foods/{food}', [UserFoodController::class, 'destroy'])
        ->name('foods.destroy');

    Route::get('/notifications', [UserNotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::delete('/notifications/clear', [UserNotificationController::class, 'clear'])
        ->name('notifications.clear');

    Route::get('/notifications/latest', [UserNotificationController::class, 'latest'])
        ->name('notifications.latest');

    Route::patch('/notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::delete('/notifications/{notification}', [UserNotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    /*
    |--------------------------------------------------------------------------
    | Community
    |--------------------------------------------------------------------------
    */

    Route::get('/community', [SocialController::class, 'index'])
        ->name('community.index');

    Route::post('/community/posts', [SocialController::class, 'store'])
        ->name('community.posts.store');

    Route::get('/community/posts/{post}/photo', [SocialController::class, 'photo'])
        ->name('community.posts.photo');

    Route::get('/community/statuses/{status}/photo', [SocialController::class, 'statusPhoto'])
        ->name('community.statuses.photo');

    Route::post('/community/posts/{post}/like', [SocialController::class, 'toggleLike'])
        ->name('community.posts.like');

    Route::post('/community/posts/{post}/comments', [SocialController::class, 'comment'])
        ->name('community.posts.comment');

    Route::delete('/community/posts/{post}', [SocialController::class, 'destroy'])
        ->name('community.posts.destroy');

    Route::post('/community/users/{user}/follow', [SocialController::class, 'toggleFollow'])
        ->name('community.users.follow');

    Route::get('/community/users/{user}/avatar', [SocialController::class, 'avatar'])
        ->name('community.users.avatar');

    Route::get('/community/users/{user}', [SocialController::class, 'profile'])
        ->name('community.profile');

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

    Route::post('/workouts/plan', [WorkoutController::class, 'savePlan'])
        ->name('workouts.plan.save');

    Route::get('/workouts/history', [WorkoutController::class, 'history'])
        ->name('workouts.history');
});

require __DIR__ . '/auth.php';
