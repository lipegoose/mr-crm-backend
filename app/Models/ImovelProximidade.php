<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Auth;

class ImovelProximidade extends Pivot
{
    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis_proximidades';

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
        'imovel_id',
        'proximidade_id',
        'distancia_texto',
        'distancia_metros',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'distancia_metros' => 'integer',
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
            
            // Converter distância de texto para metros se não estiver definido
            if (empty($pivot->distancia_metros) && !empty($pivot->distancia_texto)) {
                $pivot->distancia_metros = self::converterDistanciaParaMetros($pivot->distancia_texto);
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($pivot) {
            if (Auth::check()) {
                $pivot->updated_by = Auth::id();
            }
            
            // Converter distância de texto para metros se foi atualizado
            if ($pivot->isDirty('distancia_texto')) {
                $pivot->distancia_metros = self::converterDistanciaParaMetros($pivot->distancia_texto);
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
     * Proximidade relacionada.
     */
    public function proximidade()
    {
        return $this->belongsTo(Proximidade::class);
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
     * Formata a distância para exibição.
     *
     * @return string
     */
    public function formatarDistancia()
    {
        if (!empty($this->distancia_texto)) {
            return $this->distancia_texto;
        }
        
        if (!empty($this->distancia_metros)) {
            if ($this->distancia_metros < 1000) {
                return $this->distancia_metros . 'm';
            } else {
                $km = $this->distancia_metros / 1000;
                return number_format($km, 1, ',', '.') . 'km';
            }
        }
        
        return '';
    }
    
    /**
     * Converte uma string de distância para metros.
     *
     * @param string $texto
     * @return int|null
     */
    public static function converterDistanciaParaMetros($texto)
    {
        if (empty($texto)) {
            return null;
        }
        
        // Remover espaços e converter para minúsculas
        $texto = strtolower(trim($texto));
        
        // Extrair o número da string
        preg_match('/([0-9,.]+)/', $texto, $matches);
        if (empty($matches[1])) {
            return null;
        }
        
        // Converter vírgula para ponto para cálculos
        $numero = str_replace(',', '.', $matches[1]);
        
        // Verificar se é em km ou m
        if (strpos($texto, 'km') !== false) {
            return (int) ($numero * 1000);
        } else {
            return (int) $numero;
        }
    }
}
