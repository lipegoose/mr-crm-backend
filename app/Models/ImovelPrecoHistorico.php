<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ImovelPrecoHistorico extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis_precos_historico';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'imovel_id',
        'tipo_negocio',
        'valor',
        'data_inicio',
        'data_fim',
        'motivo',
        'observacao',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'valor' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($historico) {
            if (empty($historico->created_by) && Auth::check()) {
                $historico->created_by = Auth::id();
            }
            
            // Se não foi definida uma data de início, usar a data atual
            if (empty($historico->data_inicio)) {
                $historico->data_inicio = Carbon::today();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($historico) {
            if (Auth::check()) {
                $historico->updated_by = Auth::id();
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
     * Escopo: Registros vigentes (sem data_fim ou com data_fim >= hoje).
     */
    public function scopeVigentes($query)
    {
        $hoje = Carbon::today();
        return $query->where(function ($q) use ($hoje) {
            $q->whereNull('data_fim')
              ->orWhere('data_fim', '>=', $hoje);
        });
    }
    
    /**
     * Escopo: Registros expirados (com data_fim < hoje).
     */
    public function scopeExpirados($query)
    {
        $hoje = Carbon::today();
        return $query->whereNotNull('data_fim')
                     ->where('data_fim', '<', $hoje);
    }
    
    /**
     * Escopo: Filtrar por tipo de negócio.
     */
    public function scopePorTipoNegocio($query, $tipo)
    {
        return $query->where('tipo_negocio', $tipo);
    }
    
    /**
     * Verifica se o registro está vigente.
     *
     * @return bool
     */
    public function estaVigente()
    {
        $hoje = Carbon::today();
        return $this->data_fim === null || $this->data_fim->greaterThanOrEqualTo($hoje);
    }
    
    /**
     * Fecha o registro de preço, definindo a data_fim como a data atual.
     *
     * @param string|null $motivo Motivo opcional para o fechamento
     * @return bool
     */
    public function fechar($motivo = null)
    {
        $this->data_fim = Carbon::today();
        
        if ($motivo) {
            $this->motivo = $motivo;
        }
        
        return $this->save();
    }
    
    /**
     * Formata o valor para exibição.
     *
     * @return string
     */
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
    
    /**
     * Retorna o período de vigência formatado.
     *
     * @return string
     */
    public function getPeriodoFormatadoAttribute()
    {
        $inicio = $this->data_inicio->format('d/m/Y');
        
        if ($this->data_fim) {
            $fim = $this->data_fim->format('d/m/Y');
            return "{$inicio} até {$fim}";
        }
        
        return "A partir de {$inicio}";
    }
}
