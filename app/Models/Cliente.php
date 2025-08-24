<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'clientes';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'tipo',
        'cpf_cnpj',
        'rg_ie',
        'email',
        'telefone',
        'celular',
        'whatsapp',
        'cep',
        'uf',
        'cidade',
        'bairro',
        'logradouro',
        'numero',
        'complemento',
        'observacoes',
        'status',
        'categoria',
        'origem_captacao',
        // Novos campos PF/PJ
        'data_nascimento',
        'profissao',
        'estado_civil',
        'renda_mensal',
        'razao_social',
        'nome_fantasia',
        'data_fundacao',
        'ramo_atividade',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        // Formatação de datas no JSON
        'data_nascimento' => 'date:Y-m-d',
        'data_fundacao' => 'date:Y-m-d',
        'renda_mensal' => 'decimal:2',
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
        static::creating(function ($cliente) {
            if (empty($cliente->created_by) && Auth::check()) {
                $cliente->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($cliente) {
            if (Auth::check()) {
                $cliente->updated_by = Auth::id();
            }
        });
    }

    /**
     * Imóveis que pertencem a este cliente (como proprietário).
     */
    public function imoveis()
    {
        return $this->hasMany(Imovel::class, 'proprietario_id');
    }

    /**
     * Formata o nome completo do cliente.
     *
     * @return string
     */
    public function getNomeCompletoAttribute()
    {
        return $this->nome;
    }

    /**
     * Formata o endereço completo do cliente.
     *
     * @return string|null
     */
    public function getEnderecoCompletoAttribute()
    {
        $partes = [];
        
        if (!empty($this->logradouro)) {
            $partes[] = $this->logradouro;
            
            if (!empty($this->numero)) {
                $partes[0] .= ", {$this->numero}";
            }
        }
        
        if (!empty($this->bairro)) {
            $partes[] = $this->bairro;
        }
        
        if (!empty($this->cidade)) {
            $cidade = $this->cidade;
            
            if (!empty($this->uf)) {
                $cidade .= "/{$this->uf}";
            }
            
            $partes[] = $cidade;
        }
        
        if (!empty($this->cep)) {
            $partes[] = "CEP: {$this->cep}";
        }
        
        return !empty($partes) ? implode(', ', $partes) : null;
    }

    /**
     * Escopo: Clientes ativos.
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ATIVO');
    }

    /**
     * Escopo: Clientes por tipo.
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
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
