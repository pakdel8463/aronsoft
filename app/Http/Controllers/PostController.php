<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(11);

        $allCategories = Category::where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '!=', 'portfolio');
            })
            ->withCount([
                'posts as posts_count' => function ($q) {
                    $q->published();
                },
            ])
            ->orderBy('name')
            ->get();

        SeoHelper::set([
            'title' => 'وبلاگ',
            'description' => 'مقالات تخصصی طراحی سایت، سئو، لاراول و دواپس از تیم آرون سافت.',
            'canonical' => route('blog.index'),
        ]);

        return view('front.t-one.posts.index', compact('posts', 'allCategories'));
    }

    public function show(string $post)
    {
        $query = Post::where('slug', $post);
        if (! $this->canPreview()) {
            $query->published();
        }
        $post = $query->firstOrFail();

        $category = $post->categories()
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '!=', 'portfolio');
            })
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        SeoHelper::set([
            'title' => $post->meta_title ?: $post->title,
            'description' => $post->meta_description ?: Str::limit(strip_tags((string) $post->body), 160),
            'image' => $post->featured_image ? asset($post->featured_image) : null,
        ]);

        return view('front.t-one.posts.show', compact('post', 'category'));
    }
}
