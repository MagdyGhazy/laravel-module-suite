<?php

namespace Ghazym\LaravelModuleSuite\Models;

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
    ];

    protected $appends = ['url'];


    protected $hidden = [
        'mediable_type',
        'mediable_id',
        'file_path',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function getUrlAttribute()
    {
        $env = env('APP_ENV');
        if ($env == 'local') {
            return $this->file_path ? url($this->file_path) : null;
        }else{
            return $this->file_path ? url('storage/' . $this->file_path) : null;
        }
    }

    /**
     * Get the parent mediable model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
} 