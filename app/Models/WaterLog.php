<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterLog extends Model
{
    protected $fillable = [
        'user_id',
        'amount_ml',
        'recorded_at',
    ];
}
