<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishedScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_only_lists_published_posts(): void
    {
        $pub = $this->makePost(['title' => 'Visible published']);
        $draft = $this->makePost(['title' => 'Secret draft', 'status' => 'draft']);
        $private = $this->makePost(['title' => 'Secret private', 'status' => 'private']);
        $future = $this->makePost(['title' => 'Secret scheduled', 'published_at' => now()->addWeek()]);

        $res = $this->get('/blog')->assertOk();
        $res->assertSee('Visible published');
        $res->assertDontSee('Secret draft');
        $res->assertDontSee('Secret private');
        $res->assertDontSee('Secret scheduled');
    }

    public function test_unpublished_post_pages_are_404_for_visitors(): void
    {
        $pub = $this->makePost();
        $draft = $this->makePost(['status' => 'draft']);
        $future = $this->makePost(['published_at' => now()->addWeek()]);

        $this->get('/blog/'.$pub->slug)->assertOk();
        $this->get('/blog/'.$draft->slug)->assertNotFound();
        $this->get('/blog/'.$future->slug)->assertNotFound();
    }

    public function test_staff_can_preview_a_draft(): void
    {
        $draft = $this->makePost(['status' => 'draft']);

        $this->actingAs($this->admin())->get('/blog/'.$draft->slug)->assertOk();
    }

    public function test_blog_category_excludes_drafts(): void
    {
        $cat = Category::create(['name' => 'Laravel', 'slug' => 'laravel', 'is_active' => true]);
        $pub = $this->makePost(['title' => 'Cat visible']);
        $draft = $this->makePost(['title' => 'Cat hidden', 'status' => 'draft']);
        $cat->posts()->attach([$pub->id, $draft->id]);

        $this->get('/blog/category/laravel')->assertOk()
            ->assertSee('Cat visible')
            ->assertDontSee('Cat hidden');
    }

    public function test_portfolio_index_renders_and_lists_published_only(): void
    {
        $this->makePortfolio(['title' => 'Shown project']);
        $this->makePortfolio(['title' => 'Hidden project', 'status' => 'draft']);

        $this->get('/portfolios')->assertOk()
            ->assertSee('Shown project')
            ->assertDontSee('Hidden project');
    }

    public function test_draft_portfolio_is_404(): void
    {
        $p = $this->makePortfolio(['status' => 'draft']);
        $this->get('/portfolio/'.$p->slug)->assertNotFound();
    }

    public function test_comments_cannot_be_posted_on_drafts(): void
    {
        $draft = $this->makePost(['status' => 'draft']);

        $this
            ->postJson(route('comments.store', $draft->slug), [
                'body' => 'hello there', 'name' => 'A', 'email' => 'a@b.co', 'captcha' => $this->captcha(),
            ])->assertNotFound();
    }
}
