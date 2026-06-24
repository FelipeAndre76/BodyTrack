<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialStatusReaction extends Model
{
    protected $fillable = [
        'social_status_id',
        'user_id',
        'reaction',
    ];

    public function status()
    {
        return $this->belongsTo(SocialStatus::class, 'social_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
