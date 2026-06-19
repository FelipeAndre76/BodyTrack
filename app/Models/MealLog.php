<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealLog extends Model
{
    protected $fillable = [
        'user_id',
        'meal_id',
        'food_id',
        'quantity',
        'meal_type',
        'protein',
        'carbs',
        'fat',
        'calories',
        'meal_date',
        'photo_path',
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
