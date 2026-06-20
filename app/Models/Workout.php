<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'workout_date',
        'duration_minutes',
        'status'
    ];

    protected $casts = [
        'workout_date' => 'date'
    ];

    public function items()
    {
        return $this->hasMany(WorkoutItem::class);
    }



}
