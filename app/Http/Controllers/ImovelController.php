<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImovelRequest;
use App\Http\Requests\ImovelSearchRequest;
use App\Http\Resources\ImovelResource;
use App\Http\Resources\ImovelCollection;
use App\Models\Imovel;
use App\Models\ImovelDetalhe;
use App\Models\ImovelImagem;
use App\Models\ImovelPrecoHistorico;
use App\Models\Caracteristica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ImovelController extends Controller
{
    /**
     * Exibe uma lista paginada de imóveis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Imovel::query();
            
            // Aplicar filtros
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            if ($request->has('subtipo')) {
                $query->where('subtipo', $request->subtipo);
            }
            
            if ($request->has('perfil')) {
                $query->where('perfil', $request->perfil);
            }
            
            if ($request->has('cidade')) {
                $query->where('cidade', 'like', '%' . $request->cidade . '%');
            }
            
            if ($request->has('bairro')) {
                $query->where('bairro', 'like', '%' . $request->bairro . '%');
            }
            
            if ($request->has('codigo_referencia')) {
                $query->where('codigo_referencia', $request->codigo_referencia);
            }
            
            // Filtros de preço
            if ($request->has('valor_venda_min')) {
                $query->where('valor_venda', '>=', $request->valor_venda_min);
            }
            
            if ($request->has('valor_venda_max')) {
                $query->where('valor_venda', '<=', $request->valor_venda_max);
            }
            
            if ($request->has('valor_locacao_min')) {
                $query->where('valor_locacao', '>=', $request->valor_locacao_min);
            }
            
            if ($request->has('valor_locacao_max')) {
                $query->where('valor_locacao', '<=', $request->valor_locacao_max);
            }
            
            // Filtros de características físicas
            if ($request->has('quartos_min')) {
                $query->where('quartos', '>=', $request->quartos_min);
            }
            
            if ($request->has('banheiros_min')) {
                $query->where('banheiros', '>=', $request->banheiros_min);
            }
            
            if ($request->has('suites_min')) {
                $query->where('suites', '>=', $request->suites_min);
            }
            
            if ($request->has('vagas_min')) {
                $query->where('vagas', '>=', $request->vagas_min);
            }
            
            if ($request->has('area_total_min')) {
                $query->where('area_total', '>=', $request->area_total_min);
            }
            
            if ($request->has('area_privativa_min')) {
                $query->where('area_privativa', '>=', $request->area_privativa_min);
            }
            
            // Ordenação
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            
            // Lista de campos permitidos para ordenação
            $allowedSortFields = [
                'created_at', 'updated_at', 'codigo_referencia', 
                'valor_venda', 'valor_locacao', 'area_total', 'area_privativa'
            ];
            
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            // Paginação
            $perPage = $request->input('per_page', 15);
            $imoveis = $query->paginate($perPage);
            
            return new ImovelCollection($imoveis);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar imóveis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe os detalhes de um imóvel específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            // Iniciar query com relacionamentos padrão
            $query = Imovel::with([
                'detalhes',
                'condominio',
                'caracteristicas',
                'proximidades',
                'imagens' => function ($query) {
                    $query->orderBy('principal', 'desc')->orderBy('ordem', 'asc');
                },
                'videos' => function ($query) {
                    $query->orderBy('ordem', 'asc');
                },
                'plantas' => function ($query) {
                    $query->orderBy('ordem', 'asc');
                },
                'proprietario',
                'corretor',
                'criadoPor',
                'atualizadoPor'
            ]);
            
            // Incluir histórico de preços se solicitado
            if ($request->boolean('incluir_historico_precos')) {
                $query->with(['precosHistorico' => function ($query) use ($request) {
                    // Ordenar por data de início (decrescente)
                    $query->orderBy('data_inicio', 'desc');
                    
                    // Filtrar por tipo de negócio se especificado
                    if ($request->has('tipo_negocio')) {
                        $query->where('tipo_negocio', $request->tipo_negocio);
                    }
                    
                    // Filtrar apenas registros vigentes se solicitado
                    if ($request->boolean('apenas_vigentes')) {
                        $query->vigentes();
                    }
                    
                    // Incluir usuários relacionados
                    $query->with(['criadoPor', 'atualizadoPor']);
                }]);
            }
            
            $imovel = $query->findOrFail($id);
            
            return new ImovelResource($imovel);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar imóvel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inicia o cadastro de um novo imóvel com status RASCUNHO.
     *
     * @return \Illuminate\Http\Response
     */
    public function iniciar()
    {
        try {
            DB::beginTransaction();
            
            // Criar imóvel com valores padrão
            $imovel = new Imovel();
            $imovel->fill([
                'tipo' => 'APARTAMENTO',
                'subtipo' => 'PADRAO',
                'perfil' => 'RESIDENCIAL',
                'status' => 'RASCUNHO',
                'corretor_id' => Auth::id(),
                'created_by' => Auth::id()
            ]);
            
            // Gerar código de referência automaticamente antes de salvar
            $imovel->codigo_referencia = $imovel->gerarCodigoReferencia();
            $imovel->codigo_referencia_editado = false; // Marca que o código não foi editado manualmente
            
            $imovel->save();
            
            // Criar registro de detalhes vazio
            $detalhes = new ImovelDetalhe();
            $detalhes->id = $imovel->id;
            $detalhes->created_by = Auth::id();
            $detalhes->save();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Cadastro de imóvel iniciado com sucesso',
                'imovel' => new ImovelResource($imovel)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Erro ao iniciar cadastro de imóvel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplica um imóvel existente, criando uma cópia com status RASCUNHO.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicar($id)
    {
        try {
            DB::beginTransaction();
            
            // Buscar imóvel original com relacionamentos
            $imovelOriginal = Imovel::with([
                'detalhes',
                'caracteristicas',
                'proximidades',
                'imagens',
                'videos',
                'plantas'
            ])->findOrFail($id);
            
            // Criar novo imóvel com os mesmos dados do original
            $novoImovel = new Imovel();
            
            // Copiar atributos básicos do imóvel
            $atributosParaCopiar = [
                'tipo', 'subtipo', 'perfil', 'tipo_negocio',
                'quartos', 'suites', 'banheiros', 'vagas',
                'area_total', 'area_privativa', 'area_terreno', 'unidade_medida',
                'andar', 'total_andares', 'unidades_andar', 'unidades_predio',
                'ano_construcao', 'incorporadora', 'construtora',
                'preco_venda', 'preco_locacao', 'preco_temporada', 'preco_condominio', 'preco_iptu',
                'aceita_permuta', 'aceita_financiamento',
                'uf', 'cidade', 'bairro', 'logradouro', 'numero', 'complemento', 'cep',
                'latitude', 'longitude', 'mostrar_mapa_site',
                'condominio_id', 'proprietario_id', 'corretor_id'
            ];
            
            foreach ($atributosParaCopiar as $atributo) {
                if (isset($imovelOriginal->$atributo)) {
                    $novoImovel->$atributo = $imovelOriginal->$atributo;
                }
            }
            
            // Definir status como RASCUNHO e dados de auditoria
            $novoImovel->status = 'RASCUNHO';
            $novoImovel->created_by = Auth::id();
            
            // Gerar novo código de referência
            $novoImovel->codigo_referencia = $novoImovel->gerarCodigoReferencia();
            $novoImovel->codigo_referencia_editado = false;
            
            // Salvar o novo imóvel
            $novoImovel->save();
            
            // Criar detalhes do imóvel
            $detalhesOriginal = $imovelOriginal->detalhes;
            $novosDetalhes = new ImovelDetalhe();
            $novosDetalhes->id = $novoImovel->id;
            
            // Copiar atributos dos detalhes
            if ($detalhesOriginal) {
                $atributosDetalhesParaCopiar = [
                    'titulo_anuncio', 'descricao', 'palavras_chave',
                    'gerar_titulo_automatico', 'gerar_descricao_automatica',
                    'observacoes_internas', 'tour_virtual',
                    'seo_titulo', 'seo_descricao', 'seo_palavras_chave', 'url_amigavel',
                    'gerar_seo_titulo_automatico', 'gerar_seo_descricao_automatica',
                    'exclusividade', 'comissao_porcentagem', 'comissao_valor', 'anotacoes_contrato',
                    'config_exibicao'
                ];
                
                foreach ($atributosDetalhesParaCopiar as $atributo) {
                    if (isset($detalhesOriginal->$atributo)) {
                        $novosDetalhes->$atributo = $detalhesOriginal->$atributo;
                    }
                }
            }
            
            $novosDetalhes->created_by = Auth::id();
            $novosDetalhes->save();
            
            // Copiar características
            if ($imovelOriginal->caracteristicas->count() > 0) {
                $caracteristicas = [];
                foreach ($imovelOriginal->caracteristicas as $caracteristica) {
                    $caracteristicas[$caracteristica->id] = [
                        'valor' => $caracteristica->pivot->valor,
                        'created_by' => Auth::id()
                    ];
                }
                $novoImovel->caracteristicas()->attach($caracteristicas);
            }
            
            // Copiar proximidades
            if ($imovelOriginal->proximidades->count() > 0) {
                $proximidades = [];
                foreach ($imovelOriginal->proximidades as $proximidade) {
                    $proximidades[$proximidade->id] = [
                        'distancia_metros' => $proximidade->pivot->distancia_metros,
                        'distancia_texto' => $proximidade->pivot->distancia_texto,
                        'created_by' => Auth::id()
                    ];
                }
                $novoImovel->proximidades()->attach($proximidades);
            }
            
            // Não copiar imagens, vídeos e plantas - o usuário deverá adicioná-los novamente
            
            DB::commit();
            
            // Carregar relacionamentos para o retorno
            $novoImovel->load(['detalhes', 'caracteristicas', 'proximidades']);
            
            return response()->json([
                'success' => true,
                'message' => 'Imóvel duplicado com sucesso',
                'imovel' => new ImovelResource($novoImovel)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar imóvel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Atualiza um imóvel existente.
     *
     * @param  \App\Http\Requests\ImovelRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ImovelRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $imovel = Imovel::findOrFail($id);
            $tipoAnterior = $imovel->tipo;
            
            // Verificar se é um rascunho
            $isRascunho = $imovel->status === 'RASCUNHO';
            
            // Verificar se o código de referência foi editado manualmente
            $codigoEditadoManualmente = $request->has('codigo_referencia');
            
            // Atualizar dados do imóvel
            $imovel->fill($request->validated());
            
            // Se o código foi editado manualmente, marcar a flag
            if ($codigoEditadoManualmente) {
                $imovel->codigo_referencia_editado = true;
            }
            
            // Atualizar código de referência se o tipo mudou e o código não foi personalizado
            if ($tipoAnterior !== $imovel->tipo && !$imovel->codigo_referencia_editado) {
                $imovel->codigo_referencia = $imovel->gerarCodigoReferencia();
            }
            
            // Verificar se todos os campos obrigatórios foram preenchidos para ativar o imóvel
            if ($isRascunho && $request->has('status') && $request->status === 'ATIVO') {
                $validator = Validator::make($imovel->toArray(), [
                    'tipo' => 'required',
                    'subtipo' => 'required',
                    'perfil' => 'required',
                    'proprietario_id' => 'required',
                    'corretor_id' => 'required',
                    'area_total' => 'required',
                    'quartos' => 'required',
                    'banheiros' => 'required',
                    'cep' => 'required',
                    'uf' => 'required',
                    'cidade' => 'required',
                    'bairro' => 'required',
                    'logradouro' => 'required',
                ]);
                
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Não é possível ativar o imóvel. Campos obrigatórios não preenchidos.',
                        'errors' => $validator->errors()
                    ], 422);
                }
            }
            
            // Verificar se houve alteração nos preços para registrar histórico
            $this->verificarAlteracaoPrecos($imovel);
            
            $imovel->save();
            
            // Atualizar detalhes do imóvel se fornecidos
            if ($request->has('detalhes')) {
                $detalhes = ImovelDetalhe::findOrNew($imovel->id);
                $detalhes->fill($request->detalhes);
                $detalhes->id = $imovel->id; // Garantir que o ID seja o mesmo do imóvel
                $detalhes->save();
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Imóvel atualizado com sucesso',
                'imovel' => new ImovelResource($imovel->fresh(['detalhes']))
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Erro ao atualizar imóvel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove logicamente um imóvel.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $imovel = Imovel::findOrFail($id);
            $imovel->delete();
            
            return response()->json([
                'message' => 'Imóvel excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir imóvel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valida se um código de referência está disponível.
     *
     * @param  string  $codigo
     * @param  int|null  $id
     * @return \Illuminate\Http\Response
     */
    public function validarCodigoReferencia($codigo, $id = null)
    {
        try {
            $query = Imovel::where('codigo_referencia', $codigo);
            
            // Se um ID foi fornecido, ignorar o próprio imóvel na validação
            if ($id) {
                $query->where('id', '!=', $id);
            }
            
            $existe = $query->exists();
            
            return response()->json([
                'disponivel' => !$existe,
                'codigo' => $codigo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao validar código de referência',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza o código de referência de um imóvel, APENAS quando for mudado o tipo do imóvel
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function atualizarCodigoReferencia(Request $request, $id)
    {
        try {
            $request->validate([
                'codigo_referencia' => 'required|string|max:20'
            ]);
            
            $imovel = Imovel::findOrFail($id);
            
            // Verificar se o código já existe em outro imóvel
            $codigoExiste = Imovel::where('codigo_referencia', $request->codigo_referencia)
                ->where('id', '!=', $id)
                ->exists();
            
            if ($codigoExiste) {
                return response()->json([
                    'message' => 'O código de referência já está em uso por outro imóvel',
                    'disponivel' => false
                ], 422);
            }
            
            $imovel->codigo_referencia = $request->codigo_referencia;
            $imovel->save();
            
            return response()->json([
                'message' => 'Código de referência atualizado com sucesso',
                'imovel' => new ImovelResource($imovel)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar código de referência',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa o upload de uma imagem para o imóvel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadImagem(Request $request, $id)
    {
        try {
            $request->validate([
                'imagem' => 'required|image|max:5120', // 5MB max
                'titulo' => 'nullable|string|max:255',
                'principal' => 'nullable|boolean'
            ]);
            
            $imovel = Imovel::findOrFail($id);
            
            // Processar upload da imagem
            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/imoveis/' . $imovel->id . '/imagens', $filename);
                
                // Criar registro da imagem
                $imagem = new ImovelImagem();
                $imagem->imovel_id = $imovel->id;
                $imagem->titulo = $request->titulo ?? $file->getClientOriginalName();
                $imagem->caminho = str_replace('public/', '', $path);
                $imagem->principal = $request->has('principal') ? $request->principal : false;
                
                // Definir ordem (última + 1)
                $ultimaOrdem = ImovelImagem::where('imovel_id', $imovel->id)->max('ordem') ?? 0;
                $imagem->ordem = $ultimaOrdem + 1;
                
                $imagem->save();
                
                return response()->json([
                    'message' => 'Imagem enviada com sucesso',
                    'imagem' => $imagem
                ], 201);
            }
            
            return response()->json([
                'message' => 'Nenhuma imagem enviada'
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao enviar imagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordena as imagens do imóvel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reordenarImagens(Request $request, $id)
    {
        try {
            $request->validate([
                'imagens' => 'required|array',
                'imagens.*' => 'required|integer|exists:imoveis_imagens,id'
            ]);
            
            $imovel = Imovel::findOrFail($id);
            
            // Verificar se todas as imagens pertencem ao imóvel
            $imagens = ImovelImagem::whereIn('id', $request->imagens)
                ->where('imovel_id', $imovel->id)
                ->get();
            
            if (count($imagens) !== count($request->imagens)) {
                return response()->json([
                    'message' => 'Algumas imagens não pertencem a este imóvel'
                ], 422);
            }
            
            // Atualizar ordem das imagens
            foreach ($request->imagens as $index => $imagemId) {
                ImovelImagem::where('id', $imagemId)->update(['ordem' => $index + 1]);
            }
            
            return response()->json([
                'message' => 'Ordem das imagens atualizada com sucesso'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao reordenar imagens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Define uma imagem como principal.
     *
     * @param  int  $id
     * @param  int  $imagemId
     * @return \Illuminate\Http\Response
     */
    public function definirImagemPrincipal($id, $imagemId)
    {
        try {
            $imovel = Imovel::findOrFail($id);
            $imagem = ImovelImagem::where('imovel_id', $imovel->id)
                ->where('id', $imagemId)
                ->firstOrFail();
            
            // Definir como principal
            $imagem->definirComoPrincipal();
            
            return response()->json([
                'message' => 'Imagem definida como principal com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao definir imagem como principal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove uma imagem do imóvel.
     *
     * @param  int  $id
     * @param  int  $imagemId
     * @return \Illuminate\Http\Response
     */
    public function excluirImagem($id, $imagemId)
    {
        try {
            $imovel = Imovel::findOrFail($id);
            $imagem = ImovelImagem::where('imovel_id', $imovel->id)
                ->where('id', $imagemId)
                ->firstOrFail();
            
            // Excluir imagem (o evento deleting no modelo ImovelImagem cuidará de excluir o arquivo físico)
            $imagem->delete();
            
            return response()->json([
                'message' => 'Imagem excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir imagem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica se houve alteração nos preços e registra no histórico.
     *
     * @param  \App\Models\Imovel  $imovel
     * @return void
     */
    private function verificarAlteracaoPrecos($imovel)
    {
        // Verificar se o imóvel já existe no banco (tem ID)
        if (!$imovel->exists) {
            $this->registrarPrecoInicial($imovel);
            return;
        }
        
        // Buscar o imóvel original do banco para comparar
        $original = Imovel::find($imovel->id);
        
        // Verificar alteração no valor de venda
        if ($imovel->isDirty('valor_venda') && $imovel->valor_venda != $original->valor_venda) {
            $this->atualizarPrecoComHistorico($imovel, 'VENDA', $imovel->valor_venda, $original->valor_venda);
        }
        
        // Verificar alteração no valor de locação
        if ($imovel->isDirty('valor_locacao') && $imovel->valor_locacao != $original->valor_locacao) {
            $this->atualizarPrecoComHistorico($imovel, 'LOCACAO', $imovel->valor_locacao, $original->valor_locacao);
        }
        
        // Verificar alteração no valor de temporada
        if ($imovel->isDirty('valor_temporada') && $imovel->valor_temporada != $original->valor_temporada) {
            $this->atualizarPrecoComHistorico($imovel, 'TEMPORADA', $imovel->valor_temporada, $original->valor_temporada);
        }
    }

    /**
     * Registra o preço inicial do imóvel no histórico.
     *
     * @param  \App\Models\Imovel  $imovel
     * @return void
     */
    private function registrarPrecoInicial($imovel)
    {
        // Registrar preço de venda, se definido
        if ($imovel->valor_venda > 0) {
            $this->criarRegistroHistorico($imovel, 'VENDA', $imovel->valor_venda, 'Preço inicial', 'Registro do preço inicial de venda');
        }
        
        // Registrar preço de locação, se definido
        if ($imovel->valor_locacao > 0) {
            $this->criarRegistroHistorico($imovel, 'LOCACAO', $imovel->valor_locacao, 'Preço inicial', 'Registro do preço inicial de locação');
        }
        
        // Registrar preço de temporada, se definido
        if ($imovel->valor_temporada > 0) {
            $this->criarRegistroHistorico($imovel, 'TEMPORADA', $imovel->valor_temporada, 'Preço inicial', 'Registro do preço inicial de temporada');
        }
    }
    
    /**
     * Atualiza o preço do imóvel e registra no histórico.
     *
     * @param  \App\Models\Imovel  $imovel
     * @param  string  $tipoNegocio
     * @param  float  $novoValor
     * @param  float  $valorAnterior
     * @param  string  $motivo
     * @return void
     */
    private function atualizarPrecoComHistorico($imovel, $tipoNegocio, $novoValor, $valorAnterior, $motivo = 'Atualização via cadastro')
    {
        // Verificar se a diferença é significativa (2% ou mais)
        $registrarHistorico = true;
        
        if ($valorAnterior > 0) {
            $diferenca = abs(($novoValor - $valorAnterior) / $valorAnterior) * 100;
            $registrarHistorico = $diferenca >= 2; // Registrar apenas se a diferença for de 2% ou mais
        }
        
        // Se a diferença for significativa ou se for forçado o registro
        if ($registrarHistorico) {
            // Fechar o registro atual (se existir)
            $hoje = Carbon::today();
            $ontem = Carbon::yesterday();
            
            ImovelPrecoHistorico::where('imovel_id', $imovel->id)
                ->where('tipo_negocio', $tipoNegocio)
                ->whereNull('data_fim')
                ->update([
                    'data_fim' => $ontem,
                    'updated_by' => Auth::id()
                ]);
            
            // Criar novo registro
            $observacao = "Alteração de R$ " . number_format($valorAnterior, 2, ',', '.') . 
                         " para R$ " . number_format($novoValor, 2, ',', '.');
            
            $this->criarRegistroHistorico($imovel, $tipoNegocio, $novoValor, $motivo, $observacao);
        }
    }
    
    /**
     * Cria um novo registro no histórico de preços.
     *
     * @param  \App\Models\Imovel  $imovel
     * @param  string  $tipoNegocio
     * @param  float  $valor
     * @param  string  $motivo
     * @param  string|null  $observacao
     * @return \App\Models\ImovelPrecoHistorico
     */
    private function criarRegistroHistorico($imovel, $tipoNegocio, $valor, $motivo, $observacao = null)
    {
        $historico = new ImovelPrecoHistorico();
        $historico->fill([
            'imovel_id' => $imovel->id,
            'tipo_negocio' => $tipoNegocio,
            'valor' => $valor,
            'data_inicio' => Carbon::today(),
            'motivo' => $motivo,
            'observacao' => $observacao,
            'created_by' => Auth::id()
        ]);
        
        $historico->save();
        
        return $historico;
    }
    
    /**
     * Busca avançada de imóveis com múltiplos filtros.
     *
     * @param  \App\Http\Requests\ImovelSearchRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // Validar a requisição usando a classe ImovelSearchRequest
        $searchRequest = new ImovelSearchRequest();
        $searchRequest->replace($request->all());
        $searchRequest->validate();
        
        try {
            // Definir chave de cache baseada nos parâmetros da requisição
            $cacheKey = 'imoveis_search_' . md5(json_encode($request->all()));
            $cacheTtl = 30; // 30 minutos
            
            // Verificar se já existe no cache (apenas para consultas frequentes)
            if (!$request->has('requisitosImovel') && !$request->has('requisitosCondominio') && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            // Iniciar a query base
            $query = Imovel::query()
                ->select('imoveis.*')
                ->where('imoveis.status', 'ATIVO')
                ->where('imoveis.publicar_site', true);
            
            // Aplicar filtro de tipo/subtipo (sem parsing, usar o valor exato)
            if ($request->filled('tipoSubtipo')) {
                $query->where('imoveis.tipo', $request->tipoSubtipo);
            }
            
            // Aplicar filtro de transação (VENDA/ALUGUEL)
            if ($request->filled('transacao')) {
                if ($request->transacao === 'VENDA') {
                    $query->whereNotNull('imoveis.preco_venda');
                } elseif ($request->transacao === 'ALUGUEL') {
                    $query->whereNotNull('imoveis.preco_aluguel');
                }
            }
            
            // Aplicar filtro de UF
            if ($request->filled('uf')) {
                $query->where('imoveis.uf', $request->uf);
            }
            
            // Aplicar filtro de cidade
            if ($request->filled('cidade')) {
                $query->where('imoveis.cidade', $request->cidade);
            }
            
            // Aplicar filtro de bairros (múltiplos)
            if ($request->filled('bairros') && is_array($request->bairros)) {
                $query->whereIn('imoveis.bairro', $request->bairros);
            }
            
            // Aplicar filtro de perfil do imóvel
            if ($request->filled('perfilImovel')) {
                $query->where('imoveis.perfil', $request->perfilImovel);
            }
            
            // Aplicar filtros de características numéricas (mínimos)
            if ($request->filled('dormitorios')) {
                $query->where('imoveis.dormitorios', '>=', $request->dormitorios);
            }
            
            if ($request->filled('suites')) {
                $query->where('imoveis.suites', '>=', $request->suites);
            }
            
            if ($request->filled('garagens')) {
                $query->where('imoveis.garagens', '>=', $request->garagens);
            }
            
            // Aplicar filtro de situação (múltiplos)
            if ($request->filled('situacao') && is_array($request->situacao)) {
                $query->whereIn('imoveis.situacao', $request->situacao);
            }
            
            // Aplicar filtros de faixa de preço
            if ($request->filled('precoMin') || $request->filled('precoMax')) {
                // Determinar qual campo de preço usar com base na transação
                $campoPreco = $request->transacao === 'ALUGUEL' ? 'preco_aluguel' : 'preco_venda';
                
                if ($request->filled('precoMin')) {
                    $query->where("imoveis.{$campoPreco}", '>=', $request->precoMin);
                }
                
                if ($request->filled('precoMax')) {
                    $query->where("imoveis.{$campoPreco}", '<=', $request->precoMax);
                }
            }
            
            // Aplicar filtros de faixa de área
            if ($request->filled('areaMin')) {
                $query->where('imoveis.area_total', '>=', $request->areaMin);
            }
            
            if ($request->filled('areaMax')) {
                $query->where('imoveis.area_total', '<=', $request->areaMax);
            }
            
            // Aplicar filtros booleanos
            if ($request->filled('mobiliado')) {
                $query->where('imoveis.mobiliado', $request->mobiliado);
            }
            
            if ($request->filled('aceitaPermuta')) {
                $query->where('imoveis.aceita_permuta', $request->aceitaPermuta);
            }
            
            if ($request->filled('aceitaFinanciamento')) {
                $query->where('imoveis.aceita_financiamento', $request->aceitaFinanciamento);
            }
            
            // Aplicar filtro de características do imóvel (N:N)
            if ($request->filled('requisitosImovel') && is_array($request->requisitosImovel) && count($request->requisitosImovel) > 0) {
                foreach ($request->requisitosImovel as $caracteristicaId) {
                    $query->whereExists(function ($subquery) use ($caracteristicaId) {
                        $subquery->select(DB::raw(1))
                            ->from('imoveis_caracteristicas')
                            ->whereColumn('imoveis_caracteristicas.imovel_id', 'imoveis.id')
                            ->where('imoveis_caracteristicas.caracteristica_id', $caracteristicaId);
                    });
                }
            }
            
            // Aplicar filtro de características do condomínio (N:N)
            if ($request->filled('requisitosCondominio') && is_array($request->requisitosCondominio) && count($request->requisitosCondominio) > 0) {
                // Primeiro, garantir que o imóvel tenha um condomínio
                $query->whereNotNull('imoveis.condominio_id');
                
                foreach ($request->requisitosCondominio as $caracteristicaId) {
                    $query->whereExists(function ($subquery) use ($caracteristicaId) {
                        $subquery->select(DB::raw(1))
                            ->from('condominios_caracteristicas')
                            ->join('condominios', 'condominios.id', '=', 'condominios_caracteristicas.condominio_id')
                            ->whereColumn('condominios.id', 'imoveis.condominio_id')
                            ->where('condominios_caracteristicas.caracteristica_id', $caracteristicaId);
                    });
                }
            }
            
            // Aplicar ordenação
            $ordenacao = $request->input('ordenacao', 'recentes');
            switch ($ordenacao) {
                case 'preco_asc':
                    // Ordenar pelo preço apropriado conforme o tipo de transação
                    if ($request->transacao === 'ALUGUEL') {
                        $query->orderBy('imoveis.preco_aluguel', 'asc');
                    } else {
                        $query->orderBy('imoveis.preco_venda', 'asc');
                    }
                    break;
                    
                case 'preco_desc':
                    // Ordenar pelo preço apropriado conforme o tipo de transação
                    if ($request->transacao === 'ALUGUEL') {
                        $query->orderBy('imoveis.preco_aluguel', 'desc');
                    } else {
                        $query->orderBy('imoveis.preco_venda', 'desc');
                    }
                    break;
                    
                case 'area_asc':
                    $query->orderBy('imoveis.area_total', 'asc');
                    break;
                    
                case 'area_desc':
                    $query->orderBy('imoveis.area_total', 'desc');
                    break;
                    
                case 'recentes':
                default:
                    $query->orderBy('imoveis.created_at', 'desc');
                    break;
            }
            
            // Aplicar eager loading de relacionamentos necessários
            $includes = [];
            
            // Sempre incluir a imagem principal
            $includes[] = 'imagens';
            
            // Incluir outros relacionamentos se solicitado
            if ($request->filled('include')) {
                $requestedIncludes = explode(',', $request->include);
                $allowedIncludes = ['detalhes', 'caracteristicas', 'condominio'];
                
                foreach ($requestedIncludes as $include) {
                    if (in_array($include, $allowedIncludes)) {
                        $includes[] = $include;
                    }
                }
            }
            
            if (!empty($includes)) {
                $query->with($includes);
            }
            
            // Aplicar paginação
            $perPage = $request->input('per_page', 15);
            $imoveis = $query->paginate($perPage);
            
            // Criar a resposta com a coleção de imóveis
            $response = new ImovelCollection($imoveis);
            
            // Armazenar no cache (apenas para consultas sem filtros de características)
            if (!$request->has('requisitosImovel') && !$request->has('requisitosCondominio')) {
                Cache::put($cacheKey, $response, $cacheTtl);
            }
            
            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao realizar busca de imóveis',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
