<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'height',
        'start_weight',
        'current_weight',
        'goal_weight',
        'goal',
        'birth_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
