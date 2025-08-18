<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Proximidade extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'proximidades';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
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
        static::creating(function ($proximidade) {
            if (empty($proximidade->created_by) && Auth::check()) {
                $proximidade->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($proximidade) {
            if (Auth::check()) {
                $proximidade->updated_by = Auth::id();
            }
        });
    }

    /**
     * Imóveis que possuem esta proximidade.
     */
    public function imoveis()
    {
        return $this->belongsToMany(Imovel::class, 'imoveis_proximidades')
            ->withTimestamps()
            ->withPivot(['distancia_texto', 'distancia_metros', 'created_by', 'updated_by']);
    }

    /**
     * Escopo: Proximidades por categoria.
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
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
     * Verifica se a proximidade está em uso por algum imóvel.
     *
     * @return bool
     */
    public function estaEmUso()
    {
        return $this->imoveis()->count() > 0;
    }
    
    /**
     * Retorna as proximidades ordenadas por popularidade (mais usadas).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit Limite de resultados
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPopularidade($query, $limit = 10)
    {
        return $query->select('proximidades.*', DB::raw('COUNT(imoveis_proximidades.id) as total_usos'))
            ->leftJoin('imoveis_proximidades', 'proximidades.id', '=', 'imoveis_proximidades.proximidade_id')
            ->groupBy('proximidades.id')
            ->orderBy('total_usos', 'desc')
            ->limit($limit);
    }
}
