<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionItemPhoto extends Model
{
    protected $fillable = [
        'section_item_id', 'path', 'principal', 'ordem', 'alt_text'
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
                self::where('section_item_id', $photo->section_item_id)
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

    public function item()
    {
        return $this->belongsTo(SectionItem::class, 'section_item_id');
    }

    public function getUrlAttribute()
    {
        return $this->path ? url('/' . ltrim($this->path, '/')) : null;
    }
}
