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

    protected function casts(): array
    {
        return [
            'meal_date' => 'date',
            'quantity' => 'decimal:2',
            'protein' => 'decimal:2',
            'carbs' => 'decimal:2',
            'fat' => 'decimal:2',
            'calories' => 'decimal:2',
        ];
    }

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
