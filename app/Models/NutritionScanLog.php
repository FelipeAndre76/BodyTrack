<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NutritionScanLog extends Model
{
    protected $fillable = [
        'user_id',
        'food_id',
        'food_name',
        'label_photo_path',
        'serving_size',
        'calories',
        'protein',
        'carbs',
        'fat',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'raw_text',
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
