<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:role-index', only: ['index']),
            new Middleware('can:role-create', only: ['create', 'store']),
            new Middleware('can:role-edit', only: ['edit', 'update']),
            new Middleware('can:role-delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('id')->paginate(20);

        return view('admin.dash.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.dash.roles.create', [
            'role' => new Role(),
            'permissionsByGroup' => $this->groupedPermissions(),
            'rolePermissions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = DB::transaction(function () use ($data, $request) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'is_superuser' => $this->mayGrantSuperuser($request) && $request->boolean('is_superuser'),
            ]);
            $role->permissions()->sync($data['permissions'] ?? []);

            return $role;
        });

        return redirect()->route('panel.roles.edit', $role)->with('success', 'نقش جدید ایجاد شد.');
    }

    public function edit(Role $role)
    {
        return view('admin.dash.roles.edit', [
            'role' => $role->loadCount('users'),
            'permissionsByGroup' => $this->groupedPermissions(),
            'rolePermissions' => $role->permissions()->pluck('permissions.id')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validated($request, $role);

        DB::transaction(function () use ($data, $request, $role) {
            $payload = ['name' => $data['name'], 'slug' => $data['slug']];
            // فقط مدیر کل می‌تواند پرچم سوپریوزر را تغییر دهد؛ برای بقیه مقدار فعلی حفظ می‌شود
            if ($this->mayGrantSuperuser($request)) {
                $payload['is_superuser'] = $request->boolean('is_superuser');
            }
            $role->update($payload);
            $role->permissions()->sync($data['permissions'] ?? []);
        });

        // کش دسترسی کاربر فعلی (در همین درخواست) نامعتبر شود
        $request->user()->forgetPermissionCache();

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->is_superuser && ! $request->user()->isSuperAdmin()) {
            abort(403);
        }

        DB::transaction(function () use ($role) {
            $role->users()->detach();
            $role->permissions()->detach();
            $role->delete();
        });

        return redirect()->route('panel.roles.index')->with('success', 'نقش حذف شد.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\.]+$/', Rule::unique('roles', 'slug')->ignore($role?->id)],
            'is_superuser' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], [
            'slug.regex' => 'شناسه فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و نقطه باشد.',
        ]);
    }

    private function mayGrantSuperuser(Request $request): bool
    {
        return (bool) $request->user()->isSuperAdmin();
    }

    private function groupedPermissions()
    {
        return Permission::orderBy('group')->orderBy('id')->get()->groupBy(fn ($p) => $p->group ?: 'سایر');
    }
}
