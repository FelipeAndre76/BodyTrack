<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'name',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'calories_per_100g',
        'unit_type',
        'grams_per_unit',
    ];
}
