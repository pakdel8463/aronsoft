<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class PermissionController extends Controller implements HasMiddleware
{
    /** دسترسی‌های استانداردی که سایدبار و کنترلرهای پنل به آن‌ها تکیه دارند: [slug, نام, گروه] */
    public const DEFAULTS = [
        ['users.index', 'مشاهده کاربران', 'کاربران'],
        ['users.create', 'افزودن کاربر', 'کاربران'],
        ['users.edit', 'ویرایش کاربر', 'کاربران'],
        ['users.delete', 'حذف کاربر', 'کاربران'],
        ['role-index', 'مشاهده نقش‌ها', 'نقش‌ها'],
        ['role-create', 'ساخت نقش', 'نقش‌ها'],
        ['role-edit', 'ویرایش نقش', 'نقش‌ها'],
        ['role-delete', 'حذف نقش', 'نقش‌ها'],
        ['permission-index', 'مشاهده دسترسی‌ها', 'دسترسی‌ها'],
        ['permission-create', 'ساخت دسترسی', 'دسترسی‌ها'],
        ['permission-edit', 'ویرایش دسترسی', 'دسترسی‌ها'],
        ['permission-delete', 'حذف دسترسی', 'دسترسی‌ها'],
        ['menus-index', 'مدیریت منوها', 'محتوا'],
        ['comments-index', 'مدیریت دیدگاه‌ها', 'محتوا'],
        ['products-index', 'مدیریت محصولات', 'محتوا'],
        ['leads-index', 'مدیریت لیدها', 'فروش و ارتباط'],
        ['tickets.manage', 'مدیریت تیکت‌ها', 'تیکت‌ها'],
        ['settings.contact', 'تنظیمات اطلاعات تماس', 'تنظیمات'],
        ['product-pages.manage', 'مدیریت صفحات محصول (لندینگ)', 'محتوا'],
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('can:permission-index', only: ['index']),
            new Middleware('can:permission-create', only: ['create', 'store', 'seedDefaults']),
            new Middleware('can:permission-edit', only: ['edit', 'update']),
            new Middleware('can:permission-delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $permissions = Permission::withCount(['roles', 'users'])->orderBy('group')->orderBy('id')->get();

        return view('admin.dash.permissions.index', [
            'groups' => $permissions->groupBy(fn ($p) => $p->group ?: 'سایر'),
            'total' => $permissions->count(),
        ]);
    }

    public function create()
    {
        return view('admin.dash.permissions.create', [
            'permission' => new Permission(),
            'groups' => $this->groupNames(),
        ]);
    }

    public function store(Request $request)
    {
        Permission::create($this->validated($request));

        return redirect()->route('panel.permissions.index')->with('success', 'دسترسی جدید با موفقیت ایجاد شد.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.dash.permissions.edit', [
            'permission' => $permission->loadCount(['roles', 'users']),
            'groups' => $this->groupNames(),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $permission->update($this->validated($request, $permission));

        return redirect()->route('panel.permissions.index')->with('success', 'دسترسی با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->users()->detach();
        $permission->deniedUsers()->detach();
        $permission->delete();

        return redirect()->route('panel.permissions.index')->with('success', 'دسترسی با موفقیت حذف شد.');
    }

    /** ساخت دسترسی‌های استاندارد پنل (موارد موجود دست‌نخورده می‌مانند) */
    public function seedDefaults()
    {
        $created = 0;
        foreach (self::DEFAULTS as [$slug, $name, $group]) {
            $p = Permission::firstOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
            $created += $p->wasRecentlyCreated ? 1 : 0;
        }

        return redirect()->route('panel.permissions.index')
            ->with('success', $created ? "{$created} دسترسی استاندارد ساخته شد." : 'همه دسترسی‌های استاندارد از قبل وجود دارند.');
    }

    private function validated(Request $request, ?Permission $permission = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\.]+$/', Rule::unique('permissions', 'slug')->ignore($permission?->id)],
            'group' => ['nullable', 'string', 'max:255'],
        ], [
            'slug.regex' => 'شناسه فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره، نقطه و زیرخط باشد.',
        ]);
    }

    private function groupNames()
    {
        return Permission::whereNotNull('group')->distinct()->orderBy('group')->pluck('group');
    }
}
