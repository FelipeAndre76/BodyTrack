<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BodyProfileController extends Controller
{
    public function create()
    {
        return view('body-profile.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'height' => 'required|numeric|min:0.5|max:2.5',
            'start_weight' => 'required|numeric|min:1',
            'current_weight' => 'required|numeric|min:1',
            'goal_weight' => 'required|numeric|min:1',
            'goal' => 'required|in:weight_loss,muscle_gain,body_recomposition',
            'birth_date' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()->route('dashboard')
            ->with('success', 'Perfil corporal salvo com sucesso!');
    }
}
