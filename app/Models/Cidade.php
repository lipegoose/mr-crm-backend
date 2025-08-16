<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Cidade extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'cidades';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'uf',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
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
        static::creating(function ($cidade) {
            if (empty($cidade->created_by) && Auth::check()) {
                $cidade->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($cidade) {
            if (Auth::check()) {
                $cidade->updated_by = Auth::id();
            }
        });
    }

    /**
     * Mutator para o campo UF
     * Garante que o valor seja sempre convertido para maiúsculo
     *
     * @param mixed $value
     * @return void
     */
    public function setUfAttribute($value)
    {
        $this->attributes['uf'] = strtoupper($value);
    }

    /**
     * Mutator para o campo nome
     * Garante que o valor seja sempre tratado como string e formatado corretamente
     *
     * @param mixed $value
     * @return void
     */
    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = ucwords(mb_strtolower($value));
    }

    /**
     * Acessor para adicionar valor e label para uso em selects
     *
     * @return array
     */
    public function getSelectOptionsAttribute()
    {
        return [
            'value' => (string) $this->id,
            'label' => $this->nome,
        ];
    }

    /*
     * Relacionamentos
     */
    
    /**
     * Bairros da cidade.
     */
    public function bairros()
    {
        return $this->hasMany(Bairro::class);
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
     * Escopo: Filtrar por UF.
     */
    public function scopePorUf($query, $uf)
    {
        return $query->where('uf', strtoupper($uf));
    }
    
    /**
     * Escopo: Buscar por nome (busca parcial/LIKE).
     */
    public function scopePorNome($query, $nome)
    {
        return $query->where('nome', 'LIKE', '%' . $nome . '%');
    }
    
    /**
     * Escopo: Ordenar por nome.
     */
    public function scopeOrdenadoPorNome($query)
    {
        return $query->orderBy('nome');
    }
    
    /**
     * Busca uma cidade pelo nome e UF ou cria se não existir
     *
     * @param string $nome
     * @param string $uf
     * @return Cidade
     */
    public static function buscarOuCriar($nome, $uf)
    {
        $nome = ucwords(mb_strtolower($nome));
        $uf = strtoupper($uf);
        
        return static::firstOrCreate(
            ['nome' => $nome, 'uf' => $uf],
            ['created_by' => Auth::id()]
        );
    }
}
