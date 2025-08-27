<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'slug', 'titulo', 'subtitulo', 'descricao', 'url_link', 'texto_url', 'botao',
        'template', 'show_on_home', 'ordem', 'ativo', 'published_at'
    ];

    protected $casts = [
        'botao' => 'boolean',
        'show_on_home' => 'boolean',
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'published_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SectionItem::class)->orderBy('ordem', 'asc');
    }

    public function photos()
    {
        return $this->hasMany(SectionPhoto::class)->orderBy('ordem', 'asc');
    }

    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'keyword_section')->withTimestamps();
    }
}
