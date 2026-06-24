<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExerciseCategoryController extends Controller
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
    $status = $request->get('status');

    $query = ExerciseCategory::withCount('exercises');

    if ($search !== '') {
        $query->where('name', 'like', "%{$search}%")
            ->orWhere('icon', 'like', "%{$search}%");
    }

    if ($status === 'with-exercises') {
        $query->has('exercises');
    }

    if ($status === 'empty') {
        $query->doesntHave('exercises');
    }

    $totalCategories = ExerciseCategory::count();

    $categoriesWithExercises = ExerciseCategory::has('exercises')->count();

    $emptyCategories = ExerciseCategory::doesntHave('exercises')->count();

    $categories = $query
        ->orderBy('name')
        ->paginate(12)
        ->withQueryString();

    return view('admin.categories.index', compact(
        'categories',
        'totalCategories',
        'categoriesWithExercises',
        'emptyCategories'
    ));
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

        $category = ExerciseCategory::create($data);

        $this->logAction(
            'create_category',
            auth()->user()->name . " cadastrou a categoria {$category->name}."
        );

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
        $oldName = $category->name;

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:exercise_categories,name,' . $category->id,
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($data);

        $this->logAction(
            'update_category',
            auth()->user()->name . " atualizou a categoria {$oldName} para {$category->name}."
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(ExerciseCategory $category)
    {
        $categoryName = $category->name;
        $exerciseCount = $category->exercises()->count();

        foreach ($category->exercises as $exercise) {
            if ($exercise->image_path) {
                Storage::disk('public')->delete($exercise->image_path);
            }

            $exercise->delete();
        }

        $category->delete();

        $this->logAction(
            'delete_category',
            auth()->user()->name .
            " removeu a categoria {$categoryName} e {$exerciseCount} exercício(s) vinculado(s)."
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria e exercícios vinculados foram removidos.');
    }
}
