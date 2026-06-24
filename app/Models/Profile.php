<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'height',
        'start_weight',
        'current_weight',
        'goal_weight',
        'goal',
        'birth_date',
        'gender',
        'activity_level',
        'nutrition_goal',
        'meals_per_day',
        'custom_protein_goal',
        'custom_carbs_goal',
        'custom_fat_goal',
        'custom_calories_goal',
        'custom_water_goal',
    ];

    protected function casts(): array
    {
        return [
            'height' => 'decimal:2',
            'start_weight' => 'decimal:2',
            'current_weight' => 'decimal:2',
            'goal_weight' => 'decimal:2',
            'birth_date' => 'date',
            'meals_per_day' => 'integer',
            'custom_protein_goal' => 'integer',
            'custom_carbs_goal' => 'integer',
            'custom_fat_goal' => 'integer',
            'custom_calories_goal' => 'integer',
            'custom_water_goal' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
