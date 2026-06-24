<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostComment extends Model
{
    protected $fillable = [
        'social_post_id',
        'user_id',
        'parent_id',
        'body',
        'likes_count',
    ];

    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
        ];
    }

    public function post()
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(SocialPostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(SocialPostComment::class, 'parent_id')->oldest();
    }

    public function likes()
    {
        return $this->hasMany(SocialPostCommentLike::class);
    }

    public function likedByUser(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->likes->contains('user_id', $userId);
    }
}
