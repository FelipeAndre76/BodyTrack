<?php

namespace App\Http\Controllers;

use App\Models\WeightLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeightLogController extends Controller
{
    public function create()
    {
        return view('weights.create');
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

        return redirect()->route('dashboard')
            ->with('success', 'Pesagem registrada com sucesso!');
    }
}
