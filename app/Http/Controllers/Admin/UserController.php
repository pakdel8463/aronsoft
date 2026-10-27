<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller implements HasMiddleware
{
    /** سطح دسترسی پایه: کاربر عادی / مدیر (staff) / مدیر کل (superuser) */
    public const LEVELS = [
        'user' => 'کاربر عادی',
        'staff' => 'مدیر (دسترسی به پنل)',
        'superuser' => 'مدیر کل',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('can:users.index', only: ['index', 'show']),
            new Middleware('can:users.create', only: ['create', 'store']),
            new Middleware('can:users.edit', only: ['edit', 'update', 'avatarUpload']),
            new Middleware('can:users.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = User::with('profile')->withCount('roles')->latest('id');

        if ($request->filled('search')) {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($request->input('search'))).'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhereHas('profile', fn ($p) => $p->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('mobile', 'like', $like));
            });
        }

        match ($request->input('role')) {
            'superuser' => $query->where('is_superuser', 1),
            'staff' => $query->where('is_staff', 1)->where('is_superuser', 0),
            'user' => $query->where('is_superuser', 0)->where('is_staff', 0),
            default => null,
        };

        if (in_array($request->input('status'), ['0', '1'], true)) {
            $query->whereHas('profile', fn ($q) => $q->where('status', (int) $request->input('status')));
        }

        $stats = [
            'total' => User::count(),
            'staff' => User::where(fn ($q) => $q->where('is_staff', 1)->orWhere('is_superuser', 1))->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];

        return view('admin.dash.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'stats' => $stats,
            'levels' => self::LEVELS,
        ]);
    }

    public function create()
    {
        return view('admin.dash.users.create', [
            'levels' => $this->assignableLevels(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'level' => ['required', Rule::in(array_keys($this->assignableLevels()))],
            'mobile' => ['nullable', 'string', 'max:20'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'boolean'],
            'avatar_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $active = $request->boolean('status', true);

        $user = DB::transaction(function () use ($data, $request, $active) {
            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'], // cast «hashed» در مدل
                'is_staff' => in_array($data['level'], ['staff', 'superuser']),
                'is_superuser' => $data['level'] === 'superuser',
            ]);
            $user->forceFill(['email_verified_at' => $active ? now() : null])->save();

            $user->profile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'] ?? null,
                'province' => $data['province'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => $active,
                'avatar' => $request->hasFile('avatar_file') ? $request->file('avatar_file')->store('avatars', 'public') : null,
            ]);

            if (! empty($data['roles'])) {
                $user->roles()->sync($this->allowedRoleIds($data['roles']));
            }

            return $user;
        });

        return redirect()->route('panel.users.show', $user)->with('success', 'کاربر با موفقیت ایجاد شد.');
    }

    public function show(User $user)
    {
        $user->load(['profile', 'roles.permissions', 'extraPermissions', 'deniedPermissions']);

        return view('admin.dash.users.show', [
            'user' => $user,
            'levels' => self::LEVELS,
            'proformaCount' => $user->proformas()->count(),
            'orderCount' => \App\Models\Order::where('user_id', $user->id)->count(),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorizeTarget($user);
        $user->load(['profile', 'roles', 'extraPermissions', 'deniedPermissions']);

        return view('admin.dash.users.edit', [
            'user' => $user,
            'levels' => $this->assignableLevels($user),
            'roles' => Role::orderBy('name')->get(),
            'permissionsByGroup' => Permission::orderBy('group')->orderBy('id')->get()->groupBy(fn ($p) => $p->group ?: 'سایر'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeTarget($user);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
            'level' => ['required', Rule::in(array_keys($this->assignableLevels($user)))],
            'mobile' => ['nullable', 'string', 'max:20'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'boolean'],
            'remove_avatar' => ['nullable', 'boolean'],
            'avatar_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'extra_permissions' => ['nullable', 'array'],
            'extra_permissions.*' => ['integer', 'exists:permissions,id'],
            'denied_permissions' => ['nullable', 'array'],
            'denied_permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $actor = $request->user();
        $isSelf = $actor->id === $user->id;
        $level = $data['level'];

        // کاربر نمی‌تواند سطح خودش را پایین بیاورد و آخرین مدیر کل هم نباید تنزل پیدا کند
        if ($user->is_superuser && $level !== 'superuser') {
            if ($isSelf) {
                return back()->withInput()->with('error', 'سطح دسترسی حساب خودتان را نمی‌توانید کاهش دهید.');
            }
            if (User::where('is_superuser', 1)->count() <= 1) {
                return back()->withInput()->with('error', 'حداقل یک «مدیر کل» باید در سیستم بماند.');
            }
        }

        $active = $request->boolean('status');
        if ($isSelf && ! $active) {
            return back()->withInput()->with('error', 'حساب خودتان را نمی‌توانید غیرفعال کنید.');
        }

        DB::transaction(function () use ($data, $request, $user, $level, $active) {
            $payload = [
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'is_staff' => in_array($level, ['staff', 'superuser']),
                'is_superuser' => $level === 'superuser',
            ];
            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }
            $user->update($payload);

            // فعال‌سازی حساب = تأیید ایمیل (مثل رفتار قبلی)
            if ($active && ! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $avatar = $user->profile?->avatar;
            if ($request->hasFile('avatar_file')) {
                $this->deleteAvatarFile($avatar);
                $avatar = $request->file('avatar_file')->store('avatars', 'public');
            } elseif ($request->boolean('remove_avatar')) {
                $this->deleteAvatarFile($avatar);
                $avatar = null;
            }

            $user->profile()->updateOrCreate(['user_id' => $user->id], [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'] ?? null,
                'province' => $data['province'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => $active,
                'avatar' => $avatar,
            ]);

            $user->roles()->sync($this->allowedRoleIds($data['roles'] ?? []));
            $user->extraPermissions()->sync($data['extra_permissions'] ?? []);
            // ممنوعه‌ها بر مستقیم غلبه می‌کنند؛ تکراری‌ها از مستقیم حذف می‌شوند
            $denied = array_values(array_diff($data['denied_permissions'] ?? [], $data['extra_permissions'] ?? []));
            $user->deniedPermissions()->sync($denied);
        });

        return redirect()->route('panel.users.show', $user)->with('success', 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
    }

    /** آپلود آواتار (ajax از صفحه ویرایش) */
    public function avatarUpload(Request $request, User $user)
    {
        $this->authorizeTarget($user);

        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $old = $user->profile?->avatar;
        $path = $request->file('file')->store('avatars', 'public');

        if (! $user->profile) {
            $parts = explode(' ', $user->name, 2);
            $user->profile()->create([
                'first_name' => $parts[0],
                'last_name' => $parts[1] ?? '',
                'avatar' => $path,
            ]);
        } else {
            $user->profile->update(['avatar' => $path]);
        }
        $this->deleteAvatarFile($old);

        return response()->json(['status' => 'success', 'path' => asset('storage/'.$path)]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeTarget($user);

        if ($request->user()->id === $user->id) {
            return back()->with('error', 'حساب خودتان را نمی‌توانید حذف کنید.');
        }
        if ($user->is_superuser && User::where('is_superuser', 1)->count() <= 1) {
            return back()->with('error', 'آخرین «مدیر کل» قابل حذف نیست.');
        }

        try {
            $avatar = $user->profile?->avatar;
            $user->delete();
            $this->deleteAvatarFile($avatar);
        } catch (QueryException $e) {
            report($e);

            return back()->with('error', 'این کاربر به داده‌های دیگر (مثلاً نوشته‌ها) وابسته است و حذف نشد. به‌جای حذف، حساب را غیرفعال کنید.');
        }

        return redirect()->route('panel.users.index')->with('success', 'کاربر حذف شد.');
    }

    /* -------------------------------------------------------------- */

    /** فقط مدیر کل می‌تواند مدیر کل بسازد یا حساب مدیر کل را ویرایش کند */
    private function assignableLevels(?User $target = null): array
    {
        $levels = self::LEVELS;
        if (! auth()->user()?->isSuperAdmin()) {
            unset($levels['superuser']);
            if ($target?->is_superuser) {
                $levels['superuser'] = self::LEVELS['superuser'];
            }
        }

        return $levels;
    }

    private function authorizeTarget(User $target): void
    {
        abort_if($target->is_superuser && ! auth()->user()->isSuperAdmin(), 403, 'ویرایش حساب مدیر کل فقط برای مدیر کل مجاز است.');
    }

    /** نقش‌های سوپریوزر را فقط مدیر کل می‌تواند واگذار کند */
    private function allowedRoleIds(array $ids): array
    {
        if (auth()->user()->isSuperAdmin()) {
            return $ids;
        }

        return Role::whereIn('id', $ids)->where('is_superuser', false)->pluck('id')->all();
    }

    private function deleteAvatarFile(?string $path): void
    {
        if ($path && ! preg_match('#^(https?:)?//|^/|^storage/#', $path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
