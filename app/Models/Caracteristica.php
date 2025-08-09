<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Caracteristica extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'caracteristicas';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'descricao',
        'escopo',
        'icone',
        'ordem',
        'sistema',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'sistema' => 'boolean',
        'ordem' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($caracteristica) {
            if (empty($caracteristica->created_by) && Auth::check()) {
                $caracteristica->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($caracteristica) {
            if (Auth::check()) {
                $caracteristica->updated_by = Auth::id();
            }
        });
    }

    /**
     * Imóveis que possuem esta característica.
     */
    public function imoveis()
    {
        return $this->belongsToMany(Imovel::class, 'imoveis_caracteristicas')
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    /**
     * Condomínios que possuem esta característica.
     */
    public function condominios()
    {
        return $this->belongsToMany(Condominio::class, 'condominios_caracteristicas')
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    /**
     * Escopo: Características para imóveis.
     */
    public function scopeParaImoveis($query)
    {
        return $query->where('escopo', 'IMOVEL');
    }

    /**
     * Escopo: Características para condomínios.
     */
    public function scopeParaCondominios($query)
    {
        return $query->where('escopo', 'CONDOMINIO');
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
}
