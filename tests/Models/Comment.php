<?php

namespace Yassi\NestedForm\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * Relation to App\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to App\User
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Relation to App\Like
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
