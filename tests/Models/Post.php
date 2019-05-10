<?php

namespace Yassi\NestedForm\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * Relation to App\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to App\Comment
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Relation to App\Comment
     */
    public function comment()
    {
        return $this->morphOne(Comment::class, 'commentable');
    }
}
