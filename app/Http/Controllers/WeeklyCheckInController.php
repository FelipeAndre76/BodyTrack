<?php

namespace App\Http\Controllers;

use App\Models\WeeklyCheckIn;
use App\Models\WeightLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WeeklyCheckInController extends Controller
{
    public function index()
    {
        $checkIns = WeeklyCheckIn::where('user_id', Auth::id())
            ->select([
                'id',
                'user_id',
                'check_in_date',
                'weight',
                'energy_level',
                'mood_level',
                'sleep_quality',
                'notes',
                'photo_path',
                'photo_mime',
                'photo_size',
                'created_at',
                'updated_at',
            ])
            ->latest('check_in_date')
            ->latest()
            ->get();

        $latestCheckIn = $checkIns->first();
        $previousCheckIn = $checkIns->skip(1)->first();
        $weightChange = null;

        if ($latestCheckIn?->weight && $previousCheckIn?->weight) {
            $weightChange = (float) $latestCheckIn->weight - (float) $previousCheckIn->weight;
        }

        $averages = [
            'energy' => round($checkIns->take(4)->avg('energy_level') ?: 0, 1),
            'mood' => round($checkIns->take(4)->avg('mood_level') ?: 0, 1),
            'sleep' => round($checkIns->take(4)->avg('sleep_quality') ?: 0, 1),
        ];

        return view('weekly-check-ins', compact(
            'checkIns',
            'latestCheckIn',
            'previousCheckIn',
            'weightChange',
            'averages'
        ));
    }

    public function gallery()
    {
        $photoCheckIns = WeeklyCheckIn::where('user_id', Auth::id())
            ->select([
                'id',
                'user_id',
                'check_in_date',
                'weight',
                'notes',
                'photo_path',
                'photo_mime',
                'photo_size',
                'created_at',
                'updated_at',
            ])
            ->where(function ($query) {
                $query->whereNotNull('photo_size')
                    ->orWhereNotNull('photo_path');
            })
            ->orderBy('check_in_date')
            ->orderBy('created_at')
            ->get();

        $firstPhoto = $photoCheckIns->first();
        $latestPhoto = $photoCheckIns->last();
        $weightDiff = null;
        $daysDiff = null;

        if ($firstPhoto && $latestPhoto && $firstPhoto->id !== $latestPhoto->id) {
            if ($firstPhoto->weight && $latestPhoto->weight) {
                $weightDiff = (float) $latestPhoto->weight - (float) $firstPhoto->weight;
            }

            $daysDiff = $firstPhoto->check_in_date->diffInDays($latestPhoto->check_in_date);
        }

        return view('evolution-gallery', compact(
            'photoCheckIns',
            'firstPhoto',
            'latestPhoto',
            'weightDiff',
            'daysDiff'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'weight' => 'nullable|numeric|min:1|max:500',
            'energy_level' => 'nullable|integer|min:1|max:5',
            'mood_level' => 'nullable|integer|min:1|max:5',
            'sleep_quality' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:700',
        ]);

        $photoData = null;
        $photoMime = null;
        $photoSize = null;

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoData = file_get_contents($photo->getRealPath());
            $photoMime = $photo->getMimeType();
            $photoSize = $photo->getSize();
        }

        unset($validated['photo']);

        $checkIn = WeeklyCheckIn::create([
            ...$validated,
            'user_id' => Auth::id(),
            'photo_mime' => $photoMime,
            'photo_size' => $photoSize,
            'photo_data' => $photoData,
        ]);

        if (!empty($validated['weight'])) {
            WeightLog::create([
                'user_id' => Auth::id(),
                'weight' => $validated['weight'],
                'recorded_at' => $validated['check_in_date'],
            ]);

            Auth::user()->profile?->update([
                'current_weight' => $validated['weight'],
            ]);
        }

        return redirect()->route('check-ins.index')
            ->with('success', 'Check-in semanal salvo com sucesso!');
    }

    public function destroy(WeeklyCheckIn $checkIn)
    {
        if ($checkIn->user_id !== Auth::id()) {
            abort(403);
        }

        $checkIn->delete();

        return redirect()->route('check-ins.index')
            ->with('success', 'Check-in removido com sucesso!');
    }

    public function photo(WeeklyCheckIn $checkIn)
    {
        if ($checkIn->user_id !== Auth::id()) {
            abort(403);
        }

        if ($checkIn->photo_data) {
            $photoData = is_resource($checkIn->photo_data)
                ? stream_get_contents($checkIn->photo_data)
                : $checkIn->photo_data;

            return response($photoData)
                ->header('Content-Type', $checkIn->photo_mime ?: 'image/jpeg')
                ->header('Cache-Control', 'private, max-age=86400');
        }

        if ($checkIn->photo_path && Storage::disk('public')->exists($checkIn->photo_path)) {
            return response()->file(Storage::disk('public')->path($checkIn->photo_path));
        }

        abort(404);
    }
}
