<?php

namespace App\Http\Controllers;

use App\Http\Requests\Etapas\CaracteristicasCondominioRequest;
use App\Http\Requests\Etapas\CaracteristicasRequest;
use App\Http\Requests\Etapas\ComodosRequest;
use App\Http\Requests\Etapas\ComplementosRequest;
use App\Http\Requests\Etapas\DescricaoRequest;
use App\Http\Requests\Etapas\ImagensRequest;
use App\Http\Requests\Etapas\InformacoesRequest;
use App\Http\Requests\Etapas\LocalizacaoRequest;
use App\Http\Requests\Etapas\MedidasRequest;
use App\Http\Requests\Etapas\PrecoRequest;
use App\Http\Requests\Etapas\ProprietarioRequest;
use App\Http\Requests\Etapas\ProximidadesRequest;
use App\Http\Requests\Etapas\PublicacaoRequest;
use App\Http\Requests\Etapas\SeoRequest;
use App\Http\Requests\Etapas\DadosPrivativosRequest;
use App\Http\Resources\Etapas\CaracteristicasCondominioResource;
use App\Http\Resources\Etapas\CaracteristicasResource;
use App\Http\Resources\Etapas\ComodosResource;
use App\Http\Resources\Etapas\ComplementosResource;
use App\Http\Resources\Etapas\DescricaoResource;
use App\Http\Resources\Etapas\DadosPrivativosResource;
use App\Http\Resources\Etapas\ImagensResource;
use App\Http\Resources\Etapas\InformacoesResource;
use App\Http\Resources\Etapas\LocalizacaoResource;
use App\Http\Resources\Etapas\MedidasResource;
use App\Http\Resources\Etapas\PrecoResource;
use App\Http\Resources\Etapas\ProprietarioResource;
use App\Http\Resources\Etapas\ProximidadesResource;
use App\Http\Resources\Etapas\PublicacaoResource;
use App\Http\Resources\Etapas\SeoResource;
use App\Models\Caracteristica;
use App\Models\Imovel;
use App\Models\ImovelDetalhe;
use App\Models\Proximidade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImovelEtapasController extends Controller
{
    /**
     * Verifica se o imóvel existe e se o usuário tem permissão para acessá-lo.
     *
     * @param int $id
     * @return \App\Models\Imovel
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function verificarImovel($id)
    {
        $imovel = Imovel::with('detalhes')->findOrFail($id);
        
        // Aqui poderia ter verificação de permissão
        // if (!$imovel->podeSerEditadoPor(auth()->user())) {
        //     throw new \Illuminate\Auth\Access\AuthorizationException('Você não tem permissão para editar este imóvel.');
        // }
        
        return $imovel;
    }
    
    /**
     * Verifica se o imóvel está completo para publicação.
     *
     * @param \App\Models\Imovel $imovel
     * @return array
     */
    public function verificarCompletude(Imovel $imovel)
    {
        $etapas = [
            'informacoes' => false,
            'comodos' => false,
            'medidas' => false,
            'preco' => false,
            'caracteristicas' => true, // Opcional
            'caracteristicas_condominio' => true, // Opcional
            'localizacao' => false,
            'proximidades' => true, // Opcional
            'descricao' => false,
            'complementos' => true, // Opcional
            'imagens' => false,
            'publicacao' => true, // Opcional
            'proprietario' => false,
            'dados_privativos' => true, // Opcional
            'seo' => true, // Opcional
        ];
        
        // Verificar etapa Informações
        $etapas['informacoes'] = !empty($imovel->tipo) && !empty($imovel->subtipo) && !empty($imovel->tipo_negocio);
        
        // Verificar etapa Cômodos
        $etapas['comodos'] = $imovel->quartos !== null;
        
        // Verificar etapa Medidas
        $etapas['medidas'] = !empty($imovel->area_total) && !empty($imovel->unidade_medida);
        
        // Verificar etapa Preço
        $etapas['preco'] = $this->verificarPrecoCompleto($imovel);
        
        // Verificar etapa Localização
        // Considera tanto os campos texto quanto os campos de relação
        $etapas['localizacao'] = !empty($imovel->uf) && 
            (!empty($imovel->cidade) || !empty($imovel->cidade_id)) && 
            (!empty($imovel->bairro) || !empty($imovel->bairro_id));
        
        // Verificar etapa Descrição
        $etapas['descricao'] = $imovel->detalhes && !empty($imovel->detalhes->titulo_anuncio) && !empty($imovel->detalhes->descricao);
        
        // Verificar etapa Imagens
        $etapas['imagens'] = $imovel->imagens()->count() > 0;
        
        // Verificar etapa Proprietário
        $etapas['proprietario'] = !empty($imovel->proprietario_id);
        
        return [
            'etapas' => $etapas,
            'completo' => !in_array(false, $etapas),
        ];
    }
    
    /**
     * Verifica se os dados de preço estão completos conforme o tipo de negócio.
     *
     * @param \App\Models\Imovel $imovel
     * @return bool
     */
    protected function verificarPrecoCompleto(Imovel $imovel)
    {
        if (empty($imovel->tipo_negocio)) {
            return false;
        }
        
        switch ($imovel->tipo_negocio) {
            case 'VENDA':
                return !empty($imovel->preco_venda);
            case 'ALUGUEL':
                return !empty($imovel->preco_aluguel);
            case 'TEMPORADA':
                return !empty($imovel->preco_temporada);
            case 'VENDA_ALUGUEL':
                return !empty($imovel->preco_venda) && !empty($imovel->preco_aluguel);
            default:
                return false;
        }
    }
    
    /**
     * Obtém os dados da etapa Informações de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInformacoes($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load('condominio');
            
            return response()->json([
                'success' => true,
                'data' => new InformacoesResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter informações do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter informações do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Informações de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInformacoes($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $informacoesRequest = new InformacoesRequest();
            $dados = $informacoesRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Usar fill para atualizar apenas os campos enviados na requisição
            $imovel->fill($dados);
            
            // Salvar as alterações
            $imovel->save();
            
            DB::commit();
            
            // Carregar relacionamentos necessários
            $imovel->load('condominio');
            
            return response()->json([
                'success' => true,
                'message' => 'Informações do imóvel atualizadas com sucesso.',
                'data' => new InformacoesResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar informações do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar informações do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Cômodos de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComodos($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new ComodosResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter cômodos do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter cômodos do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Cômodos de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComodos($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $comodosRequest = new ComodosRequest();
            $dados = $comodosRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Usar fill para atualizar apenas os campos enviados na requisição
            $imovel->fill($dados);
            
            // Salvar as alterações
            $imovel->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cômodos do imóvel atualizados com sucesso.',
                'data' => new ComodosResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar cômodos do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cômodos do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Medidas de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMedidas($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new MedidasResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter medidas do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter medidas do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Medidas de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMedidas($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $medidasRequest = new MedidasRequest();
            $dados = $medidasRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Usar fill para atualizar apenas os campos enviados na requisição
            $imovel->fill($dados);
            
            // Salvar as alterações
            $imovel->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Medidas do imóvel atualizadas com sucesso.',
                'data' => new MedidasResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar medidas do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar medidas do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Preço de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPreco($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new PrecoResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter preço do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter preço do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Preço de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePreco($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $precoRequest = new PrecoRequest();
            $dados = $precoRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Verificar se houve alteração nos preços para registrar histórico
            $precoAlterado = false;
            $camposPreco = ['preco_venda', 'preco_aluguel', 'preco_temporada', 'preco_condominio', 'preco_iptu'];
            
            foreach ($camposPreco as $campo) {
                if (isset($dados[$campo]) && $imovel->$campo != $dados[$campo]) {
                    $precoAlterado = true;
                    break;
                }
            }
            
            // Se houve alteração de preço, registrar no histórico
            if ($precoAlterado) {
                // Determinar qual tipo de negócio foi alterado
                $tipoNegocio = null;
                $valor = null;
                
                if (isset($dados['preco_venda']) && $imovel->preco_venda != $dados['preco_venda']) {
                    $tipoNegocio = 'VENDA';
                    $valor = $dados['preco_venda'];
                } elseif (isset($dados['preco_aluguel']) && $imovel->preco_aluguel != $dados['preco_aluguel']) {
                    $tipoNegocio = 'ALUGUEL';
                    $valor = $dados['preco_aluguel'];
                } elseif (isset($dados['preco_temporada']) && $imovel->preco_temporada != $dados['preco_temporada']) {
                    $tipoNegocio = 'TEMPORADA';
                    $valor = $dados['preco_temporada'];
                }
                
                // Se não foi identificado um tipo de negócio específico, usar o tipo atual do imóvel
                if (!$tipoNegocio) {
                    $tipoNegocio = $imovel->tipo_negocio === 'VENDA_ALUGUEL' ? 'VENDA' : $imovel->tipo_negocio;
                    
                    // Determinar o valor com base no tipo de negócio
                    if ($tipoNegocio === 'VENDA') {
                        $valor = $dados['preco_venda'] ?? $imovel->preco_venda;
                    } elseif ($tipoNegocio === 'ALUGUEL') {
                        $valor = $dados['preco_aluguel'] ?? $imovel->preco_aluguel;
                    } elseif ($tipoNegocio === 'TEMPORADA') {
                        $valor = $dados['preco_temporada'] ?? $imovel->preco_temporada;
                    }
                }
                
                // Criar o registro no histórico
                $imovel->precosHistorico()->create([
                    'tipo_negocio' => $tipoNegocio,
                    'valor' => $valor,
                    'data_inicio' => Carbon::today()->format('Y-m-d'),
                    'motivo' => $request->input('motivo_alteracao') ?? 'Atualização pelo wizard',
                    'observacao' => 'Preço atualizado via API. Valores: ' . 
                        (isset($dados['preco_venda']) ? 'Venda: ' . $dados['preco_venda'] . ' ' : '') .
                        (isset($dados['preco_aluguel']) ? 'Aluguel: ' . $dados['preco_aluguel'] . ' ' : '') .
                        (isset($dados['preco_temporada']) ? 'Temporada: ' . $dados['preco_temporada'] . ' ' : '') .
                        (isset($dados['preco_condominio']) ? 'Condomínio: ' . $dados['preco_condominio'] . ' ' : '') .
                        (isset($dados['preco_iptu']) ? 'IPTU: ' . $dados['preco_iptu'] : ''),
                    'created_by' => auth()->id(),
                ]);
            }
            
            // Usar fill para atualizar apenas os campos enviados na requisição
            $imovel->fill($dados);
            
            // Salvar as alterações
            $imovel->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Preço do imóvel atualizado com sucesso.',
                'data' => new PrecoResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar preço do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar preço do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Características de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaracteristicas($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load('caracteristicas');
            
            return response()->json([
                'success' => true,
                'data' => new CaracteristicasResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter características do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter características do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Características de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaracteristicas($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $caracteristicasRequest = new CaracteristicasRequest();
            $dados = $caracteristicasRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar características existentes
            if (isset($dados['caracteristicas'])) {
                $imovel->caracteristicas()->detach();
                
                if (!empty($dados['caracteristicas'])) {
                    $imovel->caracteristicas()->attach($dados['caracteristicas']);
                }
            }
            
            // Adicionar novas características
            if (isset($dados['novas_caracteristicas']) && !empty($dados['novas_caracteristicas'])) {
                foreach ($dados['novas_caracteristicas'] as $novaCaracteristica) {
                    $caracteristica = Caracteristica::create([
                        'nome' => $novaCaracteristica,
                        'escopo' => 'IMOVEL',
                    ]);
                    
                    $imovel->caracteristicas()->attach($caracteristica->id);
                }
            }
            
            DB::commit();
            
            // Carregar relacionamentos atualizados
            $imovel->load('caracteristicas');
            
            return response()->json([
                'success' => true,
                'message' => 'Características do imóvel atualizadas com sucesso.',
                'data' => new CaracteristicasResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar características do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar características do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Características do Condomínio de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaracteristicasCondominio($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load(['condominio', 'condominio.caracteristicas']);
            
            return response()->json([
                'success' => true,
                'data' => new CaracteristicasCondominioResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter características do condomínio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter características do condomínio.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Características do Condomínio de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaracteristicasCondominio($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $caracteristicasCondominioRequest = new CaracteristicasCondominioRequest();
            $dados = $caracteristicasCondominioRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel e condomínio
            DB::beginTransaction();
            
            // Atualizar condomínio_id no imóvel
            if (isset($dados['condominio_id'])) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->fill([
                    'condominio_id' => $dados['condominio_id'],
                ]);
                
                // Salvar as alterações
                $imovel->save();
            }
            
            // Se tem condomínio, atualizar suas características
            if ($imovel->condominio_id) {
                $condominio = $imovel->condominio;
                
                // Atualizar características existentes do condomínio
                // Sempre fazer detach primeiro, independentemente se há novas características ou não
                if (isset($dados['caracteristicas'])) {
                    // Remover todas as características existentes
                    $condominio->caracteristicas()->detach();
                    
                    // Adicionar as características selecionadas (se houver)
                    if (!empty($dados['caracteristicas'])) {
                        $condominio->caracteristicas()->attach($dados['caracteristicas']);
                    }
                }
                
                // Adicionar novas características ao condomínio
                if (isset($dados['novas_caracteristicas_condominio']) && !empty($dados['novas_caracteristicas_condominio'])) {
                    foreach ($dados['novas_caracteristicas_condominio'] as $novaCaracteristica) {
                        $caracteristica = Caracteristica::create([
                            'nome' => $novaCaracteristica,
                            'escopo' => 'CONDOMINIO',
                        ]);
                        
                        $condominio->caracteristicas()->attach($caracteristica->id);
                    }
                }
            } else if (isset($dados['caracteristicas']) || isset($dados['novas_caracteristicas_condominio'])) {
                // Se não tem condomínio mas enviou características, retornar erro
                throw new \Exception('Não é possível atualizar características sem um condomínio associado ao imóvel.');
            }
            
            DB::commit();
            
            // Carregar relacionamentos atualizados
            $imovel->load(['condominio', 'condominio.caracteristicas']);
            
            return response()->json([
                'success' => true,
                'message' => 'Características do condomínio atualizadas com sucesso.',
                'data' => new CaracteristicasCondominioResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar características do condomínio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar características do condomínio.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Localização de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocalizacao($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load(['cidade', 'bairro']);
            
            return response()->json([
                'success' => true,
                'data' => new LocalizacaoResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter localização do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter localização do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Localização de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocalizacao($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $localizacaoRequest = new LocalizacaoRequest();
            $dados = $localizacaoRequest->validate($request, $isRascunho);
            
            // Processar busca de cidade_id e bairro_id quando apenas os nomes forem fornecidos
            $dados = $this->processarCidadeBairro($dados, $imovel);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Usar fill para atualizar apenas os campos enviados na requisição
            $imovel->fill($dados);
            
            // Salvar as alterações
            $imovel->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Localização do imóvel atualizada com sucesso.',
                'data' => new LocalizacaoResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar localização do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar localização do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Processa os dados de cidade e bairro para buscar os IDs quando apenas os nomes forem fornecidos
     * ou para definir os IDs como null quando os nomes forem null
     *
     * @param array $dados
     * @param \App\Models\Imovel $imovel
     * @return array
     */
    protected function processarCidadeBairro(array $dados, $imovel)
    {
        // Tratar o caso da cidade
        if (array_key_exists('cidade', $dados)) {
            // Caso 1: Se cidade for explicitamente null, definir cidade_id como null também
            if ($dados['cidade'] === null) {
                $dados['cidade_id'] = null;
            }
            // Caso 2: Processar cidade_id quando temos apenas o nome da cidade (não null)
            elseif (!isset($dados['cidade_id']) && !empty($dados['cidade'])) {
                // Usar a UF do payload ou a UF atual do imóvel
                $uf = $dados['uf'] ?? $imovel->uf;
                
                if ($uf) {
                    // Buscar cidade pelo nome e UF
                    $cidade = \App\Models\Cidade::where('nome', ucwords(mb_strtolower($dados['cidade'])))
                        ->where('uf', strtoupper($uf))
                        ->first();
                    
                    if ($cidade) {
                        $dados['cidade_id'] = $cidade->id;
                    }
                }
            }
        }
        
        // Tratar o caso do bairro
        if (array_key_exists('bairro', $dados)) {
            // Caso 3: Se bairro for explicitamente null, definir bairro_id como null também
            if ($dados['bairro'] === null) {
                $dados['bairro_id'] = null;
            }
            // Caso 4: Processar bairro_id quando temos apenas o nome do bairro (não null)
            elseif (!empty($dados['bairro'])) {
                if (!isset($dados['cidade_id']))
                    $cidade_id = $imovel->cidade_id;
                else
                    $cidade_id = $dados['cidade_id'];

                // Se temos cidade_id (do payload ou do processamento acima)
                if (isset($cidade_id) && $cidade_id !== null) {
                    // Buscar bairro pelo nome e cidade_id
                    $bairro = \App\Models\Bairro::where('nome', ucwords(mb_strtolower($dados['bairro'])))
                        ->where('cidade_id', $cidade_id)
                        ->first();
                    
                    if ($bairro) {
                        $dados['bairro_id'] = $bairro->id;
                    }
                }
                // Se temos o nome da cidade e UF, mas não temos cidade_id
                elseif (isset($dados['cidade']) && !empty($dados['cidade']) && isset($dados['uf'])) {
                    // Buscar ou criar bairro pelo nome, nome da cidade e UF
                    $resultado = \App\Models\Bairro::buscarOuCriarPorCidadeUf(
                        $dados['bairro'],
                        $dados['cidade'],
                        $dados['uf']
                    );
                    
                    $bairro = $resultado['bairro'];
                    $dados['bairro_id'] = $bairro->id;
                    $dados['cidade_id'] = $bairro->cidade_id;
                }
            }
        }
        
        return $dados;
    }
    
    /**
     * Obtém os dados da etapa Proximidades de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProximidades($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load('proximidades');
            
            return response()->json([
                'success' => true,
                'data' => new ProximidadesResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter proximidades do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter proximidades do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Proximidades de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProximidades($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $proximidadesRequest = new ProximidadesRequest();
            $dados = $proximidadesRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar configuração de exibição
            if (isset($dados['mostrar_proximidades'])) {
                $detalhes = $imovel->detalhes;
                $config = $detalhes->config_exibicao ? json_decode($detalhes->config_exibicao, true) : [];
                $config['mostrar_proximidades'] = $dados['mostrar_proximidades'];
                $detalhes->config_exibicao = json_encode($config);
                $detalhes->save();
            }
            
            // Atualizar proximidades existentes
            if (isset($dados['proximidades'])) {
                // Remover todas as proximidades atuais
                $imovel->proximidades()->detach();
                
                // Adicionar as proximidades enviadas
                if (!empty($dados['proximidades'])) {
                    foreach ($dados['proximidades'] as $proximidadeId => $info) {
                        $distanciaMetros = null;
                        $distanciaTexto = null;
                        
                        if (isset($info['distancia_texto'])) {
                            $distanciaTexto = $info['distancia_texto'];
                            
                            // Tentar converter texto para metros
                            $distanciaMetros = $this->converterDistanciaParaMetros($distanciaTexto);
                        }
                        
                        $imovel->proximidades()->attach($proximidadeId, [
                            'distancia_metros' => $distanciaMetros,
                            'distancia_texto' => $distanciaTexto,
                        ]);
                    }
                }
            }
            
            // Adicionar novas proximidades
            if (isset($dados['novas_proximidades']) && !empty($dados['novas_proximidades'])) {
                foreach ($dados['novas_proximidades'] as $novaProximidade) {
                    $proximidade = Proximidade::create([
                        'nome' => $novaProximidade['nome'],
                        'categoria' => $novaProximidade['categoria'] ?? 'OUTROS',
                    ]);
                    
                    $distanciaMetros = null;
                    $distanciaTexto = null;
                    
                    if (isset($novaProximidade['distancia_texto'])) {
                        $distanciaTexto = $novaProximidade['distancia_texto'];
                        
                        // Tentar converter texto para metros
                        $distanciaMetros = $this->converterDistanciaParaMetros($distanciaTexto);
                    }
                    
                    $imovel->proximidades()->attach($proximidade->id, [
                        'distancia_metros' => $distanciaMetros,
                        'distancia_texto' => $distanciaTexto,
                    ]);
                }
            }
            
            DB::commit();
            
            // Carregar relacionamentos atualizados
            $imovel->load('proximidades');
            
            return response()->json([
                'success' => true,
                'message' => 'Proximidades do imóvel atualizadas com sucesso.',
                'data' => new ProximidadesResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar proximidades do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar proximidades do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Converte uma string de distância para metros.
     *
     * @param string $distanciaTexto
     * @return int|null
     */
    protected function converterDistanciaParaMetros($distanciaTexto)
    {
        if (empty($distanciaTexto)) {
            return null;
        }
        
        // Padronizar o texto para facilitar a conversão
        $texto = strtolower(trim($distanciaTexto));
        
        // Remover pontos e substituir vírgulas por pontos para números decimais
        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);
        
        // Extrair o número da string
        preg_match('/([0-9]+[\.,]?[0-9]*)/', $texto, $matches);
        
        if (empty($matches)) {
            return null;
        }
        
        $numero = (float) $matches[1];
        
        // Verificar a unidade
        if (strpos($texto, 'km') !== false || strpos($texto, 'quilometro') !== false) {
            return (int) ($numero * 1000);
        } elseif (strpos($texto, 'm') !== false || strpos($texto, 'metro') !== false) {
            return (int) $numero;
        }
        
        // Se não encontrou unidade, assume metros
        return (int) $numero;
    }
    
    /**
     * Obtém os dados da etapa Descrição de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDescricao($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new DescricaoResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter descrição do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter descrição do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Descrição de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDescricao($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $descricaoRequest = new DescricaoRequest();
            $dados = $descricaoRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar campos diretos do imóvel
            $camposImovel = array_intersect_key($dados, array_flip([
                'titulo', 'descricao', 'palavras_chave',
                'gerar_titulo_automatico', 'gerar_descricao_automatica'
            ]));
            
            if (!empty($camposImovel)) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->fill($camposImovel);
                
                // Salvar as alterações
                $imovel->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Descrição do imóvel atualizada com sucesso.',
                'data' => new DescricaoResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar descrição do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar descrição do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Complementos de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComplementos($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load(['videos', 'plantas']);
            
            return response()->json([
                'success' => true,
                'data' => new ComplementosResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter complementos do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter complementos do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Complementos de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComplementos($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $complementosRequest = new ComplementosRequest();
            $dados = $complementosRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar campos de observações e tour virtual
            $camposDetalhes = array_intersect_key($dados, array_flip([
                'observacoes_internas', 'tour_virtual'
            ]));
            
            if (!empty($camposDetalhes)) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->detalhes->fill($camposDetalhes);
                
                // Salvar as alterações
                $imovel->detalhes->save();
            }
            
            // Atualizar vídeos
            if (isset($dados['videos'])) {
                // Remover vídeos existentes
                $imovel->videos()->delete();
                
                // Adicionar novos vídeos
                if (!empty($dados['videos'])) {
                    foreach ($dados['videos'] as $index => $video) {
                        $imovel->videos()->create([
                            'titulo' => $video['titulo'] ?? null,
                            'descricao' => $video['descricao'] ?? null,
                            'url' => $video['url'],
                            'ordem' => $index + 1,
                        ]);
                    }
                }
            }
            
            // Atualizar plantas
            if (isset($dados['plantas'])) {
                // Remover plantas existentes que não estão na lista enviada
                $plantasExistentes = $imovel->plantas->pluck('id')->toArray();
                $plantasEnviadas = collect($dados['plantas'])->pluck('id')->filter()->toArray();
                $plantasRemover = array_diff($plantasExistentes, $plantasEnviadas);
                
                if (!empty($plantasRemover)) {
                    $imovel->plantas()->whereIn('id', $plantasRemover)->delete();
                }
                
                // Atualizar ou adicionar plantas
                if (!empty($dados['plantas'])) {
                    foreach ($dados['plantas'] as $index => $planta) {
                        if (isset($planta['id']) && $planta['id']) {
                            // Atualizar planta existente
                            $plantaModel = $imovel->plantas()->where('id', $planta['id'])->first();
                            if ($plantaModel) {
                                // Usar fill para atualizar apenas os campos enviados na requisição
                                $plantaModel->fill([
                                    'titulo' => $planta['titulo'] ?? null,
                                    'descricao' => $planta['descricao'] ?? null,
                                    'ordem' => $index + 1,
                                ]);
                                
                                // Salvar as alterações
                                $plantaModel->save();
                            }
                        } else if (isset($planta['caminho']) && $planta['caminho']) {
                            // Adicionar nova planta
                            $imovel->plantas()->create([
                                'titulo' => $planta['titulo'] ?? null,
                                'descricao' => $planta['descricao'] ?? null,
                                'caminho' => $planta['caminho'],
                                'ordem' => $index + 1,
                            ]);
                        }
                    }
                }
            }
            
            DB::commit();
            
            // Carregar relacionamentos atualizados
            $imovel->load(['videos', 'plantas']);
            
            return response()->json([
                'success' => true,
                'message' => 'Complementos do imóvel atualizados com sucesso.',
                'data' => new ComplementosResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar complementos do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar complementos do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Imagens de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImagens($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load('imagens');
            
            return response()->json([
                'success' => true,
                'data' => new ImagensResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter imagens do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter imagens do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Imagens de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImagens($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $imagensRequest = new ImagensRequest();
            $dados = $imagensRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar imagens existentes que não estão na lista enviada
            if (isset($dados['imagens'])) {
                $imagensExistentes = $imovel->imagens->pluck('id')->toArray();
                $imagensEnviadas = collect($dados['imagens'])->pluck('id')->filter()->toArray();
                $imagensRemover = array_diff($imagensExistentes, $imagensEnviadas);
                
                if (!empty($imagensRemover)) {
                    $imovel->imagens()->whereIn('id', $imagensRemover)->delete();
                }
                
                // Atualizar ou adicionar imagens
                if (!empty($dados['imagens'])) {
                    // Resetar a imagem principal se necessário
                    $temPrincipal = false;
                    foreach ($dados['imagens'] as $imagem) {
                        if (isset($imagem['principal']) && $imagem['principal']) {
                            $temPrincipal = true;
                            break;
                        }
                    }
                    
                    if ($temPrincipal) {
                        // Remover flag principal de todas as imagens
                        // Buscar todas as imagens e atualizá-las individualmente para manter consistência
                        // com a abordagem de atualizações parciais
                        foreach ($imovel->imagens as $imagem) {
                            $imagem->fill(['principal' => false]);
                            $imagem->save();
                        }
                    }
                    
                    // Processar cada imagem
                    foreach ($dados['imagens'] as $index => $imagem) {
                        if (isset($imagem['id']) && $imagem['id']) {
                            // Atualizar imagem existente
                            $imagemModel = $imovel->imagens()->where('id', $imagem['id'])->first();
                            if ($imagemModel) {
                                // Usar fill para atualizar apenas os campos enviados na requisição
                                $imagemModel->fill([
                                    'titulo' => $imagem['titulo'] ?? null,
                                    'descricao' => $imagem['descricao'] ?? null,
                                    'ordem' => $index + 1,
                                    'principal' => isset($imagem['principal']) ? (bool) $imagem['principal'] : false,
                                ]);
                                
                                // Salvar as alterações
                                $imagemModel->save();
                            }
                        } else if (isset($imagem['caminho']) && $imagem['caminho']) {
                            // Adicionar nova imagem
                            $imovel->imagens()->create([
                                'titulo' => $imagem['titulo'] ?? null,
                                'descricao' => $imagem['descricao'] ?? null,
                                'caminho' => $imagem['caminho'],
                                'ordem' => $index + 1,
                                'principal' => isset($imagem['principal']) ? (bool) $imagem['principal'] : false,
                            ]);
                        }
                    }
                    
                    // Se não tem imagem principal, definir a primeira como principal
                    if (!$temPrincipal && $imovel->imagens()->count() > 0) {
                        $primeiraImagem = $imovel->imagens()->orderBy('ordem')->first();
                        if ($primeiraImagem) {
                            // Usar fill para atualizar apenas os campos enviados na requisição
                            $primeiraImagem->fill(['principal' => true]);
                            
                            // Salvar as alterações
                            $primeiraImagem->save();
                        }
                    }
                }
            }
            
            DB::commit();
            
            // Carregar relacionamentos atualizados
            $imovel->load('imagens');
            
            return response()->json([
                'success' => true,
                'message' => 'Imagens do imóvel atualizadas com sucesso.',
                'data' => new ImagensResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar imagens do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar imagens do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Publicação de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicacao($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new PublicacaoResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de publicação do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados de publicação do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Publicação de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePublicacao($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $publicacaoRequest = new PublicacaoRequest();
            $dados = $publicacaoRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar campos diretos do imóvel
            $camposImovel = array_intersect_key($dados, array_flip([
                'publicar_site', 'destaque_site', 'data_publicacao', 'data_expiracao', 'status'
            ]));
            
            if (!empty($camposImovel)) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->fill($camposImovel);
                
                // Salvar as alterações
                $imovel->save();
            }
            
            // Atualizar configurações de exibição nos detalhes
            if (isset($dados['publicar_portais']) || isset($dados['portais']) || isset($dados['redes_sociais'])) {
                $detalhes = $imovel->detalhes;
                $config = $detalhes->config_exibicao ? json_decode($detalhes->config_exibicao, true) : [];
                
                if (isset($dados['publicar_portais'])) {
                    $config['publicar_portais'] = $dados['publicar_portais'];
                }
                
                if (isset($dados['portais'])) {
                    $config['portais'] = $dados['portais'];
                }
                
                if (isset($dados['redes_sociais'])) {
                    $config['redes_sociais'] = $dados['redes_sociais'];
                }
                
                $detalhes->config_exibicao = json_encode($config);
                $detalhes->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Dados de publicação do imóvel atualizados com sucesso.',
                'data' => new PublicacaoResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar dados de publicação do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar dados de publicação do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Proprietário de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProprietario($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new ProprietarioResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados do proprietário do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do proprietário do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Proprietário de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProprietario($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $proprietarioRequest = new ProprietarioRequest();
            $dados = $proprietarioRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar campos diretos do imóvel
            $camposImovel = array_intersect_key($dados, array_flip([
                'proprietario_id', 'corretor_id'
            ]));
            
            if (!empty($camposImovel)) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->fill($camposImovel);
                
                // Salvar as alterações
                $imovel->save();
            }
            
            // Atualizar campos de detalhes
            $camposDetalhes = array_intersect_key($dados, array_flip([
                'exclusividade', 'comissao_porcentagem', 'comissao_valor', 'anotacoes_contrato'
            ]));
            
            if (!empty($camposDetalhes)) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->detalhes->fill($camposDetalhes);
                
                // Salvar as alterações
                $imovel->detalhes->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Dados do proprietário do imóvel atualizados com sucesso.',
                'data' => new ProprietarioResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar dados do proprietário do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar dados do proprietário do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa SEO de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSeo($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            return response()->json([
                'success' => true,
                'data' => new SeoResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados SEO do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados SEO do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa SEO de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSeo($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $seoRequest = new SeoRequest();
            $dados = $seoRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel
            DB::beginTransaction();
            
            // Atualizar campos de SEO nos detalhes
            $camposDetalhes = array_intersect_key($dados, array_flip([
                'seo_titulo', 'seo_descricao', 'seo_palavras_chave', 'url_amigavel',
                'gerar_seo_titulo_automatico', 'gerar_seo_descricao_automatica'
            ]));
            
            if (!empty($camposDetalhes)) {
                // Usar fill para atualizar apenas os campos enviados na requisição
                $imovel->detalhes->fill($camposDetalhes);
                
                // Salvar as alterações
                $imovel->detalhes->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Dados SEO do imóvel atualizados com sucesso.',
                'data' => new SeoResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar dados SEO do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar dados SEO do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtém os dados da etapa Dados Privativos de um imóvel.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDadosPrivativos($id)
    {
        try {
            $imovel = $this->verificarImovel($id);
            $imovel->load('corretor');
            
            return response()->json([
                'success' => true,
                'data' => new DadosPrivativosResource($imovel),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados privativos do imóvel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados privativos do imóvel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Atualiza os dados da etapa Dados Privativos de um imóvel.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDadosPrivativos($id, Request $request)
    {
        try {
            $imovel = $this->verificarImovel($id);
            
            // Verificar se é modo rascunho
            $isRascunho = $request->has('rascunho') && $request->rascunho === 'true';
            
            // Validar dados
            $dadosPrivativosRequest = new DadosPrivativosRequest();
            $dados = $dadosPrivativosRequest->validate($request, $isRascunho);
            
            // Atualizar imóvel e detalhes
            DB::beginTransaction();
            
            // Atualizar corretor_id no imóvel
            if (array_key_exists('corretor_id', $dados)) {
                $imovel->corretor_id = $dados['corretor_id'];
                $imovel->save();
            }
            
            // Garantir que o registro de detalhes existe
            $detalhes = $imovel->detalhes;
            if (!$detalhes) {
                $detalhes = new ImovelDetalhe(['id' => $imovel->id]);
                $detalhes->save();
                $imovel->refresh();
            }
            
            // Remover corretor_id dos dados para não tentar salvar na tabela errada
            unset($dados['corretor_id']);
            
            // Atualizar dados privativos
            // Usar fill para atualizar apenas os campos enviados na requisição
            $imovel->detalhes->fill($dados);
            
            // Salvar as alterações
            $imovel->detalhes->save();
            
            DB::commit();
            
            // Recarregar relacionamentos
            $imovel->load('corretor');
            
            return response()->json([
                'success' => true,
                'message' => 'Dados privativos atualizados com sucesso.',
                'data' => new DadosPrivativosResource($imovel),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar dados privativos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar dados privativos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}