<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Fa;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TicketController as ClientTicketController;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCannedResponse;
use App\Models\TicketDepartment;
use App\Models\TicketMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;
use Throwable;

/**
 * پنل مدیریت تیکت‌ها: /panel/tickets (دسترسی tickets.manage، از طریق middleware در routes/admin.php)
 */
class TicketController extends Controller
{
    private const TABS = [
        'open' => 'باز',
        'staff' => 'منتظر پشتیبان',
        'customer' => 'منتظر مشتری',
        'closed' => 'بسته‌شده',
        'all' => 'همه',
    ];

    public function index(Request $request)
    {
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'open';
        $f = [
            'q' => trim((string) $request->query('q')),
            'department' => (int) $request->query('department') ?: null,
            'priority' => in_array($request->query('priority'), array_keys(Ticket::PRIORITY_LABELS), true) ? $request->query('priority') : null,
            'assignee' => (string) $request->query('assignee', ''),
            'from' => trim((string) $request->query('from')),
            'to' => trim((string) $request->query('to')),
            'sla' => $request->boolean('sla'),
        ];

        $q = Ticket::query()->with(['user:id,name,email', 'department:id,name', 'assignee:id,name']);
        $this->applyTab($q, $tab);
        $q->search($f['q']);
        if ($f['department']) {
            $q->where('department_id', $f['department']);
        }
        if ($f['priority']) {
            $q->where('priority', $f['priority']);
        }
        if ($f['assignee'] === 'none') {
            $q->whereNull('assigned_to');
        } elseif ($f['assignee'] === 'me') {
            $q->where('assigned_to', $request->user()->id);
        } elseif (ctype_digit($f['assignee'])) {
            $q->where('assigned_to', (int) $f['assignee']);
        }
        if ($from = $this->parseDate($f['from'])) {
            $q->where('created_at', '>=', $from->startOfDay());
        }
        if ($to = $this->parseDate($f['to'])) {
            $q->where('created_at', '<=', $to->endOfDay());
        }
        if ($f['sla']) {
            $this->applyOverdue($q);
        }

        $tickets = $q->orderByDesc('staff_unread')->orderByDesc('last_reply_at')->orderByDesc('id')
            ->paginate((int) config('tickets.per_page', 20))->withQueryString();

        $byStatus = Ticket::query()->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        $counts = [
            'open' => (int) ($byStatus->sum() - ($byStatus[Ticket::CLOSED] ?? 0)),
            'staff' => (int) (($byStatus[Ticket::OPEN] ?? 0) + ($byStatus[Ticket::WAITING_ADMIN] ?? 0)),
            'customer' => (int) ($byStatus[Ticket::WAITING_CUSTOMER] ?? 0),
            'closed' => (int) ($byStatus[Ticket::CLOSED] ?? 0),
            'all' => (int) $byStatus->sum(),
            'overdue' => (int) $this->applyOverdue(Ticket::query())->count(),
            'unread' => (int) Ticket::where('staff_unread', true)->notClosed()->count(),
            'today' => (int) Ticket::where('created_at', '>=', now()->startOfDay())->count(),
        ];

        return view('admin.dash.tickets.index', [
            'tickets' => $tickets,
            'tab' => $tab,
            'tabs' => self::TABS,
            'counts' => $counts,
            'filters' => $f,
            'departments' => TicketDepartment::ordered()->get(['id', 'name']),
            'staff' => Ticket::staffUsers(),
        ]);
    }

    public function show(Request $request, Ticket $ticket)
    {
        if ($ticket->staff_unread) {
            Ticket::whereKey($ticket->id)->update(['staff_unread' => false]);
            $ticket->staff_unread = false;
        }

        $ticket->load(['user', 'department', 'assignee:id,name']);
        $messages = $ticket->messages()->with(['user:id,name', 'attachments'])->get();
        $events = $ticket->events()->with('user:id,name')->get();

        $orders = null;
        try {
            if (Schema::hasTable('orders')) {
                $orders = DB::table('orders')->where('user_id', $ticket->user_id)->count();
            }
        } catch (Throwable) {
        }

        $canned = TicketCannedResponse::query()->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('department_id')->orWhere('department_id', $ticket->department_id))
            ->orderBy('title')->get(['id', 'title', 'body']);

        return view('admin.dash.tickets.show', [
            'ticket' => $ticket,
            'messages' => $messages,
            'events' => $events,
            'otherTickets' => Ticket::where('user_id', $ticket->user_id)->where('id', '!=', $ticket->id)->latest('id')->limit(5)->get(['id', 'number', 'subject', 'status', 'created_at']),
            'ticketCount' => Ticket::where('user_id', $ticket->user_id)->count(),
            'ordersCount' => $orders,
            'canned' => $canned,
            'staff' => Ticket::staffUsers(),
            'departments' => TicketDepartment::ordered()->get(['id', 'name']),
            'rules' => ['max_files' => config('tickets.attachments.max_files'), 'max_mb' => round(config('tickets.attachments.max_kb') / 1024)],
        ]);
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $cfg = config('tickets.attachments');
        $data = $request->validate([
            'mode' => ['required', Rule::in(['reply', 'note'])],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            'close_after' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:'.$cfg['max_files']],
            'attachments.*' => ['file', 'max:'.$cfg['max_kb'], 'mimes:'.implode(',', array_keys($cfg['types'])), function ($a, $v, $fail) use ($cfg) {
                $mime = $v instanceof \Illuminate\Http\UploadedFile ? (string) $v->getMimeType() : '';
                if (! in_array($mime, collect($cfg['types'])->flatten()->all(), true)) {
                    $fail('نوع فایل پیوست مجاز نیست.');
                }
            }],
        ], [], ['body' => 'متن پیام', 'attachments' => 'پیوست‌ها', 'attachments.*' => 'پیوست']);

        $user = $request->user();
        $body = trim(str_replace(["\r\n", "\r"], "\n", preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data['body']) ?? ''));
        $isNote = $data['mode'] === 'note';

        $ticket->postMessage($user, $isNote ? TicketMessage::NOTE : TicketMessage::STAFF, $body, $request->file('attachments', []));

        if (! $isNote) {
            if (! $ticket->assigned_to) {
                $ticket->forceFill(['assigned_to' => $user->id])->save();
                $ticket->logEvent('assigned', $user, ['to' => $user->name]);
            }
            if (! empty($data['close_after'])) {
                $ticket->close($user);
            }
        }

        return redirect()->to(route('panel.tickets.show', $ticket).'#last')
            ->with('success', $isNote ? 'یادداشت داخلی ثبت شد (برای مشتری نمایش داده نمی‌شود).' : 'پاسخ برای مشتری ارسال شد.');
    }

    /** تغییر وضعیت / اولویت / دپارتمان / ارجاع. */
    public function update(Request $request, Ticket $ticket)
    {
        $staffIds = Ticket::staffUsers()->pluck('id')->all();
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Ticket::STATUS_LABELS))],
            'priority' => ['required', Rule::in(array_keys(Ticket::PRIORITY_LABELS))],
            'department_id' => ['nullable', 'exists:ticket_departments,id'],
            'assigned_to' => ['nullable', Rule::in($staffIds)],
        ], [], ['status' => 'وضعیت', 'priority' => 'اولویت', 'department_id' => 'دپارتمان', 'assigned_to' => 'مسئول']);

        $by = $request->user();

        if ($data['priority'] !== $ticket->priority) {
            $ticket->logEvent('priority', $by, ['from' => $ticket->priority, 'to' => $data['priority']]);
            $ticket->priority = $data['priority'];
        }
        $newDept = $data['department_id'] ?? null;
        if ((int) $newDept !== (int) $ticket->department_id) {
            $ticket->logEvent('department', $by, [
                'from' => $ticket->department?->name ?? '—',
                'to' => $newDept ? TicketDepartment::find($newDept)?->name : '—',
            ]);
            $ticket->department_id = $newDept ?: null;
        }
        $newAssignee = $data['assigned_to'] ?? null;
        if ((int) $newAssignee !== (int) $ticket->assigned_to) {
            $ticket->logEvent('assigned', $by, ['to' => $newAssignee ? User::find($newAssignee)?->name : null]);
            $ticket->assigned_to = $newAssignee ?: null;
        }
        $ticket->save();
        $ticket->changeStatus($data['status'], $by);

        return back()->with('success', 'تیکت به‌روزرسانی شد.');
    }

    public function bulk(Request $request)
    {
        $staffIds = Ticket::staffUsers()->pluck('id')->all();
        $data = $request->validate([
            'action' => ['required', Rule::in(['close', 'assign'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
            'assigned_to' => ['nullable', Rule::in($staffIds)],
        ], ['ids.required' => 'ابتدا حداقل یک تیکت را انتخاب کنید.', 'ids.min' => 'ابتدا حداقل یک تیکت را انتخاب کنید.']);

        if ($data['action'] === 'assign' && empty($data['assigned_to'])) {
            return back()->with('error', 'برای ارجاع، یک کارمند را انتخاب کنید.');
        }

        $by = $request->user();
        $assignee = $data['action'] === 'assign' ? User::find($data['assigned_to']) : null;
        $n = 0;
        foreach (Ticket::whereIn('id', $data['ids'])->get() as $t) {
            if ($data['action'] === 'close') {
                if (! $t->isClosed()) {
                    $t->close($by);
                    $n++;
                }
            } elseif ($t->assigned_to !== $assignee->id) {
                $t->forceFill(['assigned_to' => $assignee->id])->save();
                $t->logEvent('assigned', $by, ['to' => $assignee->name]);
                $n++;
            }
        }

        return back()->with('success', Fa::toFa($n).' تیکت '.($data['action'] === 'close' ? 'بسته شد.' : 'ارجاع داده شد.'));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('panel.tickets.index')->with('success', 'تیکت حذف شد.');
    }

    public function attachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        abort_unless($attachment->ticket_id === $ticket->id, 404);

        return ClientTicketController::sendAttachment($attachment, $request->boolean('inline'));
    }

    /* ------------------------------------------------------------------ helpers */

    private function applyTab(Builder $q, string $tab): void
    {
        match ($tab) {
            'open' => $q->notClosed(),
            'staff' => $q->needsStaff(),
            'customer' => $q->where('status', Ticket::WAITING_CUSTOMER),
            'closed' => $q->where('status', Ticket::CLOSED),
            default => null,
        };
    }

    /** تیکت‌های بدون پاسخ اول که مهلت SLA اولویت‌شان گذشته است. */
    private function applyOverdue(Builder $q): Builder
    {
        $q->whereNull('first_response_at')->notClosed()->where(function (Builder $w) {
            foreach ((array) config('tickets.sla.first_response_hours') as $priority => $hours) {
                $w->orWhere(fn (Builder $x) => $x->where('priority', $priority)->where('created_at', '<', now()->subHours((int) $hours)));
            }
        });

        return $q;
    }

    /** تاریخ شمسی (۱۴۰۵/۰۷/۱۲) یا میلادی (2026-10-04). */
    private function parseDate(?string $s): ?Carbon
    {
        $s = trim((string) Fa::digits($s));
        if ($s === '') {
            return null;
        }
        try {
            if (preg_match('~^(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})$~', $s, $m)) {
                return (int) $m[1] < 1700
                    ? Jalalian::fromFormat('Y/n/j', "{$m[1]}/{$m[2]}/{$m[3]}")->toCarbon()
                    : Carbon::create((int) $m[1], (int) $m[2], (int) $m[3]);
            }
        } catch (Throwable) {
        }

        return null;
    }
}
