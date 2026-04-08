<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Role name constants — used in seeders and role checks.
     * These reference the `roles.name` column via the user_roles pivot table.
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_HR = 'hr';
    const ROLE_MANAGER = 'manager';
    const ROLE_EMPLOYEE = 'employee';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Employees managed by this user (via employees.manager_id → users.id).
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    // =========================================================================
    // ROLE CHECKS (all pivot-based — reads from user_roles table)
    // =========================================================================

    /**
     * Check if the user has a specific role via the pivot table.
     * Uses eager-loaded data when available to avoid N+1 queries.
     */
    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $roleName);
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->whereIn('name', $roleNames)->isNotEmpty();
        }

        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(self::ROLE_EMPLOYEE);
    }

    public function isManager(): bool
    {
        return $this->hasRole(self::ROLE_MANAGER);
    }

    public function isHR(): bool
    {
        return $this->hasRole(self::ROLE_HR);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    // =========================================================================
    // PERMISSION CHECK
    // =========================================================================

    /**
     * Check if the user has a specific permission through any of their roles.
     * Super admin always has all permissions.
     * Leverages eager-loaded relations to avoid N+1 queries.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap(fn($role) => $role->permissions)
            ->contains('name', $permission);
    }
}
