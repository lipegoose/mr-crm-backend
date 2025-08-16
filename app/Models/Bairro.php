<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Bairro extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'bairros';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'cidade_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'cidade_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Atributos que devem ser incluídos nas arrays.
     *
     * @var array
     */
    protected $appends = [
        'cidade_nome',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($bairro) {
            if (empty($bairro->created_by) && Auth::check()) {
                $bairro->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($bairro) {
            if (Auth::check()) {
                $bairro->updated_by = Auth::id();
            }
        });
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
     * Acessor para obter o nome da cidade
     *
     * @return string|null
     */
    public function getCidadeNomeAttribute()
    {
        return $this->cidade ? $this->cidade->nome : null;
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
     * Cidade a qual o bairro pertence.
     */
    public function cidade()
    {
        return $this->belongsTo(Cidade::class);
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
     * Escopo: Filtrar por cidade.
     */
    public function scopePorCidade($query, $cidadeId)
    {
        return $query->where('cidade_id', $cidadeId);
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
     * Busca um bairro pelo nome e cidade ou cria se não existir
     *
     * @param string $nome
     * @param int $cidadeId
     * @return Bairro
     */
    public static function buscarOuCriar($nome, $cidadeId)
    {
        $nome = ucwords(mb_strtolower($nome));
        
        return static::firstOrCreate(
            ['nome' => $nome, 'cidade_id' => $cidadeId],
            ['created_by' => Auth::id()]
        );
    }
    
    /**
     * Busca ou cria um bairro pelo nome, nome da cidade e UF
     *
     * @param string $nome
     * @param string $cidadeNome
     * @param string $uf
     * @return array Contém o bairro e um flag indicando se foi criado
     */
    public static function buscarOuCriarPorCidadeUf($nome, $cidadeNome, $uf)
    {
        // Buscar ou criar a cidade primeiro
        $cidade = Cidade::buscarOuCriar($cidadeNome, $uf);
        
        // Verificar se o bairro já existe
        $bairro = static::where('nome', ucwords(mb_strtolower($nome)))
            ->where('cidade_id', $cidade->id)
            ->first();
            
        $isNew = false;
        
        // Se não existir, criar
        if (!$bairro) {
            $bairro = static::create([
                'nome' => $nome,
                'cidade_id' => $cidade->id,
                'created_by' => Auth::id()
            ]);
            $isNew = true;
        }
        
        return ['bairro' => $bairro, 'is_new' => $isNew];
    }
}
