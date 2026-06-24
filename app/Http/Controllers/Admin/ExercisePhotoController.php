<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExercisePhotoController extends Controller
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
        $status = $request->get('status');
        $search = trim($request->get('q', ''));

        $query = Exercise::with('category');

        if ($status === 'with-photo') {
            $query->whereNotNull('image_path');
        }

        if ($status === 'without-photo') {
            $query->whereNull('image_path');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $totalExercises = Exercise::count();
        $totalWithPhoto = Exercise::whereNotNull('image_path')->count();
        $totalWithoutPhoto = Exercise::whereNull('image_path')->count();

        $photoProgress = $totalExercises > 0
            ? round(($totalWithPhoto / $totalExercises) * 100)
            : 0;

        $exercises = $query
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

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

        $hadPhoto = !is_null($exercise->image_path);

        if ($exercise->image_path) {
            Storage::disk('public')->delete($exercise->image_path);
        }

        $path = $request->file('image')->store('exercises', 'public');

        $exercise->update([
            'image_path' => $path
        ]);

        $this->logAction(
            $hadPhoto ? 'update_photo' : 'upload_photo',
            auth()->user()->name .
            ($hadPhoto ? " atualizou" : " adicionou") .
            " a foto do exercício {$exercise->name}."
        );

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

        $this->logAction(
            'delete_photo',
            auth()->user()->name . " removeu a foto do exercício {$exercise->name}."
        );

        return response()->json([
            'success' => true
        ]);
    }
}
