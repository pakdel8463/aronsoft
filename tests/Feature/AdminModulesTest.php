<?php

namespace Tests\Feature;

use App\Mail\NewsletterMail;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\Menu;
use App\Models\Newsletter;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Proforma;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** ماژول‌های مهاجرت‌شده پنل: کاربران، نقش‌ها، دسترسی‌ها، منوها، لیدها، پیام‌ها، خبرنامه، سفارش‌ها، پیش‌فاکتورها */
class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['is_staff' => 1, 'is_superuser' => 1])->save();

        return $u;
    }

    private function staffWith(array $slugs = []): User
    {
        $u = User::factory()->create();
        $u->forceFill(['is_staff' => 1, 'is_superuser' => 0])->save();
        foreach ($slugs as $s) {
            $p = Permission::firstOrCreate(['slug' => $s], ['name' => $s, 'group' => 'test']);
            $u->extraPermissions()->attach($p->id);
        }
        // Gate تعریف‌ها هنگام بوت ساخته می‌شوند؛ برای تست دوباره ثبت می‌کنیم
        foreach (Permission::all() as $p) {
            \Illuminate\Support\Facades\Gate::define($p->slug, fn ($user) => $user->hasPermission($p->slug));
        }

        return $u;
    }

    public function test_guests_and_non_staff_are_blocked_everywhere(): void
    {
        $urls = ['users', 'roles', 'permissions', 'menus', 'leads', 'contact-messages', 'newsletter', 'orders', 'proformas', 'proformas/create', 'newsletter/compose'];

        foreach ($urls as $u) {
            $this->get('/panel/'.$u)->assertRedirect(route('login'));
        }

        $customer = User::factory()->create();
        foreach ($urls as $u) {
            $this->actingAs($customer)->get('/panel/'.$u)->assertRedirect(route('home'));
        }
    }

    public function test_all_index_pages_render_for_admin(): void
    {
        $a = $this->makeAdmin();
        foreach (['users', 'roles', 'permissions', 'menus', 'leads', 'contact-messages', 'newsletter', 'newsletter/compose', 'orders', 'proformas', 'proformas/create', 'users/create', 'roles/create', 'permissions/create', 'menus/create'] as $u) {
            $this->actingAs($a)->get('/panel/'.$u)->assertOk();
        }
    }

    /* ---------------- users ---------------- */

    public function test_staff_without_permission_cannot_manage_users_roles_permissions(): void
    {
        $s = $this->staffWith();
        foreach (['users', 'roles', 'permissions', 'menus', 'leads'] as $u) {
            $this->actingAs($s)->get('/panel/'.$u)->assertForbidden();
        }
    }

    public function test_user_crud_and_safeguards(): void
    {
        Storage::fake('public');
        $a = $this->makeAdmin();

        $this->actingAs($a)->post(route('panel.users.store'), [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'email' => 'ali@example.test', 'password' => 'secret-pass-1',
            'level' => 'staff', 'status' => 1, 'mobile' => '09120000000',
            'avatar_file' => UploadedFile::fake()->image('a.jpg'),
        ])->assertRedirect();

        $u = User::where('email', 'ali@example.test')->firstOrFail();
        $this->assertSame(1, (int) $u->is_staff);
        $this->assertSame(0, (int) $u->is_superuser);
        $this->assertNotNull($u->profile->avatar);
        Storage::disk('public')->assertExists($u->profile->avatar);
        $this->actingAs($a)->get(route('panel.users.show', $u))->assertOk();
        $this->actingAs($a)->get(route('panel.users.edit', $u))->assertOk();

        $this->actingAs($a)->put(route('panel.users.update', $u), [
            'first_name' => 'علی', 'last_name' => 'محمدی', 'email' => 'ali@example.test', 'level' => 'user', 'status' => 1,
        ])->assertRedirect();
        $u->refresh();
        $this->assertSame('علی محمدی', $u->name);
        $this->assertSame(0, (int) $u->is_staff);

        // ایمیل تکراری و رمز کوتاه
        $this->actingAs($a)->post(route('panel.users.store'), [
            'first_name' => 'x', 'last_name' => 'y', 'email' => 'ali@example.test', 'password' => '123', 'level' => 'user',
        ])->assertSessionHasErrors(['email', 'password']);

        // ایجاد مدیر کل فقط توسط مدیر کل
        $s = $this->staffWith(['users.create', 'users.edit', 'users.index', 'users.delete']);
        $this->actingAs($s)->post(route('panel.users.store'), [
            'first_name' => 'x', 'last_name' => 'y', 'email' => 'boss@example.test', 'password' => 'secret-pass-1', 'level' => 'superuser',
        ])->assertSessionHasErrors('level');
        $this->actingAs($s)->get(route('panel.users.edit', $a))->assertForbidden();

        // حذف خود ممنوع، حذف دیگری مجاز
        $this->actingAs($a)->delete(route('panel.users.destroy', $a))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $a->id]);
        $this->actingAs($a)->delete(route('panel.users.destroy', $u))->assertRedirect(route('panel.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $u->id]);
    }

    public function test_avatar_upload_endpoint(): void
    {
        Storage::fake('public');
        $a = $this->makeAdmin();
        $u = User::factory()->create();

        $this->actingAs($a)->postJson(route('panel.users.avatar.upload', $u), ['file' => UploadedFile::fake()->image('p.png')])
            ->assertOk()->assertJsonPath('status', 'success');
        $this->assertNotNull($u->fresh()->profile->avatar);

        $this->actingAs($a)->postJson(route('panel.users.avatar.upload', $u), ['file' => UploadedFile::fake()->create('x.pdf', 10)])
            ->assertStatus(422);
    }

    /* ---------------- roles & permissions ---------------- */

    public function test_permissions_seed_and_crud_and_role_with_matrix(): void
    {
        $a = $this->makeAdmin();

        $this->actingAs($a)->post(route('panel.permissions.seed'))->assertRedirect();
        $this->assertDatabaseHas('permissions', ['slug' => 'users.index']);
        $count = Permission::count();

        $this->actingAs($a)->post(route('panel.permissions.store'), ['name' => 'تست', 'slug' => 'test-perm', 'group' => 'آزمایش'])->assertRedirect();
        $p = Permission::where('slug', 'test-perm')->firstOrFail();
        $this->actingAs($a)->post(route('panel.permissions.store'), ['name' => 'تکراری', 'slug' => 'test-perm'])->assertSessionHasErrors('slug');
        $this->actingAs($a)->put(route('panel.permissions.update', $p), ['name' => 'تست۲', 'slug' => 'test-perm', 'group' => 'آزمایش'])->assertRedirect();
        $this->assertSame('تست۲', $p->fresh()->name);

        $this->actingAs($a)->post(route('panel.roles.store'), [
            'name' => 'ویرایشگر', 'slug' => 'editor', 'permissions' => [$p->id],
        ])->assertRedirect();
        $role = Role::where('slug', 'editor')->firstOrFail();
        $this->assertSame([$p->id], $role->permissions()->pluck('permissions.id')->all());
        $this->actingAs($a)->get(route('panel.roles.edit', $role))->assertOk()->assertSee('test-perm');

        $this->actingAs($a)->put(route('panel.roles.update', $role), ['name' => 'ویرایشگر', 'slug' => 'editor', 'permissions' => []])->assertRedirect();
        $this->assertSame(0, $role->permissions()->count());

        // نقش به کاربر → دسترسی از طریق نقش
        $u = $this->staffWith();
        $role->permissions()->sync([Permission::where('slug', 'leads-index')->value('id')]);
        $u->roles()->attach($role->id);
        $this->assertTrue($u->fresh()->hasPermission('leads-index'));

        // ممنوعه بر نقش غلبه می‌کند
        $u->deniedPermissions()->attach(Permission::where('slug', 'leads-index')->value('id'));
        $this->assertFalse($u->fresh()->hasPermission('leads-index'));

        $this->actingAs($a)->delete(route('panel.roles.destroy', $role))->assertRedirect();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->actingAs($a)->delete(route('panel.permissions.destroy', $p))->assertRedirect();
        $this->assertDatabaseMissing('permissions', ['id' => $p->id]);
        $this->assertGreaterThan(0, $count);
    }

    public function test_non_superuser_cannot_grant_superuser_flag_on_role(): void
    {
        $s = $this->staffWith(['role-create']);
        $this->actingAs($s)->post(route('panel.roles.store'), ['name' => 'x', 'slug' => 'x-role', 'is_superuser' => 1])->assertRedirect();
        $this->assertFalse((bool) Role::where('slug', 'x-role')->value('is_superuser'));
    }

    /* ---------------- menus ---------------- */

    public function test_menu_crud_and_item_tree_save(): void
    {
        $a = $this->makeAdmin();

        $this->actingAs($a)->post(route('panel.menus.store'), ['name' => 'اصلی', 'slug' => 'main', 'position' => 'top'])->assertRedirect();
        $menu = Menu::firstOrFail();
        $this->actingAs($a)->post(route('panel.menus.store'), ['name' => 'دوم', 'slug' => 'second', 'position' => 'top'])->assertSessionHasErrors('position');
        $this->actingAs($a)->post(route('panel.menus.store'), ['name' => 'سفارشی', 'slug' => 'side', 'position' => '_custom', 'position_custom' => 'sidebar'])->assertRedirect();
        $this->assertDatabaseHas('menus', ['slug' => 'side', 'position' => 'sidebar']);

        $this->actingAs($a)->get(route('panel.menus.items', $menu))->assertOk();
        $this->actingAs($a)->get(route('panel.menus.edit', $menu))->assertOk();

        $this->actingAs($a)->postJson(route('panel.menus.save', $menu), ['items' => [
            ['title' => 'خانه', 'url' => '/', 'depth' => 0],
            ['title' => 'خدمات', 'url' => '/services', 'depth' => 0],
            ['title' => 'وب', 'url' => '/services/web', 'depth' => 1],
            ['title' => 'پرش عمق', 'url' => '#', 'depth' => 2],
            ['title' => 'پیوند', 'url' => 'https://example.com', 'depth' => 0],
        ]])->assertOk()->assertJsonPath('status', 'success');

        $items = $menu->items()->get();
        $this->assertCount(5, $items);
        $this->assertSame($items[1]->id, $items[2]->parent_id);
        $this->assertSame($items[2]->id, $items[3]->parent_id);
        $this->assertNull($items[4]->parent_id);

        // ذخیره دوباره ردیف‌های قدیمی را کامل پاک می‌کند (نه soft delete)
        $this->actingAs($a)->postJson(route('panel.menus.save', $menu), ['items' => [['title' => 'فقط یکی', 'url' => '/', 'depth' => 3]]])->assertStatus(422);
        $this->actingAs($a)->postJson(route('panel.menus.save', $menu), ['items' => [['title' => 'فقط یکی', 'url' => '/', 'depth' => 1]]])->assertOk();
        $this->assertSame(1, \App\Models\MenuItem::withTrashed()->where('menu_id', $menu->id)->count());
        $this->assertSame(0, (int) $menu->items()->first()->depth);

        // نشانی خطرناک
        $this->actingAs($a)->postJson(route('panel.menus.save', $menu), ['items' => [['title' => 'x', 'url' => 'javascript:alert(1)']]])->assertStatus(422);

        $this->actingAs($a)->delete(route('panel.menus.destroy', $menu))->assertRedirect();
        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
    }

    /* ---------------- leads ---------------- */

    public function test_lead_flow(): void
    {
        $a = $this->makeAdmin();
        $l = Lead::create(['name' => 'علی', 'lastname' => 'رضایی', 'mobile' => '0912', 'email' => 'l@example.test', 'request_type' => 'سئو', 'message' => 'سلام']);

        $this->actingAs($a)->get(route('panel.leads.index', ['q' => 'رضایی', 'status' => 'pending']))->assertOk()->assertSee('علی رضایی');
        $this->actingAs($a)->get(route('panel.leads.index', ['status' => 'contacted']))->assertOk()->assertDontSee('علی رضایی');
        $this->actingAs($a)->get(route('panel.leads.show', $l))->assertOk();
        $this->actingAs($a)->getJson(route('panel.leads.json', $l))->assertOk()->assertJsonPath('request_type', 'سئو')->assertJsonPath('name', 'علی رضایی');

        $this->actingAs($a)->post(route('panel.leads.status', $l), ['is_contacted' => 1])->assertRedirect();
        $this->assertTrue($l->fresh()->is_contacted);
        $this->actingAs($a)->patch(route('panel.leads.note', $l), ['admin_note' => 'تماس گرفتم'])->assertRedirect();
        $this->assertSame('تماس گرفتم', $l->fresh()->admin_note);

        $this->actingAs($a)->delete(route('panel.leads.destroy', $l))->assertRedirect(route('panel.leads.index'));
        $this->assertDatabaseMissing('leads', ['id' => $l->id]);
    }

    /* ---------------- contact messages ---------------- */

    public function test_contact_message_flow(): void
    {
        $a = $this->makeAdmin();
        $m = ContactMessage::create(['name' => 'سارا', 'email' => 's@example.test', 'subject' => 'سلام', 'message' => 'بدنه_یکتا_۱۲۳', 'status' => 'new']);

        $this->actingAs($a)->get(route('panel.contact-messages.index'))->assertOk()->assertSee('سارا');
        // نمایش مرورگر = صفحه جزئیات + «دیده شد»
        $this->actingAs($a)->get(route('panel.contact-messages.show', $m))->assertOk()->assertSee('mailto:s@example.test', false);
        $this->assertSame('reviewed', $m->fresh()->status);
        $this->actingAs($a)->getJson(route('panel.contact-messages.show', $m))->assertOk()->assertJsonPath('name', 'سارا');

        $this->actingAs($a)->patch(route('panel.contact-messages.status', $m), ['status' => 'contacted'])->assertRedirect();
        $this->assertSame('contacted', $m->fresh()->status);
        $this->actingAs($a)->patchJson(route('panel.contact-messages.status', $m), ['status' => 'bogus'])->assertStatus(422);

        $this->actingAs($a)->patch(route('panel.contact-messages.spam', $m))->assertRedirect();
        $this->assertTrue($m->fresh()->is_spam);
        $this->actingAs($a)->get(route('panel.contact-messages.index'))->assertDontSee('بدنه_یکتا_۱۲۳');
        $this->actingAs($a)->get(route('panel.contact-messages.index', ['status' => 'spam']))->assertSee('سارا');
        $this->actingAs($a)->patch(route('panel.contact-messages.spam', $m));
        $this->assertFalse($m->fresh()->is_spam);

        $this->actingAs($a)->delete(route('panel.contact-messages.destroy', $m))->assertRedirect(route('panel.contact-messages.index'));
        $this->assertSoftDeleted('contact_messages', ['id' => $m->id]);
    }

    /* ---------------- newsletter ---------------- */

    public function test_newsletter_filters_toggle_export_and_send(): void
    {
        Mail::fake();
        $a = $this->makeAdmin();
        $ok = Newsletter::create(['email' => 'ok@example.test', 'is_active' => true, 'confirmed_at' => now(), 'token' => 't1']);
        $pending = Newsletter::create(['email' => 'pending@example.test', 'is_active' => false, 'token' => 't2']);
        $gone = Newsletter::create(['email' => 'gone@example.test', 'is_active' => false, 'unsubscribed_at' => now(), 'token' => 't3']);

        $this->actingAs($a)->get(route('panel.newsletter.index', ['status' => 'confirmed']))->assertSee('ok@example.test')->assertDontSee('pending@example.test');
        $this->actingAs($a)->get(route('panel.newsletter.index', ['status' => 'pending']))->assertSee('pending@example.test')->assertDontSee('gone@example.test');
        $this->actingAs($a)->get(route('panel.newsletter.index', ['status' => 'unsubscribed']))->assertSee('gone@example.test')->assertDontSee('ok@example.test');

        $csv = $this->actingAs($a)->get(route('panel.newsletter.export', ['status' => 'confirmed']))->assertOk()->streamedContent();
        $this->assertStringContainsString('ok@example.test', $csv);
        $this->assertStringNotContainsString('pending@example.test', $csv);

        $this->actingAs($a)->post(route('panel.newsletter.toggle', $ok))->assertRedirect();
        $this->assertFalse($ok->fresh()->is_active);
        $this->assertNotNull($ok->fresh()->unsubscribed_at);
        $this->actingAs($a)->post(route('panel.newsletter.toggle', $ok));
        $this->assertTrue($ok->fresh()->is_active);
        $this->assertNull($ok->fresh()->unsubscribed_at);

        $this->actingAs($a)->post(route('panel.newsletter.send'), ['subject' => '', 'content' => ''])->assertSessionHasErrors(['subject', 'content']);
        $this->actingAs($a)->post(route('panel.newsletter.send'), ['subject' => 'سلام', 'content' => '<p>خبر</p><script>alert(1)</script>'])->assertRedirect(route('panel.newsletter.index'));
        Mail::assertQueuedCount(1);
        Mail::assertQueued(NewsletterMail::class, fn ($m) => $m->hasTo('ok@example.test') && ! str_contains($m->content, '<script') && str_contains($m->unsubscribeUrl, 't1'));

        $this->actingAs($a)->post(route('panel.newsletter.send'), ['subject' => 'تست', 'content' => 'x', 'test' => 1])->assertRedirect();
        Mail::assertQueued(NewsletterMail::class, fn ($m) => $m->hasTo($a->email));

        $this->actingAs($a)->delete(route('panel.newsletter.destroy', $pending))->assertRedirect();
        $this->assertDatabaseMissing('newsletters', ['id' => $pending->id]);
    }

    /* ---------------- orders ---------------- */

    public function test_order_flow_status_note_timeline_export(): void
    {
        $a = $this->makeAdmin();
        $o = Order::create(['type' => 'website', 'name' => 'مشتری', 'mobile' => '09120000000', 'description' => 'توضیح']);

        $this->actingAs($a)->get(route('panel.orders.index'))->assertOk()->assertSee($o->code);
        $this->actingAs($a)->get(route('panel.orders.show', $o))->assertOk()->assertSee($o->code);

        $this->actingAs($a)->patch(route('panel.orders.status', $o), ['status' => 'contacted'])->assertRedirect();
        $this->assertSame('contacted', $o->fresh()->status);
        $this->assertDatabaseHas('order_events', ['order_id' => $o->id, 'type' => 'status', 'from' => 'new', 'to' => 'contacted']);
        $this->actingAs($a)->patch(route('panel.orders.status', $o), ['status' => 'bogus'])->assertSessionHasErrors('status');

        $this->actingAs($a)->patch(route('panel.orders.note', $o), ['admin_note' => 'پیشنهاد قیمت داده شد'])->assertRedirect();
        $this->assertDatabaseHas('order_events', ['order_id' => $o->id, 'type' => 'note']);
        $this->actingAs($a)->get(route('panel.orders.show', $o))->assertSee('پیشنهاد قیمت داده شد');

        $csv = $this->actingAs($a)->get(route('panel.orders.export', ['status' => 'contacted']))->assertOk()->streamedContent();
        $this->assertStringContainsString($o->code, $csv);

        $this->actingAs($a)->delete(route('panel.orders.destroy', $o))->assertRedirect(route('panel.orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $o->id]);
    }

    /* ---------------- proformas ---------------- */

    public function test_proforma_builder_crud(): void
    {
        Mail::fake();
        $a = $this->makeAdmin();

        $this->actingAs($a)->post(route('panel.proformas.store'), ['client_name' => '', 'project_title' => '', 'items' => []])
            ->assertSessionHasErrors(['client_name', 'project_title', 'items']);
        $this->actingAs($a)->post(route('panel.proformas.store'), [
            'client_name' => 'x', 'project_title' => 'y', 'items' => [['title' => 'a', 'price' => 1000]],
        ])->assertSessionHasErrors('client_email');

        $this->actingAs($a)->post(route('panel.proformas.store'), [
            'client_name' => 'مشتری', 'client_email' => 'c@example.test', 'project_title' => 'فروشگاه', 'status' => 'draft',
            'items' => [5 => ['title' => 'طراحی', 'description' => 'd', 'price' => '12000000'], 9 => ['title' => 'سئو', 'price' => '2000000']],
        ])->assertRedirect(route('panel.proformas.index'));

        $p = Proforma::firstOrFail();
        $this->assertSame(14000000, (int) $p->total_amount);
        $this->assertSame([0, 1], array_keys($p->items));
        $this->assertSame('draft', $p->status);

        $this->actingAs($a)->get(route('panel.proformas.index'))->assertOk()->assertSee('فروشگاه');
        $this->actingAs($a)->get(route('panel.proformas.edit', $p))->assertOk()->assertSee('طراحی');

        $this->actingAs($a)->put(route('panel.proformas.update', $p), [
            'client_name' => 'مشتری', 'client_email' => 'c@example.test', 'project_title' => 'فروشگاه', 'status' => 'paid',
            'items' => [['title' => 'طراحی', 'price' => 5000]],
        ])->assertRedirect();
        $this->assertSame(5000, (int) $p->fresh()->total_amount);
        $this->assertSame('paid', $p->fresh()->status);

        $this->actingAs($a)->post(route('panel.proformas.send', $p))->assertRedirect();

        $this->actingAs($a)->delete(route('panel.proformas.destroy', $p))->assertRedirect(route('panel.proformas.index'));
        $this->assertDatabaseMissing('proformas', ['id' => $p->id]);
    }
}
