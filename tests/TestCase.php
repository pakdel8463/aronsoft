<?php

namespace Tests;

use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function author(): User
    {
        return User::query()->first() ?? User::factory()->create();
    }

    protected function makePost(array $attrs = []): Post
    {
        static $n = 0;
        $n++;

        $post = Post::create(array_merge([
            'title' => "Test post {$n}",
            'slug' => "test-post-{$n}",
            'body' => '<p>Body</p>',
            'status' => 'published',
            'author_id' => $this->author()->id,
            'published_at' => now()->subDay(),
        ], $attrs));

        // the public post view expects at least one blog category
        $cat = Category::firstOrCreate(['slug' => 'general'], ['name' => 'عمومی', 'is_active' => true]);
        $post->categories()->syncWithoutDetaching([$cat->id]);

        return $post;
    }

    protected function makePortfolio(array $attrs = []): Portfolio
    {
        static $n = 0;
        $n++;

        return Portfolio::create(array_merge([
            'title' => "Test portfolio {$n}",
            'slug' => "test-portfolio-{$n}",
            'description' => 'desc',
            'featured_image' => 'images/x.jpg',
            'status' => 'published',
        ], $attrs));
    }

    protected function admin(): User
    {
        return User::factory()->create(['is_superuser' => 1, 'is_staff' => 1]);
    }

    /** a solved, single-use proof-of-work captcha answer (App\Captcha) */
    protected function captcha(): string
    {
        return \App\Captcha\Captcha::solvedPayload();
    }
}
