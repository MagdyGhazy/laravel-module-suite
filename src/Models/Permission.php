<?php

namespace Ghazym\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'description'];
    

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('module-builder.roles.model'), 'role_permission');
    }
} 