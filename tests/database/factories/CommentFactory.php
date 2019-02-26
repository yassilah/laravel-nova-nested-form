<?php

use Faker\Generator as Faker;
use Yassi\NestedForm\Tests\Models\Comment;
use Yassi\NestedForm\Tests\Models\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'content' => $faker->paragraph,
        'user_id' => User::inRandomOrder()->first()->id,
    ];
});
