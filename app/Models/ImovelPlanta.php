<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImovelPlanta extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis_plantas';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'imovel_id',
        'titulo',
        'url',
        'ordem',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'ordem' => 'integer',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($planta) {
            if (empty($planta->created_by) && Auth::check()) {
                $planta->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($planta) {
            if (Auth::check()) {
                $planta->updated_by = Auth::id();
            }
        });
    }

    /**
     * Imóvel relacionado.
     */
    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }

    /**
     * Usuário que criou o registro.
     */
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que atualizou o registro.
     */
    public function atualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Gera a URL completa da planta.
     *
     * @return string|null
     */
    public function getUrlCompletaAttribute()
    {
        if (empty($this->url)) {
            return null;
        }
        
        // Se a URL já for completa (começar com http ou https), retorná-la como está
        if (strpos($this->url, 'http://') === 0 || strpos($this->url, 'https://') === 0) {
            return $this->url;
        }
        
        // Caso contrário, assumir que é um caminho de arquivo no storage
        return url(Storage::url($this->url));
    }
    
    /**
     * Atualiza a ordem desta planta.
     *
     * @param int $ordem
     * @return bool
     */
    public function atualizarOrdem($ordem)
    {
        $this->ordem = $ordem;
        return $this->save();
    }
    
    /**
     * Escopo: Ordenar por ordem.
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem', 'asc');
    }
}
