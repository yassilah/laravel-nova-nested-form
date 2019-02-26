<?php

use Faker\Generator as Faker;
use Yassi\NestedForm\Tests\Models\Video;

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

$factory->define(Video::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'url' => $faker->url,
    ];
});
