<?php

namespace App\Http\Controllers;

use App\BodyMetrics;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BodyProfileController extends Controller
{
    public function create()
    {
        $profile = Auth::user()->profile;
        $metrics = BodyMetrics::for($profile);
        $imc = $metrics['bmi'];
        $weightLost = $metrics['weight_lost'];
        $remainingWeight = $metrics['remaining_weight'];
        $progressPercent = $metrics['progress_percent'];
        $nutritionGoals = $metrics['nutrition_goals'];

        return view('body-profile.create', compact(
            'profile',
            'imc',
            'weightLost',
            'remainingWeight',
            'progressPercent',
            'metrics',
            'nutritionGoals'
        ));
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
            'gender' => 'nullable|in:male,female,not_informed',
            'activity_level' => 'required|in:sedentary,light,moderate,high,athlete',
            'nutrition_goal' => 'required|in:weight_loss,muscle_gain,body_recomposition',
            'meals_per_day' => 'required|integer|min:2|max:8',
            'custom_protein_goal' => 'nullable|integer|min:1|max:500',
            'custom_carbs_goal' => 'nullable|integer|min:1|max:1000',
            'custom_fat_goal' => 'nullable|integer|min:1|max:300',
            'custom_calories_goal' => 'nullable|integer|min:800|max:10000',
            'custom_water_goal' => 'nullable|integer|min:500|max:10000',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['nutrition_goal'] = $validated['nutrition_goal'] ?: $validated['goal'];

        foreach ([
            'custom_protein_goal',
            'custom_carbs_goal',
            'custom_fat_goal',
            'custom_calories_goal',
            'custom_water_goal',
        ] as $goalField) {
            $validated[$goalField] = $validated[$goalField] ?: null;
        }

        Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()->route('body-profile.create')
            ->with('success', 'Perfil corporal salvo com sucesso!');
    }
}
