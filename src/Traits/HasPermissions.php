<?php

namespace Ghazym\LaravelModuleSuite\Traits;

use Ghazym\LaravelModuleSuite\Models\Permission;
use Ghazym\LaravelModuleSuite\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Get all roles for the model.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(config('laravel-module-suite.roles.model'), 'roleable');
    }

    /**
     * Assign a role to the model.
     *
     * @param int|string|Role $role Role ID, name, or Role model instance
     * @return bool|array
     */
    public function assignRole($role): bool|array
    {
        try {
            if (is_string($role)) {
                $role = Role::where('name', $role)->firstOrFail();
            } elseif (is_int($role)) {
                $role = Role::findOrFail($role);
            } elseif (!$role instanceof Role) {
                return ['error' => 'Role must be an ID, name, or Role model instance'];
            }

            $this->roles()->attach($role->id);
            return true;
        } catch (\Exception $e) {
            report($e);
            return ['error' => 'Failed to assign role'];
        }
    }

    /**
     * Remove a role from the model.
     *
     * @param int|string|Role $role Role ID, name, or Role model instance
     * @return bool|array
     */
    public function removeRole($role): bool|array
    {
        try {
            if (is_string($role)) {
                $role = Role::where('name', $role)->firstOrFail();
            } elseif (is_int($role)) {
                $role = Role::findOrFail($role);
            } elseif (!$role instanceof Role) {
                return ['error' => 'Role must be an ID, name, or Role model instance'];
            }

            $this->roles()->detach($role->id);
            return true;
        } catch (\Exception $e) {
            report($e);
            return ['error' => 'Failed to remove role'];
        }
    }

    /**
     * Sync roles for the model.
     *
     * @param array $roleIds Array of role IDs
     * @return bool|array
     */
    public function syncRoles(array $roleIds): bool|array
    {
        try {
            $this->roles()->sync($roleIds);
            return true;
        } catch (\Exception $e) {
            report($e);
            return ['error' => 'Failed to sync roles'];
        }
    }

    /**
     * Get all permissions for the model.
     *
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Check if the model has a specific permission.
     *
     * @param string $permission Permission name
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->getPermissions()->contains('name', $permission);
    }

    /**
     * Check if the model has any of the given permissions.
     *
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->getPermissions()->whereIn('name', $permissions)->isNotEmpty();
    }

    /**
     * Check if the model has all of the given permissions.
     *
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return $this->getPermissions()->whereIn('name', $permissions)->count() === count($permissions);
    }
} 