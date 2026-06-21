<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    public function index()
    {
        $exercises = Exercise::with('category')
            ->orderBy('name')
            ->paginate(12);

        return view('admin.exercises.index', compact('exercises'));
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

        Exercise::create($data);

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
        $data = $request->validate([
            'exercise_category_id' => 'required|exists:exercise_categories,id',
            'name' => 'required|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($exercise->image_path) {
                Storage::disk('public')->delete($exercise->image_path);
            }

            $data['image_path'] = $request->file('image')->store('exercises', 'public');
        }

        $exercise->update($data);

        return redirect()
            ->route('admin.exercises.index')
            ->with('success', 'Exercício atualizado com sucesso.');
    }

    public function destroy(Exercise $exercise)
    {
        if ($exercise->image_path) {
            Storage::disk('public')->delete($exercise->image_path);
        }

        $exercise->delete();

        return redirect()
            ->route('admin.exercises.index')
            ->with('success', 'Exercício removido com sucesso.');
    }
}
