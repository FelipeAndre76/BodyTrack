<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialStatus extends Model
{
    protected $fillable = [
        'user_id',
        'caption',
        'photo_mime',
        'photo_size',
        'photo_data',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'photo_size' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reactions()
    {
        return $this->hasMany(SocialStatusReaction::class);
    }
}
