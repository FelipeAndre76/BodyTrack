<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExercisePhotoController extends Controller
{
    public function index(Request $request)
{
    $query = Exercise::with('category');

    if ($request->status === 'with-photo') {
        $query->whereNotNull('image_path');
    }

    if ($request->status === 'without-photo') {
        $query->whereNull('image_path');
    }

    $totalExercises = Exercise::count();

    $totalWithPhoto = Exercise::whereNotNull('image_path')->count();

    $totalWithoutPhoto = Exercise::whereNull('image_path')->count();

    $photoProgress = $totalExercises > 0
        ? round(($totalWithPhoto / $totalExercises) * 100)
        : 0;

    $exercises = $query
        ->orderBy('name')
        ->get();

    return view('admin.photos.index', compact(
        'exercises',
        'totalExercises',
        'totalWithPhoto',
        'totalWithoutPhoto',
        'photoProgress'
    ));
}

  public function update(Request $request, Exercise $exercise)
{
    $request->validate([
        'image' => 'required|image|max:4096'
    ]);

    if ($exercise->image_path) {
        Storage::disk('public')->delete($exercise->image_path);
    }

    $path = $request->file('image')->store('exercises', 'public');

    $exercise->update([
        'image_path' => $path
    ]);

    return response()->json([
        'success' => true,
        'image_url' => asset('storage/' . $path)
    ]);
}

public function destroy(Exercise $exercise)
{
    if ($exercise->image_path) {
        Storage::disk('public')->delete($exercise->image_path);
    }

    $exercise->update([
        'image_path' => null
    ]);

    return response()->json([
        'success' => true
    ]);
}
}
