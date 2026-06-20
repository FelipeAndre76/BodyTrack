<?php

namespace App\Http\Controllers;

use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutItem;

class WorkoutController extends Controller
{
    public function index()
    {
        $categories = ExerciseCategory::with('exercises')
            ->orderBy('name')
            ->get();

        $currentWorkout = session()->get('current_workout', []);
        return view('workouts.index', compact('categories', 'currentWorkout'));
    }

    public function addExercise(Request $request)
    {
        $exercise = Exercise::findOrFail($request->exercise_id);

        $workout = session()->get('current_workout', []);

        $workout[] = [
            'id' => uniqid(),
            'exercise_id' => $exercise->id,
            'name' => $exercise->name,
            'sets' => $request->sets,
            'reps' => $request->reps,
            'weight' => $request->weight,
            'notes' => $request->notes,
        ];

        session()->put('current_workout', $workout);

        return response()->json([
            'success' => true
        ]);
    }

    public function removeExercise(Request $request)
    {
        $workout = session()->get('current_workout', []);

        $workout = collect($workout)
            ->reject(fn($item) => $item['id'] == $request->id)
            ->values()
            ->toArray();

        session()->put('current_workout', $workout);

        return response()->json([
            'success' => true
        ]);
    }

    public function saveWorkout(Request $request)
{
    $currentWorkout = session()->get('current_workout', []);

    if (empty($currentWorkout)) {
        return response()->json([
            'success' => false,
            'message' => 'Nenhum exercício no treino.'
        ]);
    }

    $workout = Workout::create([
        'user_id' => auth()->id(),
        'name' => $request->name,
        'workout_date' => $request->workout_date,
        'status' => 'completed'
    ]);

    foreach ($currentWorkout as $item) {

        WorkoutItem::create([
            'workout_id' => $workout->id,
            'exercise_id' => $item['exercise_id'],
            'sets' => $item['sets'],
            'reps' => $item['reps'],
            'weight' => $item['weight'] ?? null,
            'notes' => $item['notes'] ?? null,
        ]);
    }

    session()->forget('current_workout');

    return response()->json([
        'success' => true
    ]);
}

  public function history()
{
    $workouts = Workout::with('items.exercise')
        ->where('user_id', auth()->id())
        ->orderBy('workout_date')
        ->orderBy('created_at')
        ->get()
        ->groupBy(function ($workout) {
            return $workout->workout_date->format('d/m/Y');
        });

    return view('workouts.history', compact('workouts'));
}
}
