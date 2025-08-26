<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionPhoto extends Model
{
    protected $fillable = [
        'section_id', 'path', 'principal', 'ordem', 'alt_text'
    ];

    protected $casts = [
        'principal' => 'boolean',
        'ordem' => 'integer',
    ];

    protected $appends = ['url'];

    protected static function booted()
    {
        static::saved(function ($photo) {
            if ($photo->principal) {
                self::where('section_id', $photo->section_id)
                    ->where('id', '!=', $photo->id)
                    ->update(['principal' => false]);
            }
        });

        static::deleting(function ($photo) {
            if ($photo->path) {
                $absolute = app()->basePath('public/' . ltrim($photo->path, '/'));
                if (file_exists($absolute)) {
                    @unlink($absolute);
                }
            }
        });
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function getUrlAttribute()
    {
        return $this->path ? url('/' . ltrim($this->path, '/')) : null;
    }
}
