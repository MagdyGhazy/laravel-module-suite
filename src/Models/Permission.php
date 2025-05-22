<?php

namespace Ghazym\LaravelModuleSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ghazym\LaravelModuleSuite\Traits\HasPermissions;

class Permission extends Model
{
    use HasPermissions;

    protected $fillable = ['name', 'description'];
    

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('laravel-module-suite.roles.model'), 'role_permission');
    }
} 