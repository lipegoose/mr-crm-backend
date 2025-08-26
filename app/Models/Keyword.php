<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'nome', 'slug', 'descricao'
    ];

    public function items()
    {
        return $this->belongsToMany(SectionItem::class, 'keyword_section_item')->withTimestamps();
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'keyword_section')->withTimestamps();
    }
}
