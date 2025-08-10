<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImovelPrecoHistoricoRequest;
use App\Http\Resources\ImovelPrecoHistoricoResource;
use App\Models\Imovel;
use App\Models\ImovelPrecoHistorico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ImovelPrecoHistoricoController extends Controller
{
    /**
     * Lista o histórico de preços de um imóvel específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $imovelId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $imovelId)
    {
        try {
            // Verificar se o imóvel existe
            $imovel = Imovel::findOrFail($imovelId);
            
            // Iniciar a query
            $query = ImovelPrecoHistorico::where('imovel_id', $imovelId)
                ->with(['criadoPor', 'atualizadoPor']);
            
            // Filtrar por tipo de negócio
            if ($request->has('tipo_negocio')) {
                $query->where('tipo_negocio', $request->tipo_negocio);
            }
            
            // Filtrar por período
            if ($request->has('data_inicio')) {
                $query->where('data_inicio', '>=', Carbon::parse($request->data_inicio)->startOfDay());
            }
            
            if ($request->has('data_fim')) {
                $query->where(function ($q) use ($request) {
                    $q->where('data_fim', '<=', Carbon::parse($request->data_fim)->endOfDay())
                      ->orWhereNull('data_fim');
                });
            }
            
            // Filtrar apenas registros vigentes
            if ($request->boolean('vigentes')) {
                $query->vigentes();
            }
            
            // Ordenação
            $sortField = $request->input('sort_by', 'data_inicio');
            $sortDirection = $request->input('sort_direction', 'desc');
            
            // Lista de campos permitidos para ordenação
            $allowedSortFields = ['data_inicio', 'data_fim', 'valor', 'created_at'];
            
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('data_inicio', 'desc');
            }
            
            // Paginação
            $perPage = $request->input('per_page', 15);
            $historicos = $query->paginate($perPage);
            
            return ImovelPrecoHistoricoResource::collection($historicos)
                ->additional([
                    'success' => true,
                    'message' => 'Histórico de preços listado com sucesso'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar histórico de preços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar histórico de preços',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra um novo preço histórico para o imóvel.
     *
     * @param  \App\Http\Requests\ImovelPrecoHistoricoRequest  $request
     * @param  int  $imovelId
     * @return \Illuminate\Http\Response
     */
    public function store(ImovelPrecoHistoricoRequest $request, $imovelId)
    {
        try {
            // Verificar se o imóvel existe
            $imovel = Imovel::findOrFail($imovelId);
            
            DB::beginTransaction();
            
            // Verificar sobreposição de datas para o mesmo tipo de negócio
            $this->verificarSobreposicaoDatas(
                $imovelId, 
                $request->tipo_negocio, 
                $request->data_inicio, 
                $request->data_fim,
                null
            );
            
            // Fechar registro vigente anterior (se existir)
            $this->fecharRegistroVigente(
                $imovelId, 
                $request->tipo_negocio, 
                Carbon::parse($request->data_inicio)->subDay(),
                'Substituído por novo registro'
            );
            
            // Criar novo registro histórico
            $historico = new ImovelPrecoHistorico();
            $historico->fill($request->validated());
            $historico->imovel_id = $imovelId;
            $historico->created_by = Auth::id();
            $historico->save();
            
            // Atualizar preço atual no imóvel
            $this->atualizarPrecoAtualImovel($imovel, $request->tipo_negocio, $request->valor);
            
            // Invalidar cache de análise
            $this->invalidarCacheAnalise($imovelId);
            
            DB::commit();
            
            return (new ImovelPrecoHistoricoResource($historico))
                ->additional([
                    'success' => true,
                    'message' => 'Registro de preço criado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar registro de preço: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar registro de preço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um registro histórico de preço.
     *
     * @param  \App\Http\Requests\ImovelPrecoHistoricoRequest  $request
     * @param  int  $imovelId
     * @param  int  $historicoId
     * @return \Illuminate\Http\Response
     */
    public function update(ImovelPrecoHistoricoRequest $request, $imovelId, $historicoId)
    {
        try {
            // Verificar se o imóvel existe
            $imovel = Imovel::findOrFail($imovelId);
            
            // Buscar o registro histórico
            $historico = ImovelPrecoHistorico::where('imovel_id', $imovelId)
                ->where('id', $historicoId)
                ->firstOrFail();
            
            // Verificar se o registro não está encerrado (data_fim no passado)
            $hoje = Carbon::today();
            if ($historico->data_fim !== null && $historico->data_fim->lessThan($hoje)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível atualizar um registro encerrado'
                ], 422);
            }
            
            DB::beginTransaction();
            
            // Verificar sobreposição de datas para o mesmo tipo de negócio
            if ($request->has('data_inicio') || $request->has('data_fim')) {
                $dataInicio = $request->has('data_inicio') ? $request->data_inicio : $historico->data_inicio;
                $dataFim = $request->has('data_fim') ? $request->data_fim : $historico->data_fim;
                
                $this->verificarSobreposicaoDatas(
                    $imovelId, 
                    $historico->tipo_negocio, 
                    $dataInicio, 
                    $dataFim,
                    $historicoId
                );
            }
            
            // Atualizar o registro histórico
            $historico->fill($request->validated());
            $historico->updated_by = Auth::id();
            $historico->save();
            
            // Se for o registro vigente e o valor foi alterado, atualizar o preço atual do imóvel
            if ($historico->estaVigente() && $request->has('valor')) {
                $this->atualizarPrecoAtualImovel($imovel, $historico->tipo_negocio, $request->valor);
            }
            
            // Invalidar cache de análise
            $this->invalidarCacheAnalise($imovelId);
            
            DB::commit();
            
            return (new ImovelPrecoHistoricoResource($historico))
                ->additional([
                    'success' => true,
                    'message' => 'Registro de preço atualizado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar registro de preço: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar registro de preço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um registro histórico de preço.
     *
     * @param  int  $imovelId
     * @param  int  $historicoId
     * @return \Illuminate\Http\Response
     */
    public function destroy($imovelId, $historicoId)
    {
        try {
            // Verificar se o imóvel existe
            $imovel = Imovel::findOrFail($imovelId);
            
            // Buscar o registro histórico
            $historico = ImovelPrecoHistorico::where('imovel_id', $imovelId)
                ->where('id', $historicoId)
                ->firstOrFail();
            
            // Verificar se é o registro vigente
            if ($historico->estaVigente()) {
                // Buscar o registro anterior para torná-lo vigente novamente
                $registroAnterior = ImovelPrecoHistorico::where('imovel_id', $imovelId)
                    ->where('tipo_negocio', $historico->tipo_negocio)
                    ->where('id', '!=', $historicoId)
                    ->orderBy('data_inicio', 'desc')
                    ->first();
                
                if ($registroAnterior) {
                    DB::beginTransaction();
                    
                    // Tornar o registro anterior vigente novamente
                    $registroAnterior->data_fim = null;
                    $registroAnterior->updated_by = Auth::id();
                    $registroAnterior->save();
                    
                    // Atualizar o preço atual do imóvel para o valor do registro anterior
                    $this->atualizarPrecoAtualImovel($imovel, $historico->tipo_negocio, $registroAnterior->valor);
                    
                    DB::commit();
                }
            }
            
            // Realizar soft delete do registro
            $historico->delete();
            
            // Invalidar cache de análise
            $this->invalidarCacheAnalise($imovelId);
            
            return response()->json([
                'success' => true,
                'message' => 'Registro de preço removido com sucesso'
            ]);
        } catch (\Exception $e) {
            if (isset($registroAnterior)) {
                DB::rollBack();
            }
            
            Log::error('Erro ao remover registro de preço: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover registro de preço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna análise de evolução de preços para um imóvel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $imovelId
     * @return \Illuminate\Http\Response
     */
    public function analiseEvolucao(Request $request, $imovelId)
    {
        try {
            // Verificar se o imóvel existe
            $imovel = Imovel::findOrFail($imovelId);
            
            // Definir parâmetros
            $tipoNegocio = $request->input('tipo_negocio', 'VENDA');
            $agrupamento = $request->input('agrupamento', 'mensal');
            $periodoMeses = $request->input('periodo_meses', 12);
            
            // Tentar obter do cache
            $cacheKey = "imovel_{$imovelId}_analise_precos_{$tipoNegocio}_{$agrupamento}_{$periodoMeses}";
            
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
            
            // Data de início para filtro
            $dataInicio = Carbon::now()->subMonths($periodoMeses);
            
            // Buscar histórico de preços
            $historicos = ImovelPrecoHistorico::where('imovel_id', $imovelId)
                ->where('tipo_negocio', $tipoNegocio)
                ->where('data_inicio', '>=', $dataInicio)
                ->orderBy('data_inicio', 'asc')
                ->get();
            
            // Preparar dados para análise
            $dadosAnalise = $this->prepararDadosAnalise($historicos, $agrupamento, $periodoMeses);
            
            // Adicionar preço atual como referência
            $precoAtual = $this->obterPrecoAtual($imovel, $tipoNegocio);
            $dadosAnalise['preco_atual'] = $precoAtual;
            
            // Calcular estatísticas
            $dadosAnalise['estatisticas'] = $this->calcularEstatisticas($historicos);
            
            // Armazenar em cache por 1 hora
            Cache::put($cacheKey, [
                'success' => true,
                'data' => $dadosAnalise
            ], 60 * 60);
            
            return response()->json([
                'success' => true,
                'data' => $dadosAnalise
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar análise de evolução de preços: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar análise de evolução de preços',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica sobreposição de datas para o mesmo tipo de negócio.
     *
     * @param  int  $imovelId
     * @param  string  $tipoNegocio
     * @param  \Carbon\Carbon  $dataInicio
     * @param  \Carbon\Carbon|null  $dataFim
     * @param  int|null  $excluirId ID do registro a ser excluído da verificação (para updates)
     * @return void
     * @throws \Exception
     */
    private function verificarSobreposicaoDatas($imovelId, $tipoNegocio, $dataInicio, $dataFim, $excluirId = null)
    {
        $query = ImovelPrecoHistorico::where('imovel_id', $imovelId)
            ->where('tipo_negocio', $tipoNegocio);
        
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        
        // Verificar sobreposição
        $sobreposicao = $query->where(function ($q) use ($dataInicio, $dataFim) {
            // Caso 1: dataInicio está dentro de um período existente
            $q->where(function ($q1) use ($dataInicio) {
                $q1->where('data_inicio', '<=', $dataInicio)
                   ->where(function ($q2) use ($dataInicio) {
                       $q2->whereNull('data_fim')
                          ->orWhere('data_fim', '>=', $dataInicio);
                   });
            });
            
            // Caso 2: dataFim está dentro de um período existente
            if ($dataFim) {
                $q->orWhere(function ($q1) use ($dataFim) {
                    $q1->where('data_inicio', '<=', $dataFim)
                       ->where(function ($q2) use ($dataFim) {
                           $q2->whereNull('data_fim')
                              ->orWhere('data_fim', '>=', $dataFim);
                       });
                });
            }
            
            // Caso 3: período existente está completamente dentro do novo período
            $q->orWhere(function ($q1) use ($dataInicio, $dataFim) {
                $q1->where('data_inicio', '>=', $dataInicio);
                
                if ($dataFim) {
                    $q1->where('data_fim', '<=', $dataFim);
                }
            });
        })->exists();
        
        if ($sobreposicao) {
            throw new \Exception('Existe sobreposição de datas para o mesmo tipo de negócio.');
        }
    }

    /**
     * Fecha o registro vigente anterior.
     *
     * @param  int  $imovelId
     * @param  string  $tipoNegocio
     * @param  \Carbon\Carbon  $dataFim
     * @param  string  $motivo
     * @return void
     */
    private function fecharRegistroVigente($imovelId, $tipoNegocio, $dataFim, $motivo = null)
    {
        $registroVigente = ImovelPrecoHistorico::where('imovel_id', $imovelId)
            ->where('tipo_negocio', $tipoNegocio)
            ->whereNull('data_fim')
            ->first();
        
        if ($registroVigente) {
            $registroVigente->data_fim = $dataFim;
            
            if ($motivo) {
                $registroVigente->observacao = ($registroVigente->observacao ? $registroVigente->observacao . "\n" : '') . $motivo;
            }
            
            $registroVigente->updated_by = Auth::id();
            $registroVigente->save();
        }
    }

    /**
     * Atualiza o preço atual do imóvel.
     *
     * @param  \App\Models\Imovel  $imovel
     * @param  string  $tipoNegocio
     * @param  float  $valor
     * @return void
     */
    private function atualizarPrecoAtualImovel($imovel, $tipoNegocio, $valor)
    {
        switch ($tipoNegocio) {
            case 'VENDA':
                $imovel->valor_venda = $valor;
                break;
            case 'LOCACAO':
                $imovel->valor_locacao = $valor;
                break;
            case 'TEMPORADA':
                $imovel->valor_temporada = $valor;
                break;
        }
        
        $imovel->updated_by = Auth::id();
        $imovel->save();
    }

    /**
     * Obtém o preço atual do imóvel para um tipo de negócio.
     *
     * @param  \App\Models\Imovel  $imovel
     * @param  string  $tipoNegocio
     * @return float|null
     */
    private function obterPrecoAtual($imovel, $tipoNegocio)
    {
        switch ($tipoNegocio) {
            case 'VENDA':
                return $imovel->valor_venda;
            case 'LOCACAO':
                return $imovel->valor_locacao;
            case 'TEMPORADA':
                return $imovel->valor_temporada;
            default:
                return null;
        }
    }

    /**
     * Prepara os dados para análise de evolução de preços.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $historicos
     * @param  string  $agrupamento
     * @param  int  $periodoMeses
     * @return array
     */
    private function prepararDadosAnalise($historicos, $agrupamento, $periodoMeses)
    {
        $hoje = Carbon::today();
        $dataInicio = Carbon::today()->subMonths($periodoMeses);
        
        // Inicializar arrays para dados do gráfico
        $labels = [];
        $valores = [];
        $variacoes = [];
        
        // Definir formato de data e intervalo baseado no agrupamento
        switch ($agrupamento) {
            case 'mensal':
                $formato = 'M/Y';
                $intervalo = 'month';
                break;
            case 'trimestral':
                $formato = 'Q/Y';
                $intervalo = 'quarter';
                break;
            case 'anual':
                $formato = 'Y';
                $intervalo = 'year';
                break;
            default:
                $formato = 'M/Y';
                $intervalo = 'month';
        }
        
        // Criar períodos para o gráfico
        $periodoAtual = clone $dataInicio;
        $dadosPorPeriodo = [];
        
        while ($periodoAtual->lessThanOrEqualTo($hoje)) {
            $chave = $periodoAtual->format($formato);
            $labels[] = $chave;
            $dadosPorPeriodo[$chave] = [
                'valor' => null,
                'data' => clone $periodoAtual
            ];
            
            $periodoAtual->add(1, $intervalo);
        }
        
        // Preencher valores para cada período
        foreach ($historicos as $historico) {
            $dataHistorico = $historico->data_inicio;
            $chave = $dataHistorico->format($formato);
            
            if (isset($dadosPorPeriodo[$chave])) {
                // Se já existe um valor para este período, usar o mais recente
                if ($dadosPorPeriodo[$chave]['valor'] === null || 
                    $dataHistorico->greaterThan($dadosPorPeriodo[$chave]['data'])) {
                    $dadosPorPeriodo[$chave]['valor'] = $historico->valor;
                    $dadosPorPeriodo[$chave]['data'] = $dataHistorico;
                }
            }
        }
        
        // Preencher valores e calcular variações
        $valorAnterior = null;
        
        foreach ($labels as $index => $label) {
            $valor = $dadosPorPeriodo[$label]['valor'];
            $valores[] = $valor;
            
            // Calcular variação percentual
            if ($index > 0 && $valorAnterior !== null && $valor !== null) {
                $variacao = (($valor - $valorAnterior) / $valorAnterior) * 100;
                $variacoes[] = round($variacao, 2);
            } else {
                $variacoes[] = null;
            }
            
            $valorAnterior = $valor;
        }
        
        // Preencher valores nulos com o último valor conhecido
        $ultimoValorConhecido = null;
        for ($i = count($valores) - 1; $i >= 0; $i--) {
            if ($valores[$i] !== null) {
                $ultimoValorConhecido = $valores[$i];
            } elseif ($ultimoValorConhecido !== null) {
                $valores[$i] = $ultimoValorConhecido;
            }
        }
        
        return [
            'labels' => $labels,
            'valores' => $valores,
            'variacoes' => $variacoes,
            'agrupamento' => $agrupamento
        ];
    }

    /**
     * Calcula estatísticas para o histórico de preços.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $historicos
     * @return array
     */
    private function calcularEstatisticas($historicos)
    {
        if ($historicos->isEmpty()) {
            return [
                'minimo' => null,
                'maximo' => null,
                'media' => null,
                'variacao_total' => null,
                'variacao_media' => null
            ];
        }
        
        $valores = $historicos->pluck('valor')->filter()->toArray();
        
        if (empty($valores)) {
            return [
                'minimo' => null,
                'maximo' => null,
                'media' => null,
                'variacao_total' => null,
                'variacao_media' => null
            ];
        }
        
        $minimo = min($valores);
        $maximo = max($valores);
        $media = array_sum($valores) / count($valores);
        
        // Calcular variação total (do primeiro ao último)
        $primeiro = $historicos->sortBy('data_inicio')->first()->valor;
        $ultimo = $historicos->sortByDesc('data_inicio')->first()->valor;
        $variacaoTotal = $primeiro > 0 ? (($ultimo - $primeiro) / $primeiro) * 100 : null;
        
        // Calcular variação média entre registros consecutivos
        $variacoes = [];
        $historicosOrdenados = $historicos->sortBy('data_inicio')->values();
        
        for ($i = 1; $i < $historicosOrdenados->count(); $i++) {
            $anterior = $historicosOrdenados[$i - 1]->valor;
            $atual = $historicosOrdenados[$i]->valor;
            
            if ($anterior > 0) {
                $variacoes[] = (($atual - $anterior) / $anterior) * 100;
            }
        }
        
        $variacaoMedia = !empty($variacoes) ? array_sum($variacoes) / count($variacoes) : null;
        
        return [
            'minimo' => $minimo,
            'minimo_formatado' => 'R$ ' . number_format($minimo, 2, ',', '.'),
            'maximo' => $maximo,
            'maximo_formatado' => 'R$ ' . number_format($maximo, 2, ',', '.'),
            'media' => $media,
            'media_formatada' => 'R$ ' . number_format($media, 2, ',', '.'),
            'variacao_total' => $variacaoTotal !== null ? round($variacaoTotal, 2) : null,
            'variacao_media' => $variacaoMedia !== null ? round($variacaoMedia, 2) : null
        ];
    }

    /**
     * Invalida o cache de análise de preços.
     *
     * @param  int  $imovelId
     * @return void
     */
    private function invalidarCacheAnalise($imovelId)
    {
        $tiposNegocio = ['VENDA', 'LOCACAO', 'TEMPORADA'];
        $agrupamentos = ['mensal', 'trimestral', 'anual'];
        $periodos = [6, 12, 24, 36];
        
        foreach ($tiposNegocio as $tipo) {
            foreach ($agrupamentos as $agrupamento) {
                foreach ($periodos as $periodo) {
                    $cacheKey = "imovel_{$imovelId}_analise_precos_{$tipo}_{$agrupamento}_{$periodo}";
                    Cache::forget($cacheKey);
                }
            }
        }
    }
}
