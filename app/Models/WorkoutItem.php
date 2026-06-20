<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class WorkoutItem extends Model
{
    protected $fillable = [
         'workout_id',
    'exercise_id',
    'sets',
    'reps',
    'weight',
    'notes'
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }


}
