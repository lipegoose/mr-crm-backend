<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class ImovelDetalhe extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis_detalhes';

    /**
     * Indica se a chave primária é auto-incremento.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'titulo_anuncio',
        'mostrar_titulo',
        'descricao',
        'mostrar_descricao',
        'palavras_chave',
        'observacoes_internas',
        'tour_virtual_url',
        'matricula',
        'inscricao_municipal',
        'inscricao_estadual',
        'valor_comissao',
        'tipo_comissao',
        'exclusividade',
        'data_inicio_exclusividade',
        'data_fim_exclusividade',
        'observacoes_privadas',
        'config_exibicao',
        'dados_permuta',
        'seo',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        // Booleanos
        'mostrar_titulo' => 'boolean',
        'mostrar_descricao' => 'boolean',
        'exclusividade' => 'boolean',
        
        // JSON
        'config_exibicao' => 'json',
        'dados_permuta' => 'json',
        'seo' => 'json',
        
        // Enum
        'tipo_comissao' => 'string',
        
        // Datas
        'data_inicio_exclusividade' => 'date',
        'data_fim_exclusividade' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        
        // Decimal
        'valor_comissao' => 'decimal:2',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($detalhe) {
            if (empty($detalhe->created_by) && Auth::check()) {
                $detalhe->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($detalhe) {
            if (Auth::check()) {
                $detalhe->updated_by = Auth::id();
            }
        });
    }

    /**
     * Verifica se a exclusividade está vigente.
     *
     * @return bool
     */
    public function isExclusividadeVigente()
    {
        if (!$this->exclusividade) {
            return false;
        }
        
        $hoje = Date::now();
        
        // Se tem data de início, verificar se já começou
        if ($this->data_inicio_exclusividade && $hoje->lt($this->data_inicio_exclusividade)) {
            return false;
        }
        
        // Se tem data de fim, verificar se já terminou
        if ($this->data_fim_exclusividade && $hoje->gt($this->data_fim_exclusividade)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtém o valor da comissão formatado.
     *
     * @return string
     */
    public function getComissaoFormatada()
    {
        if (!$this->valor_comissao) {
            return 'Não definida';
        }
        
        if ($this->tipo_comissao === 'PORCENTAGEM') {
            return number_format($this->valor_comissao, 2, ',', '.') . '%';
        }
        
        return 'R$ ' . number_format($this->valor_comissao, 2, ',', '.');
    }
    
    /**
     * Obtém configuração específica de exibição.
     *
     * @param string $chave
     * @param mixed $valorPadrao
     * @return mixed
     */
    public function getConfigExibicao($chave, $valorPadrao = null)
    {
        $config = $this->config_exibicao ?? [];
        return $config[$chave] ?? $valorPadrao;
    }
    
    /**
     * Define configuração específica de exibição.
     *
     * @param string $chave
     * @param mixed $valor
     * @return $this
     */
    public function setConfigExibicao($chave, $valor)
    {
        $config = $this->config_exibicao ?? [];
        $config[$chave] = $valor;
        $this->config_exibicao = $config;
        
        return $this;
    }
    
    /**
     * Obtém dados de permuta.
     *
     * @param string $chave
     * @param mixed $valorPadrao
     * @return mixed
     */
    public function getDadosPermuta($chave, $valorPadrao = null)
    {
        $dados = $this->dados_permuta ?? [];
        return $dados[$chave] ?? $valorPadrao;
    }
    
    /**
     * Define dados de permuta.
     *
     * @param string $chave
     * @param mixed $valor
     * @return $this
     */
    public function setDadosPermuta($chave, $valor)
    {
        $dados = $this->dados_permuta ?? [];
        $dados[$chave] = $valor;
        $this->dados_permuta = $dados;
        
        return $this;
    }
    
    /**
     * Obtém metadados SEO.
     *
     * @param string $chave
     * @param mixed $valorPadrao
     * @return mixed
     */
    public function getSeo($chave, $valorPadrao = null)
    {
        $seo = $this->seo ?? [];
        return $seo[$chave] ?? $valorPadrao;
    }
    
    /**
     * Define metadados SEO.
     *
     * @param string $chave
     * @param mixed $valor
     * @return $this
     */
    public function setSeo($chave, $valor)
    {
        $seo = $this->seo ?? [];
        $seo[$chave] = $valor;
        $this->seo = $seo;
        
        return $this;
    }

    /*
     * Relacionamentos
     */
    
    /**
     * Imóvel ao qual estes detalhes pertencem.
     */
    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'id');
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
