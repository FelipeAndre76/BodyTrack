<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'display_format',
        'caption',
        'photo_mime',
        'photo_size',
        'photo_data',
        'likes_count',
        'comments_count',
    ];

    protected function casts(): array
    {
        return [
            'photo_size' => 'integer',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(SocialPostLike::class);
    }

    public function comments()
    {
        return $this->hasMany(SocialPostComment::class);
    }

    public function saves()
    {
        return $this->hasMany(SocialPostSave::class);
    }

    public function likedByUser(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->likes->contains('user_id', $userId);
    }

    public function savedByUser(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->saves->contains('user_id', $userId);
    }
}
