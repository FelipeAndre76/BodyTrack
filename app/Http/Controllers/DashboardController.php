<?php

namespace App\Http\Controllers;

use App\Models\WeightLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $profile = $user->profile;

        $weightLogs = WeightLog::where('user_id', $user->id)
            ->orderBy('recorded_at')
            ->get();

        $latestWeight = $weightLogs->last()?->weight ?? $profile?->current_weight ?? 0;

        $startWeight = $profile?->start_weight ?? 0;
        $goalWeight = $profile?->goal_weight ?? 0;
        $height = $profile?->height ?? 1;

        $weightLost = $startWeight - $latestWeight;
        $remainingWeight = $latestWeight - $goalWeight;
        $imc = $height > 0 ? $latestWeight / ($height * $height) : 0;

        $chartWeights = $weightLogs->pluck('weight')->toArray();

        $chartDates = $weightLogs->map(function ($log) {
            return date('d/m', strtotime($log->recorded_at));
        })->toArray();

        return view('dashboard', compact(
            'profile',
            'latestWeight',
            'startWeight',
            'goalWeight',
            'weightLost',
            'remainingWeight',
            'imc',
            'chartWeights',
            'chartDates'
        ));
    }
}
