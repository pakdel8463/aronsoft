<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\Newsletter;
use App\Models\Portfolio;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /**
     * Admin dashboard (panel.home).
     */
    public function index()
    {
        $stats = [
            'posts' => $this->safe(fn () => Post::count()),
            'posts_published' => $this->safe(fn () => Post::where('status', 'published')->count()),
            'portfolios' => $this->safe(fn () => Portfolio::count()),
            'leads_new' => $this->safe(fn () => Lead::where('is_contacted', false)->count()),
            'leads_total' => $this->safe(fn () => Lead::count()),
            'messages_new' => $this->safe(fn () => ContactMessage::where('status', 'new')->count()),
            'comments_pending' => $this->safe(fn () => Comment::where('is_approved', false)->count()),
            'newsletter' => $this->safe(fn () => Newsletter::where('is_active', true)->count()),
            'orders' => $this->tableCount('orders', 'App\\Models\\Order'),
            'tickets_open' => $this->openTickets(),
        ];

        return view('admin.home', [
            'stats' => $stats,
            'recentLeads' => $this->safe(fn () => Lead::latest()->limit(5)->get(), collect()),
            'recentMessages' => $this->safe(fn () => ContactMessage::latest()->limit(5)->get(), collect()),
            'recentComments' => $this->safe(fn () => Comment::with('post:id,title,slug')->latest()->limit(5)->get(), collect()),
            'chart' => $this->leadsChart(30),
        ]);
    }

    /** Leads per day for the last $days days (zero-filled). */
    private function leadsChart(int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $rows = $this->safe(
            fn () => Lead::where('created_at', '>=', $start)
                ->get(['created_at'])
                ->groupBy(fn ($l) => $l->created_at->format('Y-m-d'))
                ->map->count(),
            collect()
        );

        $points = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i);
            $points[] = ['date' => $d->format('Y-m-d'), 'label' => $d->format('m/d'), 'count' => (int) ($rows[$d->format('Y-m-d')] ?? 0)];
        }

        return ['points' => $points, 'total' => array_sum(array_column($points, 'count')), 'max' => max(1, ...array_column($points, 'count'))];
    }

    private function tableCount(string $table, string $model): ?int
    {
        return $this->safe(function () use ($table, $model) {
            if (class_exists($model)) {
                return $model::count();
            }

            return Schema::hasTable($table) ? DB::table($table)->count() : null;
        });
    }

    private function openTickets(): ?int
    {
        return $this->safe(function () {
            if (! Schema::hasTable('tickets')) {
                return null;
            }
            $q = DB::table('tickets');
            if (Schema::hasColumn('tickets', 'status')) {
                $q->whereIn('status', ['open', 'new', 'pending', 'waiting_admin']);
            }

            return $q->count();
        });
    }

    /** Run a query; never let a missing table/column break the dashboard. */
    private function safe(callable $fn, mixed $fallback = 0): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);

            return $fallback instanceof Collection ? $fallback : $fallback;
        }
    }
}
