<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightLog extends Model
{
    protected $fillable = [
        'user_id',
        'weight',
        'recorded_at'
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'date',
            'weight' => 'decimal:2',
        ];
    }
}
