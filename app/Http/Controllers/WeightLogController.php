<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\WeightLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeightLogController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $latestLog = $weightLogs->first();
        $metrics = BodyMetrics::for($profile, (float) ($latestLog?->weight ?? 80));
        $latestWeight = $metrics['weight'];
        $startWeight = $metrics['start_weight'];
        $goalWeight = $metrics['goal_weight'];
        $weightLost = $metrics['weight_lost'];
        $remainingWeight = $metrics['remaining_weight'];
        $progressPercent = $metrics['progress_percent'];

        return view('weights.create', compact(
            'profile',
            'weightLogs',
            'latestWeight',
            'startWeight',
            'goalWeight',
            'weightLost',
            'remainingWeight',
            'progressPercent',
            'metrics'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:1',
            'recorded_at' => 'required|date',
        ]);

        $validated['user_id'] = Auth::id();

        WeightLog::create($validated);

        $profile = Auth::user()->profile;

        if ($profile) {
            $profile->update([
                'current_weight' => $validated['weight'],
            ]);
        }

        return redirect()->route('weights.create')
            ->with('success', 'Pesagem registrada com sucesso!');
    }
}
