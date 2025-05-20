<?php

namespace Ghazym\ModuleBuilder\Traits;

use Ghazym\ModuleBuilder\Models\Permission;
use Ghazym\ModuleBuilder\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasPermissions
{
    /**
     * Get all roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Assign a role to the user.
     *
     * @param int|string|Role $role Role ID, name, or Role model instance
     * @return bool
     */
    public function assignRole($role): bool
    {
        try {
            if (is_string($role)) {
                $role = Role::where('name', $role)->firstOrFail();
            } elseif (is_int($role)) {
                $role = Role::findOrFail($role);
            } elseif (!$role instanceof Role) {
                throw new \InvalidArgumentException('Role must be an ID, name, or Role model instance');
            }

            $this->roles()->sync([$role->id]);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get all permissions for the user through their roles.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions.*.name')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    /**
     * Check if user has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->count() === count($permissions);
    }
} 