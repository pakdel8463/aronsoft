<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_superuser',
        'is_staff',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function profile(){
        return $this->hasOne(UserProfile::class);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function extraPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function deniedPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_denied_permissions');
    }

    /** @var array{allowed: array<string,bool>, denied: array<string,bool>, super: bool}|null */
    protected ?array $permissionCache = null;

    /**
     * کاربر (یا یکی از نقش‌هایش) سوپریوزر است؟
     */
    public function isSuperAdmin(): bool
    {
        return $this->loadPermissionCache()['super'];
    }

    public function forgetPermissionCache(): void
    {
        $this->permissionCache = null;
    }

    protected function loadPermissionCache(): array
    {
        if ($this->permissionCache !== null) {
            return $this->permissionCache;
        }

        $super = $this->is_superuser == 1;
        $allowed = [];
        $denied = [];

        if (! $super) {
            $roles = $this->roles()->get(['roles.id', 'roles.is_superuser']);
            $super = $roles->contains(fn ($r) => (bool) $r->is_superuser);

            if (! $super) {
                foreach ($this->extraPermissions()->pluck('permissions.slug') as $slug) {
                    $allowed[$slug] = true;
                }
                if ($roles->isNotEmpty()) {
                    $rolePerms = Permission::query()
                        ->whereIn('id', \Illuminate\Support\Facades\DB::table('role_permission')
                            ->whereIn('role_id', $roles->pluck('id'))->select('permission_id'))
                        ->pluck('slug');
                    foreach ($rolePerms as $slug) {
                        $allowed[$slug] = true;
                    }
                }
                foreach ($this->deniedPermissions()->pluck('permissions.slug') as $slug) {
                    $denied[$slug] = true;
                }
            }
        }

        return $this->permissionCache = ['super' => $super, 'allowed' => $allowed, 'denied' => $denied];
    }

    /**
     * ترتیب بررسی: سوپریوزر (کاربر یا نقش) → همه چیز آزاد؛
     * سپس «دسترسی ممنوع‌شده» بر همه چیز غلبه می‌کند؛
     * در غیر این صورت دسترسی مستقیم یا دسترسی از طریق نقش.
     * نتیجه در طول یک درخواست کش می‌شود (سایدبار ده‌ها بار این متد را صدا می‌زند).
     */
    public function hasPermission($slug)
    {
        $c = $this->loadPermissionCache();

        if ($c['super']) {
            return true;
        }
        if (isset($c['denied'][$slug])) {
            return false;
        }

        return isset($c['allowed'][$slug]);
    }

    public function getAccessLevelAttribute(): string
    {
        return $this->is_superuser ? 'superuser' : ($this->is_staff ? 'staff' : 'user');
    }

    public function proformas()
    {
        return $this->hasMany(Proforma::class);
    }



}
