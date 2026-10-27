<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('status', 'all');

        $messages = ContactMessage::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($request->input('q'))).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)
                    ->orWhere('mobile', 'like', $like)->orWhere('subject', 'like', $like)->orWhere('message', 'like', $like));
            })
            ->when($tab === 'spam', fn ($q) => $q->where('is_spam', true))
            ->when($tab !== 'spam', fn ($q) => $q->where('is_spam', false))
            ->when(array_key_exists($tab, ContactMessage::STATUSES), fn ($q) => $q->where('status', $tab))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = ContactMessage::where('is_spam', false)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.dash.contact-messages.index', [
            'messages' => $messages,
            'statuses' => ContactMessage::STATUSES,
            'counts' => $counts,
            'spamCount' => ContactMessage::where('is_spam', true)->count(),
            'tab' => $tab,
            'q' => (string) $request->input('q', ''),
        ]);
    }

    /**
     * مرورگر (لینک اعلان‌ها و داشبورد) → صفحه جزئیات؛ ajax → JSON.
     * دیدن پیام جدید آن را «دیده شد» می‌کند.
     */
    public function show(Request $request, ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'reviewed']);
        }

        if ($request->expectsJson()) {
            return response()->json($contactMessage);
        }

        return view('admin.dash.contact-messages.show', [
            'message' => $contactMessage,
            'statuses' => ContactMessage::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,contacted,closed'],
        ]);

        $contactMessage->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'وضعیت پیام با موفقیت بروزرسانی شد']);
        }

        return back()->with('success', 'وضعیت پیام به «'.$contactMessage->status_label.'» تغییر کرد.');
    }

    /** اسپم / برگرداندن از اسپم (پیام اسپم‌شده «بسته» می‌شود تا شمارنده پیام‌های جدید را بالا نبرد) */
    public function toggleSpam(Request $request, ContactMessage $contactMessage)
    {
        $spam = ! $contactMessage->is_spam;

        $contactMessage->update([
            'is_spam' => $spam,
            'status' => $spam ? 'closed' : ($contactMessage->status === 'closed' ? 'reviewed' : $contactMessage->status),
        ]);

        return back()->with('success', $spam ? 'پیام به‌عنوان اسپم علامت‌گذاری شد.' : 'پیام از اسپم خارج شد.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('panel.contact-messages.index')->with('success', 'پیام با موفقیت حذف شد.');
    }
}
