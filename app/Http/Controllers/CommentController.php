<?php

namespace App\Http\Controllers;

use App\Helpers\Notifier;
use App\Models\Comment;
use App\Models\Post;
use App\Captcha\Captcha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        // فقط روی پست‌های منتشرشده (و دارای امکان دیدگاه) می‌شود دیدگاه گذاشت
        abort_unless($post->status === 'published', 404);
        abort_if($post->commentable !== null && ! $post->commentable, 403);

        // honeypot
        if ($request->filled('website')) {
            return response()->json([
                'status' => 'success',
                'message' => 'دیدگاه شما ثبت شد و پس از تأیید نمایش داده می‌شود.',
            ]);
        }

        $request->validate([
            'body' => 'required|string|min:5|max:3000',
            'name' => auth()->check() ? 'nullable' : 'required|string|max:255',
            'email' => auth()->check() ? 'nullable' : 'required|email|max:190',
            'parent_id' => ['nullable', Rule::exists('comments', 'id')->where('post_id', $post->id)],
            'captcha' => Captcha::rules(),
        ]);

        $comment = new Comment();
        $comment->post_id = $post->id;
        $comment->user_id = auth()->id();
        $comment->parent_id = $request->parent_id;
        $comment->name = auth()->check() ? auth()->user()->name : strip_tags($request->name);
        $comment->email = auth()->check() ? auth()->user()->email : $request->email;
        $comment->body = strip_tags($request->body);
        $comment->is_approved = false; // نیاز به تأیید ادمین
        $comment->save();

        Notifier::adminAlert(
            'دیدگاه جدید در انتظار تأیید',
            'دیدگاه جدید برای «'.$post->title.'»',
            ['نام' => $comment->name, 'ایمیل' => $comment->email, 'متن' => $comment->body],
            route('panel.comments.index'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'دیدگاه شما با موفقیت ثبت شد و پس از تأیید نمایش داده می‌شود.',
        ]);
    }
}
