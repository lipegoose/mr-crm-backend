<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImovelRequest;
use App\Http\Resources\ImovelResource;
use App\Http\Resources\ImovelCollection;
use App\Models\Imovel;
use App\Models\ImovelDetalhe;
use App\Models\ImovelImagem;
use App\Models\ImovelPrecoHistorico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $imovel = Imovel::with([
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
            ])->findOrFail($id);
            
            return new ImovelResource($imovel);
        } catch (\Exception $e) {
            return response()->json([
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
            return;
        }
        
        // Buscar o imóvel original do banco para comparar
        $original = Imovel::find($imovel->id);
        
        // Verificar alteração no valor de venda
        if ($imovel->isDirty('valor_venda') && $imovel->valor_venda != $original->valor_venda) {
            $this->registrarHistoricoPreco($imovel, 'VENDA', $original->valor_venda, $imovel->valor_venda);
        }
        
        // Verificar alteração no valor de locação
        if ($imovel->isDirty('valor_locacao') && $imovel->valor_locacao != $original->valor_locacao) {
            $this->registrarHistoricoPreco($imovel, 'LOCACAO', $original->valor_locacao, $imovel->valor_locacao);
        }
    }

    /**
     * Registra um novo histórico de preço.
     *
     * @param  \App\Models\Imovel  $imovel
     * @param  string  $tipoNegocio
     * @param  float  $valorAntigo
     * @param  float  $valorNovo
     * @return void
     */
    private function registrarHistoricoPreco($imovel, $tipoNegocio, $valorAntigo, $valorNovo)
    {
        // Fechar o registro atual (se existir)
        ImovelPrecoHistorico::where('imovel_id', $imovel->id)
            ->where('tipo_negocio', $tipoNegocio)
            ->whereNull('data_fim')
            ->update([
                'data_fim' => now(),
                'updated_by' => Auth::id()
            ]);
        
        // Criar novo registro
        $historico = new ImovelPrecoHistorico();
        $historico->fill([
            'imovel_id' => $imovel->id,
            'tipo_negocio' => $tipoNegocio,
            'valor' => $valorNovo,
            'data_inicio' => now(),
            'motivo' => 'Atualização de preço',
            'observacao' => "Alteração de R$ " . number_format($valorAntigo, 2, ',', '.') . 
                           " para R$ " . number_format($valorNovo, 2, ',', '.'),
            'created_by' => Auth::id()
        ]);
        
        $historico->save();
    }
}
