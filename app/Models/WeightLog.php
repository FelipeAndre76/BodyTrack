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
}
