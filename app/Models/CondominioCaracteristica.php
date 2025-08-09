<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Auth;

class CondominioCaracteristica extends Pivot
{
    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'condominios_caracteristicas';

    /**
     * Indica se o modelo deve ser timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'condominio_id',
        'caracteristica_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($pivot) {
            if (empty($pivot->created_by) && Auth::check()) {
                $pivot->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($pivot) {
            if (Auth::check()) {
                $pivot->updated_by = Auth::id();
            }
        });
    }

    /**
     * Condomínio relacionado.
     */
    public function condominio()
    {
        return $this->belongsTo(Condominio::class);
    }

    /**
     * Característica relacionada.
     */
    public function caracteristica()
    {
        return $this->belongsTo(Caracteristica::class);
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
