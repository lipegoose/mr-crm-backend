<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Condominio extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'condominios';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'descricao',
        'cep',
        'uf',
        'cidade',
        'bairro',
        'logradouro',
        'numero',
        'complemento',
        'latitude',
        'longitude',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
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
        static::creating(function ($condominio) {
            if (empty($condominio->created_by) && Auth::check()) {
                $condominio->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($condominio) {
            if (Auth::check()) {
                $condominio->updated_by = Auth::id();
            }
        });
    }

    /**
     * Formata o endereço completo do condomínio.
     *
     * @param bool $completo Incluir cidade, UF e CEP
     * @return string
     */
    public function formatarEndereco($completo = true)
    {
        $endereco = [];
        
        // Logradouro
        if ($this->logradouro) {
            $endereco[] = $this->logradouro;
        }
        
        // Número
        if ($this->numero) {
            $endereco[] = $this->numero;
        }
        
        // Complemento
        if ($this->complemento) {
            $endereco[] = $this->complemento;
        }
        
        // Bairro
        if ($this->bairro) {
            $endereco[] = $this->bairro;
        }
        
        // Cidade/UF e CEP (se completo)
        if ($completo) {
            if ($this->cidade && $this->uf) {
                $endereco[] = "{$this->cidade}/{$this->uf}";
            }
            
            if ($this->cep) {
                $endereco[] = "CEP: " . substr_replace($this->cep, '-', 5, 0);
            }
        }
        
        return implode(', ', array_filter($endereco));
    }
    
    /**
     * Valida se uma característica é específica para condomínios.
     *
     * @param int|Caracteristica $caracteristica
     * @return bool
     */
    public function validarCaracteristica($caracteristica)
    {
        // Se for um ID, buscar a característica
        if (is_numeric($caracteristica)) {
            $caracteristica = Caracteristica::find($caracteristica);
        }
        
        // Verificar se a característica existe e tem escopo CONDOMINIO
        return $caracteristica && $caracteristica->escopo === 'CONDOMINIO';
    }
    
    /**
     * Verifica se o condomínio possui uma determinada característica.
     *
     * @param int|Caracteristica $caracteristica
     * @return bool
     */
    public function possuiCaracteristica($caracteristica)
    {
        if (is_numeric($caracteristica)) {
            return $this->caracteristicas()->where('caracteristicas.id', $caracteristica)->exists();
        }
        
        if ($caracteristica instanceof Caracteristica) {
            return $this->caracteristicas()->where('caracteristicas.id', $caracteristica->id)->exists();
        }
        
        return false;
    }
    
    /**
     * Adiciona uma característica ao condomínio.
     *
     * @param int|Caracteristica $caracteristica
     * @return bool
     */
    public function adicionarCaracteristica($caracteristica)
    {
        // Se for um ID, buscar a característica
        if (is_numeric($caracteristica)) {
            $caracteristica = Caracteristica::find($caracteristica);
        }
        
        // Verificar se é uma característica válida para condomínio
        if (!$this->validarCaracteristica($caracteristica)) {
            return false;
        }
        
        // Verificar se já possui a característica
        if ($this->possuiCaracteristica($caracteristica)) {
            return true;
        }
        
        // Adicionar a característica com auditoria
        $this->caracteristicas()->attach($caracteristica->id, [
            'created_by' => Auth::id() ?? $this->created_by,
            'created_at' => Carbon::now(),
        ]);
        
        return true;
    }

    /*
     * Relacionamentos
     */
    
    /**
     * Imóveis que pertencem a este condomínio.
     */
    public function imoveis()
    {
        return $this->hasMany(Imovel::class);
    }
    
    /**
     * Características do condomínio (N:N).
     */
    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'condominios_caracteristicas')
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
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
    
    /*
     * Escopos de consulta
     */
    
    /**
     * Escopo: Condomínios com imóveis ativos.
     */
    public function scopeComImoveisAtivos($query)
    {
        return $query->whereHas('imoveis', function ($q) {
            $q->where('status', 'ATIVO');
        });
    }
    
    /**
     * Escopo: Condomínios por cidade.
     */
    public function scopePorCidade($query, $cidade)
    {
        return $query->where('cidade', $cidade);
    }
    
    /**
     * Escopo: Condomínios por bairro.
     */
    public function scopePorBairro($query, $bairro)
    {
        return $query->where('bairro', $bairro);
    }
}
