<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostCommentLike extends Model
{
    protected $fillable = [
        'social_post_comment_id',
        'user_id',
    ];

    public function comment()
    {
        return $this->belongsTo(SocialPostComment::class, 'social_post_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
