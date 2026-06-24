<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\WaterLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaterLogController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $profile = Auth::user()->profile;

        $metrics = BodyMetrics::for($profile);
        $goal = $metrics['water_goal'];
        $trainingGoal = $metrics['training_water_goal'];

        $waterToday = WaterLog::where('user_id', Auth::id())
            ->where('recorded_at', $today)
            ->sum('amount_ml');

        $waterLogs = WaterLog::where('user_id', Auth::id())
            ->latest('recorded_at')
            ->latest()
            ->get();

        return view('water.index', compact(
            'waterToday',
            'waterLogs',
            'goal',
            'trainingGoal',
            'metrics'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount_ml' => 'required|integer|min:1',
        ]);

        WaterLog::create([
            'user_id' => Auth::id(),
            'amount_ml' => $validated['amount_ml'],
            'recorded_at' => now()->toDateString(),
        ]);

        return redirect()->route('water.index')
            ->with('success', 'Água registrada com sucesso!');
    }

public function destroy(WaterLog $waterLog)
{
    if ($waterLog->user_id !== Auth::id()) {
        abort(403);
    }

    $waterLog->delete();

    $today = now()->toDateString();

    $profile = Auth::user()->profile;

    $metrics = BodyMetrics::for($profile);
    $goal = $metrics['water_goal'];

    $waterToday = WaterLog::where('user_id', Auth::id())
        ->where('recorded_at', $today)
        ->sum('amount_ml');

    $percent = $goal > 0 ? ($waterToday / $goal) * 100 : 0;
    $percent = min(100, $percent);

    $remaining = max(0, $goal - $waterToday);

    if (request()->ajax()) {
        return response()->json([
            'success' => true,
            'waterToday' => $waterToday,
            'goal' => $goal,
            'percent' => number_format($percent, 1),
            'remaining' => $remaining,
        ]);
    }

    return redirect()->route('water.index')
        ->with('success', 'Registro removido com sucesso!');
}
}
