<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Imovel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo_referencia',
        'proprietario_id',
        'corretor_id',
        'condominio_id',
        'tipo',
        'subtipo',
        'perfil',
        'situacao',
        'ano_construcao',
        'incorporacao',
        'posicao_solar',
        'terreno',
        'escriturado',
        'esquina',
        'mobiliado',
        'averbado',
        'dormitorios',
        'suites',
        'banheiros',
        'garagens',
        'garagem_coberta',
        'box_garagem',
        'sala_tv',
        'sala_jantar',
        'sala_estar',
        'lavabo',
        'area_servico',
        'cozinha',
        'closet',
        'escritorio',
        'dependencia_servico',
        'copa',
        'area_construida',
        'area_privativa',
        'area_total',
        'unidade_medida',
        'tipo_negocio',
        'preco_venda',
        'preco_aluguel',
        'preco_temporada',
        'mostrar_preco',
        'preco_alternativo',
        'preco_anterior',
        'mostrar_preco_anterior',
        'preco_iptu',
        'periodo_iptu',
        'preco_condominio',
        'financiado',
        'aceita_financiamento',
        'minha_casa_minha_vida',
        'total_taxas',
        'descricao_taxas',
        'aceita_permuta',
        'cep',
        'uf',
        'cidade',
        'bairro',
        'logradouro',
        'numero',
        'complemento',
        'mostrar_endereco',
        'mostrar_numero',
        'mostrar_proximidades',
        'latitude',
        'longitude',
        'status',
        'publicar_site',
        'destaque_site',
        'data_publicacao',
        'data_expiracao',
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
        'escriturado' => 'boolean',
        'esquina' => 'boolean',
        'mobiliado' => 'boolean',
        'averbado' => 'boolean',
        'garagem_coberta' => 'boolean',
        'box_garagem' => 'boolean',
        'mostrar_preco' => 'boolean',
        'mostrar_preco_anterior' => 'boolean',
        'financiado' => 'boolean',
        'aceita_financiamento' => 'boolean',
        'minha_casa_minha_vida' => 'boolean',
        'aceita_permuta' => 'boolean',
        'mostrar_endereco' => 'boolean',
        'mostrar_numero' => 'boolean',
        'mostrar_proximidades' => 'boolean',
        'publicar_site' => 'boolean',
        'destaque_site' => 'boolean',
        
        // Decimais
        'area_construida' => 'decimal:2',
        'area_privativa' => 'decimal:2',
        'area_total' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'preco_aluguel' => 'decimal:2',
        'preco_temporada' => 'decimal:2',
        'preco_anterior' => 'decimal:2',
        'preco_iptu' => 'decimal:2',
        'preco_condominio' => 'decimal:2',
        'total_taxas' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        
        // Enums
        'terreno' => 'string',
        'unidade_medida' => 'string',
        'tipo_negocio' => 'string',
        'periodo_iptu' => 'string',
        'status' => 'string',
        
        // Datas
        'data_publicacao' => 'date',
        'data_expiracao' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar um imóvel, gerar código de referência se não fornecido
        static::creating(function ($imovel) {
            if (empty($imovel->codigo_referencia)) {
                $imovel->codigo_referencia = $imovel->gerarCodigoReferencia();
            }
            
            // Definir status padrão como RASCUNHO se não for especificado
            // Isso permite o cadastro em etapas via wizard
            if (empty($imovel->status)) {
                $imovel->status = 'RASCUNHO';
            }
            
            // Definir usuário que está criando
            if (empty($imovel->created_by) && Auth::check()) {
                $imovel->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, verificar alterações de preço
        static::updating(function ($imovel) {
            // Registrar histórico de preços se houver alteração
            $dirty = $imovel->getDirty();
            $precosCampos = ['preco_venda', 'preco_aluguel', 'preco_temporada'];
            
            foreach ($precosCampos as $campo) {
                if (array_key_exists($campo, $dirty) && $dirty[$campo] != $imovel->getOriginal($campo)) {
                    $tipoNegocio = match($campo) {
                        'preco_venda' => 'VENDA',
                        'preco_aluguel' => 'ALUGUEL',
                        'preco_temporada' => 'TEMPORADA',
                        default => null
                    };
                    
                    if ($tipoNegocio) {
                        $imovel->registrarHistoricoPreco(
                            $tipoNegocio, 
                            $dirty[$campo], 
                            'Alteração via sistema',
                            $imovel->getOriginal($campo)
                        );
                    }
                }
            }
            
            // Definir usuário que está atualizando
            if (Auth::check()) {
                $imovel->updated_by = Auth::id();
            }
        });
    }

    /**
     * Gera um código de referência único para o imóvel.
     *
     * @return string
     */
    public function gerarCodigoReferencia()
    {
        // Obter o ID do imóvel ou próximo ID se for novo
        $id = $this->id ?? DB::table('imoveis')->max('id') + 1;
        
        // Obter sigla do tipo de imóvel
        $sigla = match(strtolower(Str::slug($this->tipo))) {
            'apartamento' => 'AP',
            'casa' => 'CA',
            'terreno' => 'TE',
            'comercial-loja' => 'LJ',
            'comercial-sala' => 'SL',
            'comercial-galpao' => 'GL',
            'chacara' => 'CH',
            'fazenda' => 'FA',
            'sitio' => 'SI',
            default => 'IM'
        };
        
        // Formato: ID-SIGLA
        $codigo = "{$id}-{$sigla}";
        
        // Verificar unicidade
        if ($this->validarUnicidadeCodigoReferencia($codigo)) {
            return $codigo;
        }
        
        // Se não for único, adicionar sufixo
        $i = 1;
        do {
            $codigoAlternativo = "{$codigo}-{$i}";
            $i++;
        } while (!$this->validarUnicidadeCodigoReferencia($codigoAlternativo));
        
        return $codigoAlternativo;
    }
    
    /**
     * Valida se um código de referência é único.
     *
     * @param string $codigo
     * @return bool
     */
    public function validarUnicidadeCodigoReferencia($codigo)
    {
        $query = static::where('codigo_referencia', $codigo);
        
        // Se o imóvel já existe, excluir ele mesmo da verificação
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }
        
        return $query->count() === 0;
    }
    
    /**
     * Verifica se o imóvel está disponível para um tipo específico de negócio.
     *
     * @param string $tipoNegocio
     * @return bool
     */
    public function isDisponivelPara($tipoNegocio)
    {
        if ($this->status !== 'ATIVO') {
            return false;
        }
        
        switch (strtoupper($tipoNegocio)) {
            case 'VENDA':
                return in_array($this->tipo_negocio, ['VENDA', 'VENDA_ALUGUEL']) && $this->preco_venda > 0;
            case 'ALUGUEL':
                return in_array($this->tipo_negocio, ['ALUGUEL', 'VENDA_ALUGUEL']) && $this->preco_aluguel > 0;
            case 'TEMPORADA':
                return $this->tipo_negocio === 'TEMPORADA' && $this->preco_temporada > 0;
            default:
                return false;
        }
    }
    
    /**
     * Obtém o preço do imóvel de acordo com o tipo de negócio.
     *
     * @param string $tipoNegocio
     * @return float|null
     */
    public function getPreco($tipoNegocio = null)
    {
        // Se não especificado, usar o tipo de negócio principal do imóvel
        if (!$tipoNegocio) {
            $tipoNegocio = $this->tipo_negocio;
            
            // Se for VENDA_ALUGUEL, priorizar VENDA
            if ($tipoNegocio === 'VENDA_ALUGUEL') {
                $tipoNegocio = 'VENDA';
            }
        }
        
        return match(strtoupper($tipoNegocio)) {
            'VENDA' => $this->preco_venda,
            'ALUGUEL' => $this->preco_aluguel,
            'TEMPORADA' => $this->preco_temporada,
            default => null
        };
    }
    
    /**
     * Formata o endereço completo do imóvel.
     *
     * @param bool $completo Incluir cidade, UF e CEP
     * @param bool $respeitarVisibilidade Respeitar configurações de visibilidade
     * @return string
     */
    public function formatarEndereco($completo = true, $respeitarVisibilidade = true)
    {
        if ($respeitarVisibilidade && !$this->mostrar_endereco) {
            return $this->bairro . ($completo ? ", {$this->cidade}/{$this->uf}" : '');
        }
        
        $endereco = [];
        
        // Logradouro
        if ($this->logradouro) {
            $endereco[] = $this->logradouro;
        }
        
        // Número (se configurado para mostrar)
        if ($this->numero && (!$respeitarVisibilidade || $this->mostrar_numero)) {
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
     * Registra um novo preço no histórico e fecha o registro anterior.
     *
     * @param string $tipoNegocio
     * @param float $valor
     * @param string|null $motivo
     * @param float|null $valorAnterior
     * @return \App\Models\ImovelPrecoHistorico
     */
    public function registrarHistoricoPreco($tipoNegocio, $valor, $motivo = null, $valorAnterior = null)
    {
        // Fechar registro anterior
        $this->fecharHistoricoPrecoAnterior($tipoNegocio);
        
        // Criar novo registro
        return $this->precosHistorico()->create([
            'tipo_negocio' => $tipoNegocio,
            'valor' => $valor,
            'data_inicio' => now()->format('Y-m-d'),
            'motivo' => $motivo ?: ($valorAnterior ? 
                "Alteração de preço de " . number_format($valorAnterior, 2, ',', '.') . 
                " para " . number_format($valor, 2, ',', '.') : 
                "Cadastro inicial"),
            'created_by' => Auth::id() ?? $this->created_by,
        ]);
    }
    
    /**
     * Fecha o registro de preço anterior para um tipo de negócio.
     *
     * @param string $tipoNegocio
     * @return bool
     */
    protected function fecharHistoricoPrecoAnterior($tipoNegocio)
    {
        return $this->precosHistorico()
            ->where('tipo_negocio', $tipoNegocio)
            ->whereNull('data_fim')
            ->update([
                'data_fim' => now()->format('Y-m-d'),
                'updated_by' => Auth::id() ?? $this->updated_by,
            ]);
    }

    /*
     * Relacionamentos
     */
    
    /**
     * Proprietário do imóvel.
     */
    public function proprietario()
    {
        return $this->belongsTo(Cliente::class, 'proprietario_id');
    }
    
    /**
     * Corretor responsável pelo imóvel.
     */
    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }
    
    /**
     * Condomínio ao qual o imóvel pertence.
     */
    public function condominio()
    {
        return $this->belongsTo(Condominio::class);
    }
    
    /**
     * Detalhes adicionais do imóvel (1:1).
     */
    public function detalhes()
    {
        return $this->hasOne(ImovelDetalhe::class, 'id');
    }
    
    /**
     * Características do imóvel (N:N).
     */
    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'imoveis_caracteristicas')
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }
    
    /**
     * Proximidades do imóvel (N:N).
     */
    public function proximidades()
    {
        return $this->belongsToMany(Proximidade::class, 'imoveis_proximidades')
            ->withTimestamps()
            ->withPivot(['distancia_texto', 'distancia_metros', 'created_by', 'updated_by']);
    }
    
    /**
     * Imagens do imóvel.
     */
    public function imagens()
    {
        return $this->hasMany(ImovelImagem::class);
    }
    
    /**
     * Vídeos do imóvel.
     */
    public function videos()
    {
        return $this->hasMany(ImovelVideo::class);
    }
    
    /**
     * Plantas do imóvel.
     */
    public function plantas()
    {
        return $this->hasMany(ImovelPlanta::class);
    }
    
    /**
     * Histórico de preços do imóvel.
     */
    public function precosHistorico()
    {
        return $this->hasMany(ImovelPrecoHistorico::class);
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
     * Escopo: Imóveis ativos.
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ATIVO');
    }
    
    /**
     * Escopo: Imóveis em rascunho (cadastros incompletos).
     */
    public function scopeRascunhos($query)
    {
        return $query->where('status', 'RASCUNHO');
    }
    
    /**
     * Escopo: Imóveis por status.
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Escopo: Imóveis publicados no site.
     */
    public function scopePublicados($query)
    {
        return $query->where('publicar_site', true)
            ->where('status', 'ATIVO')
            ->where(function ($q) {
                $hoje = now()->format('Y-m-d');
                $q->whereNull('data_publicacao')
                  ->orWhere('data_publicacao', '<=', $hoje);
            })
            ->where(function ($q) {
                $hoje = now()->format('Y-m-d');
                $q->whereNull('data_expiracao')
                  ->orWhere('data_expiracao', '>=', $hoje);
            });
    }
    
    /**
     * Escopo: Imóveis em destaque no site.
     */
    public function scopeDestaque($query)
    {
        return $query->where('destaque_site', true);
    }
    
    /**
     * Escopo: Filtrar por tipo de negócio.
     */
    public function scopePorTipoNegocio($query, $tipo)
    {
        switch (strtoupper($tipo)) {
            case 'VENDA':
                return $query->whereIn('tipo_negocio', ['VENDA', 'VENDA_ALUGUEL'])
                    ->where('preco_venda', '>', 0);
            case 'ALUGUEL':
                return $query->whereIn('tipo_negocio', ['ALUGUEL', 'VENDA_ALUGUEL'])
                    ->where('preco_aluguel', '>', 0);
            case 'TEMPORADA':
                return $query->where('tipo_negocio', 'TEMPORADA')
                    ->where('preco_temporada', '>', 0);
            default:
                return $query->where('tipo_negocio', $tipo);
        }
    }
}
