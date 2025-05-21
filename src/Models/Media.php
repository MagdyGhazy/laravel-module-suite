<?php

namespace Ghazym\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'mediable_type',
        'mediable_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the parent mediable model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
} 