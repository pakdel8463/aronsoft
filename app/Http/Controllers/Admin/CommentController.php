<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        if ($status !== 'all' && ! array_key_exists($status, Comment::STATUSES)) {
            $status = 'all';
        }
        $q = trim((string) $request->query('q', ''));

        $query = Comment::query()->with(['post:id,title,slug', 'user:id,name,is_staff,is_superuser', 'parent:id,name,body']);

        if ($status === 'all') {
            // حالت رشته‌ای: فقط ریشه‌ها؛ پاسخ‌ها زیر والد نمایش داده می‌شوند
            $query->whereNull('parent_id');
        } else {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(fn ($w) => $w->where('body', 'like', $like)->orWhere('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('ip', 'like', $like)
                ->orWhereHas('post', fn ($p) => $p->where('title', 'like', $like)));
        }
        if ($request->filled('post_id')) {
            $query->where('post_id', (int) $request->query('post_id'));
        }

        $comments = $query->latest('id')->paginate(20)->withQueryString();
        $threads = $status === 'all' ? $this->loadReplies($comments->getCollection()) : [];

        $counts = ['all' => Comment::count()];
        foreach (array_keys(Comment::STATUSES) as $s) {
            $counts[$s] = Comment::where('status', $s)->count();
        }

        return view('admin.dash.comments.index', [
            'comments' => $comments,
            'threads' => $threads,
            'status' => $status,
            'counts' => $counts,
            'q' => $q,
            'postFilter' => $request->filled('post_id') ? Post::withTrashed()->find((int) $request->query('post_id'), ['id', 'title']) : null,
            'open' => (int) $request->query('open', 0),
        ]);
    }

    /** لینک‌های قدیمی show → لیست با همان دیدگاه باز */
    public function show(Comment $comment)
    {
        return redirect()->route('panel.comments.index', ['open' => $comment->id, 'status' => $comment->parent_id ? 'all' : $comment->status])->withFragment('c'.$comment->id);
    }

    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);

        return $this->done($comment, 'دیدگاه تأیید شد.');
    }

    public function reject(Comment $comment)
    {
        $comment->update(['status' => 'rejected']);

        return $this->done($comment, 'دیدگاه رد شد.');
    }

    public function spam(Comment $comment)
    {
        $comment->update(['status' => 'spam']);

        return $this->done($comment, 'دیدگاه به‌عنوان اسپم علامت خورد.');
    }

    public function restore(Comment $comment)
    {
        $comment->update(['status' => 'pending']);

        return $this->done($comment, 'دیدگاه به «در انتظار» برگشت.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete(); // پاسخ‌ها با cascade حذف می‌شوند

        return $this->done($comment, 'دیدگاه حذف شد.');
    }

    /** پاسخ ادمین؛ به‌صورت تأییدشده و زیر دیدگاه والد (رشته‌ای) ثبت می‌شود */
    public function reply(Request $request, Comment $comment)
    {
        $data = $request->validate(['body' => 'required|string|min:2|max:3000']);

        $reply = Comment::create([
            'post_id' => $comment->post_id,
            'parent_id' => $comment->id,
            'user_id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'body' => strip_tags($data['body']),
            'status' => 'approved',
            'ip' => $request->ip(),
        ]);

        // با پاسخ دادن، دیدگاهِ در انتظار هم تأیید می‌شود (مگر اینکه تیک برداشته شود)
        if ($request->boolean('approve_parent', true) && $comment->status !== 'approved') {
            $comment->update(['status' => 'approved']);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $reply->id]);
        }

        return back()->with('success', 'پاسخ شما ثبت شد.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject,spam,pending,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $q = Comment::whereIn('id', $data['ids']);
        $n = (clone $q)->count();
        if ($data['action'] === 'delete') {
            $q->get()->each->delete();
        } else {
            $status = ['approve' => 'approved', 'reject' => 'rejected', 'spam' => 'spam', 'pending' => 'pending'][$data['action']];
            $q->get()->each(fn ($c) => $c->update(['status' => $status]));
        }

        return back()->with('success', "عملیات روی {$n} دیدگاه انجام شد.");
    }

    private function done(Comment $comment, string $msg)
    {
        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'status' => $comment->status ?? null, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * همه‌ی نوادگان ریشه‌های صفحه (چند سطح) را با چند کوئری می‌گیرد؛ خروجی: [rootId => [[comment, depth], ...]]
     *
     * @return array<int,array<int,array{0:Comment,1:int}>>
     */
    private function loadReplies($roots): array
    {
        $byParent = [];
        $frontier = $roots->pluck('id')->all();
        for ($level = 0; $level < 6 && $frontier; $level++) {
            $rows = Comment::whereIn('parent_id', $frontier)->with(['user:id,name,is_staff,is_superuser'])->orderBy('id')->get();
            foreach ($rows as $r) {
                $byParent[$r->parent_id][] = $r;
            }
            $frontier = $rows->pluck('id')->all();
        }

        $out = [];
        $walk = function ($id, $depth) use (&$walk, &$byParent, &$flat) {
            foreach ($byParent[$id] ?? [] as $c) {
                $flat[] = [$c, $depth];
                $walk($c->id, $depth + 1);
            }
        };
        foreach ($roots as $root) {
            $flat = [];
            $walk($root->id, 1);
            $out[$root->id] = $flat;
        }

        return $out;
    }
}
