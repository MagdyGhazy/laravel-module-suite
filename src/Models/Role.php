<?php

namespace Ghazym\LaravelModuleSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Collection;
use Ghazym\LaravelModuleSuite\Traits\HasPermissions;

class Role extends Model
{
    use HasPermissions;

    protected $fillable = ['name', 'description'];

    /**
     * Get all models that have this role.
     */
    public function roleables(): MorphMany
    {
        return $this->morphMany(config('laravel-module-suite.roles.model'), 'roleable');
    }

    /**
     * Get all permissions associated with this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(config('laravel-module-suite.permissions.model'), 'role_permission');
    }

    /**
     * Sync permissions for this role.
     *
     * @param array|Collection $permissionIds
     * @return void
     */
    public function syncPermissions($permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get all permission names for this role.
     *
     * @return Collection
     */
    public function getPermissionNames(): Collection
    {
        return $this->permissions()->pluck('name');
    }

    /**
     * Check if role has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
} 