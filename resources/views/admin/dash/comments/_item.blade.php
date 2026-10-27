@php
    use App\Helpers\Fa;
    $stMap = ['pending' => ['در انتظار', 'amber'], 'approved' => ['تأییدشده', 'mint'], 'rejected' => ['ردشده', 'slate'], 'spam' => ['اسپم', 'coral']];
    [$stLabel, $stColor] = $stMap[$c->status] ?? $stMap['pending'];
    $isStaff = $c->user && ($c->user->is_staff || $c->user->is_superuser);
    $depth = $depth ?? 0;
@endphp
<article id="c{{ $c->id }}" data-comment data-id="{{ $c->id }}"
         class="rounded-2xl border {{ $open === $c->id ? 'border-aron-300 ring-4 ring-aron-100' : 'border-line' }} bg-white p-4 {{ $depth ? 'bg-slate-50/70' : '' }}">
    <div class="flex items-start gap-3">
        @if (! ($depth && false))
            <input type="checkbox" name="ids[]" value="{{ $c->id }}" class="a-check mt-2.5 shrink-0" data-c-check aria-label="انتخاب">
        @endif
        <span class="grid size-10 shrink-0 place-items-center rounded-2xl text-sm font-extrabold {{ $isStaff ? 'bg-aron-600 text-white' : 'bg-slate-100 text-slate-500' }}">{{ mb_substr($c->name ?: '؟', 0, 1) }}</span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <strong class="text-sm font-extrabold text-ink">{{ $c->name ?: 'ناشناس' }}</strong>
                @if ($isStaff)<x-admin.badge color="brand" icon="shield-check">مدیر</x-admin.badge>@endif
                <x-admin.badge :color="$stColor" dot>{{ $stLabel }}</x-admin.badge>
                <span class="text-[11px] text-slate-400">{{ Fa::toFa(jdate($c->created_at)->format('Y/m/d H:i')) }}</span>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-400">
                @if ($c->email)<a href="mailto:{{ $c->email }}" dir="ltr" class="hover:text-aron-700"><i class="ph ph-envelope-simple"></i> {{ $c->email }}</a>@endif
                @if ($c->ip)<span dir="ltr"><i class="ph ph-globe-simple"></i> {{ $c->ip }}</span>@endif
                @if ($c->post)<a href="{{ route('panel.posts.edit', $c->post->id) }}" class="max-w-[16rem] truncate font-bold text-aron-700 hover:underline"><i class="ph ph-article"></i> {{ $c->post->title }}</a>
                    <a href="{{ $c->post->publicUrl() }}#c{{ $c->id }}" target="_blank" rel="noopener" class="hover:text-aron-700" title="مشاهده در سایت"><i class="ph ph-arrow-square-out"></i></a>@endif
            </div>
            @if ($c->parent && $depth === 0)
                <p class="mt-2 line-clamp-1 rounded-xl bg-slate-50 px-3 py-1.5 text-xs text-slate-500"><i class="ph ph-arrow-bend-up-right"></i> در پاسخ به <strong>{{ $c->parent->name }}</strong>: «{{ \Illuminate\Support\Str::limit($c->parent->body, 80) }}»</p>
            @endif
            <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700" dir="auto">{{ $c->body }}</p>

            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                @if ($c->status !== 'approved')<button type="button" class="a-btn a-btn-success a-btn-sm" data-c-action="{{ route('panel.comments.approve', $c) }}"><i class="ph ph-check"></i>تأیید</button>@endif
                @if ($c->status === 'approved')<button type="button" class="a-btn a-btn-secondary a-btn-sm" data-c-action="{{ route('panel.comments.reject', $c) }}"><i class="ph ph-x"></i>لغو تأیید</button>
                @elseif ($c->status === 'pending')<button type="button" class="a-btn a-btn-secondary a-btn-sm" data-c-action="{{ route('panel.comments.reject', $c) }}"><i class="ph ph-x"></i>رد</button>@endif
                @if ($c->status === 'spam' || $c->status === 'rejected')<button type="button" class="a-btn a-btn-secondary a-btn-sm" data-c-action="{{ route('panel.comments.restore', $c) }}"><i class="ph ph-arrow-counter-clockwise"></i>برگشت به انتظار</button>
                @else<button type="button" class="a-btn a-btn-ghost a-btn-sm" data-c-action="{{ route('panel.comments.spam', $c) }}"><i class="ph ph-prohibit"></i>اسپم</button>@endif
                <button type="button" class="a-btn a-btn-soft a-btn-sm" data-c-reply="{{ $c->id }}"><i class="ph ph-arrow-bend-up-left"></i>پاسخ</button>
                <button type="button" class="a-btn a-btn-danger-soft a-btn-sm ms-auto" data-c-action="{{ route('panel.comments.destroy', $c) }}" data-method="DELETE" data-confirm="این دیدگاه{{ $depth === 0 ? ' و پاسخ‌هایش' : '' }} حذف شود؟"><i class="ph ph-trash"></i>حذف</button>
            </div>

            <div class="mt-3 hidden" data-reply-box="{{ $c->id }}">
                <div class="space-y-2 rounded-2xl border border-aron-100 bg-aron-50/40 p-3">
                    <textarea class="a-textarea !min-h-24" placeholder="پاسخ مدیر…" data-reply-text maxlength="3000"></textarea>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        @if ($c->status !== 'approved')<label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" class="a-check" checked data-reply-approve>همین دیدگاه هم تأیید شود</label>@else<span></span>@endif
                        <div class="flex gap-2"><button type="button" class="a-btn a-btn-ghost a-btn-sm" data-c-reply="{{ $c->id }}">انصراف</button><button type="button" class="a-btn a-btn-primary a-btn-sm" data-reply-send="{{ route('panel.comments.reply', $c) }}"><i class="ph ph-paper-plane-tilt"></i>ارسال پاسخ</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>
