<?php

namespace Yassi\NestedForm\Tests;

use Illuminate\Filesystem\Filesystem;
use Laravel\Nova\Nova;
use Orchestra\Testbench\TestCase as Orchestra;
use Yassi\NestedForm\FieldServiceProvider;
use Yassi\NestedForm\Tests\Models\Comment;
use Yassi\NestedForm\Tests\Models\Post;
use Yassi\NestedForm\Tests\Models\User;
use Yassi\NestedForm\Tests\Models\Video;
use Yassi\NestedForm\Tests\Resources\Comment as CommentResource;
use Yassi\NestedForm\Tests\Resources\Post as PostResource;
use Yassi\NestedForm\Tests\Resources\User as UserResource;
use Yassi\NestedForm\Tests\Resources\Video as VideoResource;

abstract class TestCase extends Orchestra
{

    /**
     * Set up the tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->filesystem = new Filesystem();

        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->withFactories(__DIR__ . '/database/factories');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        $this->seedDatabase();

        Nova::resources([
            UserResource::class,
            PostResource::class,
            VideoResource::class,
            CommentResource::class,
        ]);
    }

    /**
     * Get the package provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            FieldServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    /**
     * Initialize the database.
     *
     * @return void
     */
    private function seedDatabase()
    {
        factory(User::class, 5)->create()->each(function ($user) {

            $user->posts()->saveMany(factory(Post::class, 5)->make());

            $user->videos()->saveMany(factory(Video::class, 5)->make());

            $user->posts->each(function ($post) {
                $post->comments()->saveMany(factory(Comment::class, 5)->make());
            });

            $user->videos->each(function ($video) {
                $video->comments()->saveMany(factory(Comment::class, 5)->make());
            });
        });
    }
}
