<?php

namespace Yassi\NestedForm\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relation to App\Post.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Relation to App\Video.
     */
    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Relation to App\Post.
     */
    public function post()
    {
        return $this->hasOne(Post::class);
    }

    /**
     * Relation to App\Video.
     */
    public function video()
    {
        return $this->hasOne(Video::class);
    }

    /**
     * Relation to App\User.
     */
    public function comments()
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }
}
