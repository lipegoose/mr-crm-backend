<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionItem extends Model
{
    protected $fillable = [
        'section_id', 'titulo', 'subtitulo', 'descricao', 'url_link', 'texto_url', 'botao',
        'url_amigavel', 'ordem', 'ativo'
    ];

    protected $casts = [
        'botao' => 'boolean',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function photos()
    {
        return $this->hasMany(SectionItemPhoto::class)->orderBy('ordem', 'asc');
    }

    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'keyword_section_item')->withTimestamps();
    }
}
