<?php

namespace Ghazym\LaravelModuleSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Roleable extends Model
{
    protected $table = 'roleables';

    protected $fillable = ['role_id', 'roleable_id', 'roleable_type'];


    public function relatable(): MorphTo
    {
        return $this->morphTo('roleable');
    }
} 