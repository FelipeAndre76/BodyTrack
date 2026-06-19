<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'user_id',
        'meal_type',
        'meal_date',
        'photo_path',
    ];

    public function items()
    {
        return $this->hasMany(MealLog::class);
    }
}
