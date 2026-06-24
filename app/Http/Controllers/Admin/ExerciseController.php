<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    private function logAction(string $action, string $description): void
    {
        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }

    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));
        $categoryId = $request->get('category');
        $photoStatus = $request->get('photo');

        $query = Exercise::with('category');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('machine_name', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryId) {
            $query->where('exercise_category_id', $categoryId);
        }

        if ($photoStatus === 'with-photo') {
            $query->whereNotNull('image_path');
        }

        if ($photoStatus === 'without-photo') {
            $query->whereNull('image_path');
        }

        $totalExercises = Exercise::count();
        $totalWithPhoto = Exercise::whereNotNull('image_path')->count();
        $totalWithoutPhoto = Exercise::whereNull('image_path')->count();
        $totalCategories = ExerciseCategory::count();

        $categories = ExerciseCategory::orderBy('name')->get();

        $exercises = $query
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.exercises.index', compact(
            'exercises',
            'categories',
            'totalExercises',
            'totalWithPhoto',
            'totalWithoutPhoto',
            'totalCategories'
        ));
    }

    public function create()
    {
        $categories = ExerciseCategory::orderBy('name')->get();

        return view('admin.exercises.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exercise_category_id' => 'required|exists:exercise_categories,id',
            'name' => 'required|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('exercises', 'public');
        }

        $exercise = Exercise::create($data);

        $this->logAction(
            'create_exercise',
            auth()->user()->name . " cadastrou o exercício {$exercise->name}."
        );

        return redirect()
            ->route('admin.exercises.index')
            ->with('success', 'Exercício cadastrado com sucesso.');
    }

    public function edit(Exercise $exercise)
    {
        $categories = ExerciseCategory::orderBy('name')->get();

        return view('admin.exercises.edit', compact('exercise', 'categories'));
    }

    public function update(Request $request, Exercise $exercise)
    {
        $oldName = $exercise->name;

        $data = $request->validate([
            'exercise_category_id' => 'required|exists:exercise_categories,id',
            'name' => 'required|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);

        $changedPhoto = false;

        if ($request->hasFile('image')) {
            if ($exercise->image_path) {
                Storage::disk('public')->delete($exercise->image_path);
            }

            $data['image_path'] = $request->file('image')->store('exercises', 'public');
            $changedPhoto = true;
        }

        $exercise->update($data);

        $this->logAction(
            'update_exercise',
            auth()->user()->name . " atualizou o exercício {$oldName} para {$exercise->name}."
        );

        if ($changedPhoto) {
            $this->logAction(
                'upload_photo',
                auth()->user()->name . " atualizou a foto do exercício {$exercise->name}."
            );
        }

        return redirect()
            ->route('admin.exercises.index')
            ->with('success', 'Exercício atualizado com sucesso.');
    }

    public function destroy(Exercise $exercise)
    {
        $exerciseName = $exercise->name;

        if ($exercise->image_path) {
            Storage::disk('public')->delete($exercise->image_path);
        }

        $exercise->delete();

        $this->logAction(
            'delete_exercise',
            auth()->user()->name . " removeu o exercício {$exerciseName}."
        );

        return redirect()
            ->route('admin.exercises.index')
            ->with('success', 'Exercício removido com sucesso.');
    }
}
