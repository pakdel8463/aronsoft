<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $latestProjects = Portfolio::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $latestPosts = Post::published()
            ->with('categories')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        return view('front.t-one.home', compact('latestProjects', 'latestPosts'));
    }
}
