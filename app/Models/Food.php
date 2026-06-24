<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'user_id',
        'name',
        'source',
        'label_photo_path',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'calories_per_100g',
        'unit_type',
        'grams_per_unit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scanLogs()
    {
        return $this->hasMany(NutritionScanLog::class);
    }

    public function latestScanLog()
    {
        return $this->hasOne(NutritionScanLog::class)->latestOfMany();
    }
}
