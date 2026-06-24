<?php

namespace App\Http\Controllers;

use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutItem;
use App\Models\WorkoutPlan;
use Carbon\Carbon;

class WorkoutController extends Controller
{
    public function index()
    {
        $categories = ExerciseCategory::with('exercises')
            ->orderBy('name')
            ->get();

        $currentWorkout = session()->get('current_workout', []);
        $workoutPlan = WorkoutPlan::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'name' => 'Rodizio',
                'rotation' => ['Push', 'Pull', 'Legs'],
                'rest_days' => [6, 0],
                'current_index' => 0,
                'is_active' => true,
            ]
        );
        $nextWorkoutName = $workoutPlan->nextWorkoutName() ?? 'Treino do dia';
        $weeklyRotation = $this->weeklyRotation($workoutPlan);

        return view('workouts.index', compact(
            'categories',
            'currentWorkout',
            'workoutPlan',
            'nextWorkoutName',
            'weeklyRotation'
        ));
    }

    public function savePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:80',
            'rotation' => 'required|string|max:255',
            'rest_days' => 'nullable|array',
            'rest_days.*' => 'integer|min:0|max:6',
        ]);

        $rotation = collect(explode(',', $validated['rotation']))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->take(12)
            ->all();

        if (empty($rotation)) {
            return back()->withErrors([
                'rotation' => 'Informe pelo menos um treino no rodizio.',
            ]);
        }

        WorkoutPlan::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'name' => trim((string) ($validated['name'] ?? '')) ?: 'Rodizio',
                'rotation' => $rotation,
                'rest_days' => collect($validated['rest_days'] ?? [])->map(fn ($day) => (int) $day)->values()->all(),
                'current_index' => 0,
                'is_active' => true,
            ]
        );

        return redirect()->route('workouts.index')
            ->with('success', 'Plano semanal atualizado com sucesso!');
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
            'success' => true,
            'item' => end($workout),
            'summary' => $this->workoutSummary($workout),
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
            'success' => true,
            'summary' => $this->workoutSummary($workout),
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

    $workoutName = trim((string) $request->name);

    if ($workoutName === '') {
        return response()->json([
            'success' => false,
            'message' => 'Escolha ou digite qual treino foi feito.'
        ]);
    }

    $workoutPlan = WorkoutPlan::where('user_id', auth()->id())
        ->where('is_active', true)
        ->first();

    $workout = Workout::create([
        'user_id' => auth()->id(),
        'name' => $workoutName,
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
    $workoutPlan?->advanceAfterWorkoutName($workoutName);

    return response()->json([
        'success' => true
    ]);
}

  public function history()
{
    $workouts = Workout::with('items.exercise')
        ->where('user_id', auth()->id())
        ->orderByDesc('workout_date')
        ->orderByDesc('created_at')
        ->get()
        ->groupBy(function ($workout) {
            return $workout->workout_date->format('d/m/Y');
        });

    return view('workouts.history', compact('workouts'));
}

private function weeklyRotation(WorkoutPlan $plan): array
{
    $restDays = collect($plan->rest_days ?? [])->map(fn ($day) => (int) $day)->all();
    $rotation = collect($plan->rotation ?? [])->filter()->values();
    $previewIndex = $plan->current_index;
    $weekdays = [
        0 => 'Dom',
        1 => 'Seg',
        2 => 'Ter',
        3 => 'Qua',
        4 => 'Qui',
        5 => 'Sex',
        6 => 'Sab',
    ];

    return collect(range(0, 13))->map(function ($offset) use ($restDays, $rotation, &$previewIndex, $weekdays) {
        $date = now()->addDays($offset);
        $weekday = (int) $date->dayOfWeek;
        $isRest = in_array($weekday, $restDays, true);
        $name = 'Descanso';

        if (!$isRest && $rotation->isNotEmpty()) {
            $name = $rotation[$previewIndex % $rotation->count()];
            $previewIndex++;
        }

        return [
            'date' => $date,
            'weekday' => $weekdays[$weekday],
            'name' => $name,
            'is_rest' => $isRest,
            'is_today' => $date->isToday(),
        ];
    })->all();
}

private function workoutSummary(array $workout): array
{
    return [
        'exercises' => count($workout),
        'sets' => collect($workout)->sum(fn ($item) => (int) ($item['sets'] ?? 0)),
        'reps' => collect($workout)->sum(fn ($item) => (int) ($item['reps'] ?? 0)),
        'volume' => collect($workout)->sum(function ($item) {
            return (float) ($item['weight'] ?? 0) * (int) ($item['sets'] ?? 0) * (int) ($item['reps'] ?? 0);
        }),
    ];
}
}
