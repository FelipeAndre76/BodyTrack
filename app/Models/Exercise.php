<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'exercise_category_id',
        'name',
        'machine_name',
        'description',
        'image_path',
    ];

    public function category()
    {
        return $this->belongsTo(ExerciseCategory::class, 'exercise_category_id');
    }
}
