<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ExerciseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExerciseCategory::withCount('exercises')
            ->orderBy('name')
            ->paginate(12);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:exercise_categories,name',
            'icon' => 'nullable|string|max:255',
        ]);

        ExerciseCategory::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria cadastrada com sucesso.');
    }

    public function edit(ExerciseCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ExerciseCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:exercise_categories,name,' . $category->id,
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

   public function destroy(ExerciseCategory $category)
{
    foreach ($category->exercises as $exercise) {
        if ($exercise->image_path) {
            Storage::disk('public')->delete($exercise->image_path);
        }

        $exercise->delete();
    }

    $category->delete();

    return redirect()
        ->route('admin.categories.index')
        ->with('success', 'Categoria e exercícios vinculados foram removidos.');
}

}
