<?php

namespace Tests\Feature;

use App\Mail\TicketReplyMail;
use App\Mail\TicketStaffNotificationMail;
use App\Models\Permission;
use App\Models\Ticket;
use App\Models\TicketDepartment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private function dept(): TicketDepartment
    {
        return TicketDepartment::first() ?? TicketDepartment::create(['name' => 'پشتیبانی فنی', 'is_active' => true]);
    }

    private function user(): User
    {
        return User::factory()->create();
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'department_id' => $this->dept()->id,
            'priority' => 'normal',
            'subject' => 'سایت باز نمی‌شود',
            'body' => 'سلام، از دیشب سایت خطای ۵۰۰ می‌دهد. لطفاً بررسی کنید.',
        ], $over);
    }

    private function ticketFor(User $u, array $over = []): Ticket
    {
        return Ticket::openFor($u, array_merge(['department_id' => $this->dept()->id, 'subject' => 'موضوع تست', 'body' => 'متن تست تیکت'], $over));
    }

    private function staff(bool $permission = true): User
    {
        $u = User::factory()->create(['is_staff' => 1]);
        if ($permission) {
            $p = Permission::firstOrCreate(['slug' => 'tickets.manage'], ['name' => 'مدیریت تیکت‌ها']);
            $u->extraPermissions()->attach($p->id);
        }

        return $u;
    }

    public function test_guest_is_redirected_to_login_with_intended_url(): void
    {
        $this->get('/tickets/create')->assertRedirect(route('login'));
        $this->assertSame(url('/tickets/create'), session('url.intended'));
        $this->get('/tickets')->assertRedirect(route('login'));
    }

    public function test_create_ticket_with_number_and_staff_mail(): void
    {
        Mail::fake();
        $u = $this->user();

        $res = $this->actingAs($u)->post('/tickets', $this->payload());
        $t = Ticket::firstOrFail();

        $res->assertRedirect(route('tickets.show', $t));
        $this->assertMatchesRegularExpression('/^TKT-\d{6}$/', $t->number);
        $this->assertSame('TKT-'.(100000 + $t->id), $t->number);
        $this->assertSame('open', $t->status);
        $this->assertSame(1, $t->messages()->count());
        $this->assertSame('created', $t->events()->first()->type);
        Mail::assertSent(TicketStaffNotificationMail::class);

        $this->actingAs($u)->get('/tickets')->assertOk()->assertSee('سایت باز نمی‌شود');
        $this->actingAs($u)->get('/tickets/'.$t->number)->assertOk()->assertSee($t->number);
    }

    public function test_validation_errors(): void
    {
        $u = $this->user();
        $this->actingAs($u)->post('/tickets', ['subject' => 'x'])->assertSessionHasErrors(['department_id', 'subject', 'body']);
        $this->actingAs($u)->post('/tickets', $this->payload(['priority' => 'urgent']))->assertSessionHasErrors('priority');
        $this->assertSame(0, Ticket::count());
    }

    public function test_client_reply_moves_to_waiting_admin(): void
    {
        Mail::fake();
        $u = $this->user();
        $t = $this->ticketFor($u);
        $t->postMessage($this->staff(), TicketMessage::STAFF, 'پاسخ پشتیبان');
        $this->assertSame('waiting_customer', $t->fresh()->status);

        $this->actingAs($u)->post("/tickets/{$t->number}/reply", ['body' => 'ممنون، هنوز مشکل هست'])->assertRedirect();
        $t->refresh();
        $this->assertSame('waiting_admin', $t->status);
        $this->assertTrue($t->staff_unread);
        $this->assertSame(3, $t->messages()->count());
    }

    public function test_idor_other_user_cannot_see_reply_close_or_download(): void
    {
        Storage::fake('local');
        $owner = $this->user();
        $other = $this->user();
        $t = $this->ticketFor($owner);
        $msg = $t->messages()->first();
        $file = UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf');
        $att = \App\Models\TicketAttachment::storeUpload($msg, $file);
        $this->assertNotNull($att);

        $this->actingAs($other)->get("/tickets/{$t->number}")->assertNotFound();
        $this->actingAs($other)->post("/tickets/{$t->number}/reply", ['body' => 'هک'])->assertNotFound();
        $this->actingAs($other)->post("/tickets/{$t->number}/close")->assertNotFound();
        $this->actingAs($other)->post("/tickets/{$t->number}/rate", ['rating' => 5])->assertNotFound();
        $this->actingAs($other)->get("/tickets/{$t->number}/attachments/{$att->id}")->assertNotFound();
        $this->assertSame(1, $t->messages()->count());

        $this->actingAs($owner)->get("/tickets/{$t->number}/attachments/{$att->id}")->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        // سوپرکاربر هم در ناحیهٔ مشتری تیکت دیگران را نمی‌بیند
        $this->actingAs($this->admin())->get("/tickets/{$t->number}")->assertNotFound();
    }

    public function test_attachment_from_another_ticket_is_not_downloadable_via_own_ticket(): void
    {
        Storage::fake('local');
        $a = $this->user();
        $b = $this->user();
        $ta = $this->ticketFor($a);
        $tb = $this->ticketFor($b);
        $att = \App\Models\TicketAttachment::storeUpload($tb->messages()->first(), UploadedFile::fake()->create('x.pdf', 5, 'application/pdf'));

        $this->actingAs($a)->get("/tickets/{$ta->number}/attachments/{$att->id}")->assertNotFound();
    }

    public function test_attachment_validation(): void
    {
        Storage::fake('local');
        $u = $this->user();

        // پسوند غیرمجاز
        $this->actingAs($u)->post('/tickets', $this->payload(['attachments' => [UploadedFile::fake()->create('a.exe', 10, 'application/x-msdownload')]]))
            ->assertSessionHasErrors('attachments.0');
        // بیش از ۵ مگابایت
        $this->actingAs($u)->post('/tickets', $this->payload(['attachments' => [UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')]]))
            ->assertSessionHasErrors('attachments.0');
        // بیش از ۳ فایل
        $four = array_map(fn ($i) => UploadedFile::fake()->create("f{$i}.txt", 1, 'text/plain'), range(1, 4));
        $this->actingAs($u)->post('/tickets', $this->payload(['attachments' => $four]))->assertSessionHasErrors('attachments');
        // فایل PHP با پسوند png (بررسی محتوا)
        $path = tempnam(sys_get_temp_dir(), 'tk');
        file_put_contents($path, '<?php echo "pwn";');
        $evil = new UploadedFile($path, 'evil.png', null, null, true);
        $this->actingAs($u)->post('/tickets', $this->payload(['attachments' => [$evil]]))->assertSessionHasErrors('attachments.0');
        $this->assertSame(0, Ticket::count());

        // معتبر
        $this->actingAs($u)->post('/tickets', $this->payload(['attachments' => [UploadedFile::fake()->image('p.png', 40, 40), UploadedFile::fake()->create('r.pdf', 20, 'application/pdf')]]))
            ->assertSessionHasNoErrors();
        $t = Ticket::firstOrFail();
        $this->assertSame(2, $t->attachments()->count());
        foreach ($t->attachments as $a) {
            Storage::disk('local')->assertExists($a->path);
            $this->assertStringStartsWith('tickets/'.$t->id.'/', $a->path);
        }
    }

    public function test_filename_is_sanitized(): void
    {
        $n = \App\Models\TicketAttachment::sanitizeName("../../etc/pass\x00wd\u{202E}.png", 'png');
        $this->assertStringNotContainsString('/', $n);
        $this->assertStringNotContainsString("\u{202E}", $n);
        $this->assertStringEndsWith('.png', $n);
    }

    public function test_body_is_escaped_and_autolinked_safely(): void
    {
        $html = (string) TicketMessage::render("<script>alert(1)</script> https://example.com/a?b=1&c=2. javascript:alert(1) <img src=x onerror=y>");
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('href="https://example.com/a?b=1&amp;c=2"', $html);
        $this->assertStringContainsString('rel="nofollow noopener noreferrer ugc"', $html);
        $this->assertStringNotContainsString('href="javascript', $html);

        // و در صفحه
        $u = $this->user();
        $t = $this->ticketFor($u, ['body' => '<b onclick=x>hi</b>']);
        $this->actingAs($u)->get('/tickets/'.$t->number)->assertDontSee('<b onclick', false)->assertSee('&lt;b onclick', false);
    }

    public function test_close_reopen_and_rating(): void
    {
        $u = $this->user();
        $t = $this->ticketFor($u);

        $this->actingAs($u)->post("/tickets/{$t->number}/rate", ['rating' => 5])->assertForbidden(); // هنوز باز است
        $this->actingAs($u)->post("/tickets/{$t->number}/close")->assertRedirect();
        $this->assertSame('closed', $t->fresh()->status);

        $this->actingAs($u)->post("/tickets/{$t->number}/reply", ['body' => 'پاسخ بعد از بستن'])->assertSessionHas('error');
        $this->actingAs($u)->post("/tickets/{$t->number}/rate", ['rating' => 9])->assertSessionHasErrors('rating');
        $this->actingAs($u)->post("/tickets/{$t->number}/rate", ['rating' => 4, 'rating_comment' => 'عالی'])->assertRedirect();
        $this->assertSame(4, $t->fresh()->rating);
        $this->actingAs($u)->post("/tickets/{$t->number}/rate", ['rating' => 1])->assertForbidden(); // فقط یک بار

        $this->actingAs($u)->post("/tickets/{$t->number}/reopen")->assertRedirect();
        $t->refresh();
        $this->assertSame('waiting_admin', $t->status);
        $this->assertNull($t->rating);
    }

    public function test_reopen_window_expires(): void
    {
        $u = $this->user();
        $t = $this->ticketFor($u);
        $t->close($u);
        $t->forceFill(['closed_at' => now()->subDays(30)])->save();

        $this->actingAs($u)->post("/tickets/{$t->number}/reopen")->assertSessionHas('error');
        $this->assertSame('closed', $t->fresh()->status);
    }

    public function test_internal_notes_hidden_from_client_everywhere(): void
    {
        Storage::fake('local');
        $u = $this->user();
        $staff = $this->staff();
        $t = $this->ticketFor($u);
        $note = $t->postMessage($staff, TicketMessage::NOTE, 'یادداشت محرمانه داخلی');
        $att = \App\Models\TicketAttachment::storeUpload($note, UploadedFile::fake()->create('secret.pdf', 5, 'application/pdf'));
        $this->assertSame('open', $t->fresh()->status); // یادداشت داخلی وضعیت را تغییر نمی‌دهد
        $this->assertNull($t->fresh()->first_response_at);
        $t->postMessage($staff, TicketMessage::STAFF, 'پاسخ عمومی');
        $this->actingAs($u)->get('/tickets/'.$t->number)->assertOk()->assertSee('پاسخ عمومی')->assertDontSee('یادداشت محرمانه داخلی')->assertDontSee('secret.pdf');
        $this->actingAs($u)->get("/tickets/{$t->number}/attachments/{$att->id}")->assertNotFound();
        $this->actingAs($staff)->get('/panel/tickets/'.$t->number)->assertOk()->assertSee('یادداشت محرمانه داخلی');
    }

    public function test_admin_reply_changes_status_notifies_client_and_assigns(): void
    {
        Mail::fake();
        $u = $this->user();
        $staff = $this->staff();
        $t = $this->ticketFor($u);

        $this->actingAs($staff)->post("/panel/tickets/{$t->number}/reply", ['mode' => 'reply', 'body' => 'مشکل بررسی شد'])->assertRedirect();
        $t->refresh();
        $this->assertSame('waiting_customer', $t->status);
        $this->assertNotNull($t->first_response_at);
        $this->assertTrue($t->client_unread);
        $this->assertSame($staff->id, $t->assigned_to);
        Mail::assertSent(TicketReplyMail::class, fn ($m) => $m->hasTo($u->email));

        // مشتری که می‌خواند، نشانگر خوانده‌نشده پاک می‌شود
        $this->actingAs($u)->get('/tickets')->assertOk()->assertSee('پاسخ جدید');
        $this->actingAs($u)->get('/tickets/'.$t->number)->assertOk();
        $this->assertFalse($t->fresh()->client_unread);

        // پاسخ و بستن
        $this->actingAs($staff)->post("/panel/tickets/{$t->number}/reply", ['mode' => 'reply', 'body' => 'تمام شد', 'close_after' => 1]);
        $this->assertSame('closed', $t->fresh()->status);
    }

    public function test_admin_can_update_properties_and_events_are_logged(): void
    {
        $u = $this->user();
        $staff = $this->staff();
        $t = $this->ticketFor($u);
        $d2 = TicketDepartment::create(['name' => 'مالی', 'is_active' => true]);

        $this->actingAs($staff)->patch("/panel/tickets/{$t->number}", [
            'status' => 'waiting_customer', 'priority' => 'urgent', 'department_id' => $d2->id, 'assigned_to' => $staff->id,
        ])->assertRedirect();
        $t->refresh();
        $this->assertSame(['waiting_customer', 'urgent', $d2->id, $staff->id], [$t->status, $t->priority, $t->department_id, $t->assigned_to]);
        $types = $t->events()->pluck('type')->all();
        foreach (['created', 'priority', 'department', 'assigned', 'status'] as $type) {
            $this->assertContains($type, $types);
        }
        $this->actingAs($staff)->get('/panel/tickets/'.$t->number)->assertOk()->assertSee('گزارش فعالیت');
    }

    public function test_admin_area_requires_permission(): void
    {
        $u = $this->user();
        $t = $this->ticketFor($u);

        $this->actingAs($u)->get('/panel/tickets')->assertRedirect(route('home'));            // کاربر عادی
        $this->actingAs($this->staff(false))->get('/panel/tickets')->assertForbidden();       // کارمند بدون دسترسی
        $this->actingAs($this->staff(false))->post("/panel/tickets/{$t->number}/reply", ['mode' => 'reply', 'body' => 'xx'])->assertForbidden();
        $this->actingAs($this->staff())->get('/panel/tickets')->assertOk()->assertSee($t->number);
        $this->actingAs($this->admin())->get('/panel/tickets')->assertOk();
    }

    public function test_admin_index_filters_bulk_and_sla(): void
    {
        $u = $this->user();
        $staff = $this->staff();
        $a = $this->ticketFor($u, ['subject' => 'آلفا']);
        $b = $this->ticketFor($u, ['subject' => 'بتا']);
        $a->forceFill(['created_at' => now()->subDays(5)])->save();

        $this->actingAs($staff)->get('/panel/tickets?q=آلفا')->assertOk()->assertSee('آلفا')->assertDontSee('بتا');
        $this->actingAs($staff)->get('/panel/tickets?sla=1')->assertOk()->assertSee('آلفا')->assertDontSee('بتا');
        $this->assertSame('overdue', $a->fresh()->sla()['state']);

        $this->actingAs($staff)->post('/panel/tickets/bulk', ['action' => 'assign', 'ids' => [$a->id, $b->id], 'assigned_to' => $staff->id])->assertRedirect();
        $this->assertSame($staff->id, $b->fresh()->assigned_to);
        $this->actingAs($staff)->post('/panel/tickets/bulk', ['action' => 'close', 'ids' => [$a->id, $b->id]])->assertRedirect();
        $this->assertSame(2, Ticket::where('status', 'closed')->count());
        $this->actingAs($staff)->get('/panel/tickets?tab=closed')->assertOk()->assertSee('آلفا');
    }

    public function test_departments_and_canned_crud(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->get('/panel/tickets/departments')->assertOk();
        $this->actingAs($staff)->post('/panel/tickets/departments', ['name' => 'جدید', 'email' => 'd@example.com', 'is_active' => 1, 'sort_order' => 5])->assertRedirect();
        $d = TicketDepartment::where('name', 'جدید')->firstOrFail();
        $this->actingAs($staff)->put("/panel/tickets/departments/{$d->id}", ['name' => 'ویرایش‌شده', 'is_active' => 0])->assertRedirect();
        $this->assertFalse($d->fresh()->is_active);

        $t = $this->ticketFor($this->user(), ['department_id' => $d->id]);
        $this->actingAs($staff)->delete("/panel/tickets/departments/{$d->id}")->assertSessionHas('error');
        $this->assertNotNull($d->fresh());

        $this->actingAs($staff)->get('/panel/tickets/canned')->assertOk();
        $this->actingAs($staff)->post('/panel/tickets/canned', ['title' => 'سلام', 'body' => 'متن آماده', 'is_active' => 1])->assertRedirect();
        $c = \App\Models\TicketCannedResponse::firstWhere('title', 'سلام');
        $this->assertNotNull($c);
        $this->actingAs($staff)->get('/panel/tickets/'.$t->number)->assertOk()->assertSee('سلام');
        $this->actingAs($staff)->delete("/panel/tickets/canned/{$c->id}")->assertRedirect();
        $this->assertNull(\App\Models\TicketCannedResponse::find($c->id));
    }

    public function test_mail_failure_never_breaks_the_request(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $u = $this->user();
        $this->actingAs($u)->post('/tickets', $this->payload())->assertRedirect();
        $this->assertSame(1, Ticket::count());
    }

    public function test_posting_is_throttled(): void
    {
        $u = $this->user();
        $t = $this->ticketFor($u);
        for ($i = 0; $i < 12; $i++) {
            $this->actingAs($u)->post("/tickets/{$t->number}/reply", ['body' => 'پیام شماره '.$i]);
        }
        $this->actingAs($u)->post("/tickets/{$t->number}/reply", ['body' => 'پیام اضافه'])->assertStatus(429);
    }

    public function test_open_ticket_limit(): void
    {
        config(['tickets.max_open_per_user' => 2]);
        $u = $this->user();
        $this->ticketFor($u);
        $this->ticketFor($u);
        $this->actingAs($u)->post('/tickets', $this->payload())->assertSessionHasErrors('subject');
        $this->assertSame(2, Ticket::count());
    }

    public function test_seeded_departments_and_permission_exist(): void
    {
        $this->assertSame(['پشتیبانی فنی', 'فروش و سفارش', 'مالی', 'پیشنهاد و انتقاد'], TicketDepartment::ordered()->pluck('name')->all());
        $this->assertDatabaseHas('permissions', ['slug' => 'tickets.manage']);
    }
}
