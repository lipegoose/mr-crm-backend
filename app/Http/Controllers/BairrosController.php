<?php

namespace App\Http\Controllers;

use App\Models\Bairro;
use App\Models\Cidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BairrosController extends Controller
{
    /**
     * Listar todos os bairros com opção de filtrar por cidade
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Bairro::with('cidade');
        
        // Filtrar por cidade se fornecido
        if ($request->has('cidade_id')) {
            $query->porCidade($request->cidade_id);
        }
        
        // Filtrar por nome se fornecido
        if ($request->has('nome')) {
            $query->porNome($request->nome);
        }
        
        // Ordenar por nome
        $bairros = $query->ordenadoPorNome()->get();
        
        // Adicionar atributos para select
        $bairros = $bairros->map(function ($bairro) {
            return array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            );
        });
        
        return response()->json([
            'success' => true,
            'data' => $bairros
        ]);
    }

    /**
     * Listar bairros para uso em componentes select
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request)
    {
        $query = Bairro::query();
        
        // Filtrar por cidade se fornecido
        if ($request->has('cidade_id')) {
            $query->porCidade($request->cidade_id);
        }
        
        // Filtrar por nome se fornecido
        if ($request->has('nome')) {
            $query->porNome($request->nome);
        }
        
        $bairros = $query->ordenadoPorNome()->get();
        
        // Formatar para select
        $options = $bairros->map(function ($bairro) {
            return [
                'value' => (string) $bairro->id,
                'label' => $bairro->nome
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Listar bairros de uma cidade específica
     *
     * @param int $cidadeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function porCidade($cidadeId)
    {
        // Verificar se a cidade existe
        $cidade = Cidade::findOrFail($cidadeId);
        
        $bairros = Bairro::with('cidade')
            ->porCidade($cidadeId)
            ->ordenadoPorNome()
            ->get();
        
        // Adicionar atributos para select
        $bairros = $bairros->map(function ($bairro) {
            return array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            );
        });
        
        return response()->json([
            'success' => true,
            'data' => $bairros
        ]);
    }

    /**
     * Listar bairros de uma cidade específica para componente select
     *
     * @param int $cidadeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectPorCidade($cidadeId)
    {
        // Verificar se a cidade existe
        $cidade = Cidade::findOrFail($cidadeId);
        
        $bairros = Bairro::porCidade($cidadeId)
            ->ordenadoPorNome()
            ->get();
        
        // Formatar para select
        $options = $bairros->map(function ($bairro) {
            return [
                'value' => (string) $bairro->id,
                'label' => $bairro->nome
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Buscar bairros por nome (busca parcial)
     *
     * @param string $nome
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function porNome($nome, Request $request)
    {
        $query = Bairro::with('cidade')->porNome($nome);
        
        // Filtrar por cidade se fornecido
        if ($request->has('cidade_id')) {
            $query->porCidade($request->cidade_id);
        }
        
        $bairros = $query->ordenadoPorNome()->get();
        
        // Adicionar atributos para select
        $bairros = $bairros->map(function ($bairro) {
            return array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            );
        });
        
        return response()->json([
            'success' => true,
            'data' => $bairros
        ]);
    }

    /**
     * Salvar novo bairro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cidade_id' => 'required|exists:cidades,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar duplicidade
        $existente = Bairro::where('nome', ucwords(mb_strtolower($request->nome)))
            ->where('cidade_id', $request->cidade_id)
            ->first();
            
        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um bairro com este nome nesta cidade',
                'data' => array_merge(
                    $existente->toArray(),
                    [
                        'value' => (string) $existente->id,
                        'label' => $existente->nome
                    ]
                )
            ], 422);
        }
        
        $bairro = Bairro::create($request->all());
        $bairro->load('cidade');
        
        return response()->json([
            'success' => true,
            'message' => 'Bairro criado com sucesso',
            'data' => array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            )
        ], 201);
    }

    /**
     * Exibir detalhes de um bairro específico
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $bairro = Bairro::with('cidade')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            )
        ]);
    }

    /**
     * Atualizar bairro existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $bairro = Bairro::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'cidade_id' => 'sometimes|required|exists:cidades,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar duplicidade se nome ou cidade_id foram alterados
        if (($request->has('nome') && $bairro->nome != $request->nome) || 
            ($request->has('cidade_id') && $bairro->cidade_id != $request->cidade_id)) {
            
            $existente = Bairro::where('nome', ucwords(mb_strtolower($request->nome ?? $bairro->nome)))
                ->where('cidade_id', $request->cidade_id ?? $bairro->cidade_id)
                ->where('id', '!=', $id)
                ->first();
                
            if ($existente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um bairro com este nome nesta cidade'
                ], 422);
            }
        }
        
        $bairro->update($request->all());
        $bairro->load('cidade');
        
        return response()->json([
            'success' => true,
            'message' => 'Bairro atualizado com sucesso',
            'data' => array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            )
        ]);
    }

    /**
     * Excluir bairro (soft delete)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $bairro = Bairro::findOrFail($id);
        $bairro->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Bairro excluído com sucesso'
        ]);
    }

    /**
     * Buscar bairro por nome e cidade_id ou criar se não existir
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarOuCriar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cidade_id' => 'required|exists:cidades,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar se já existe
        $bairro = Bairro::where('nome', ucwords(mb_strtolower($request->nome)))
            ->where('cidade_id', $request->cidade_id)
            ->first();
            
        $isNew = false;
        
        // Se não existir, criar
        if (!$bairro) {
            $bairro = Bairro::create($request->all());
            $isNew = true;
        }
        
        $bairro->load('cidade');
        
        return response()->json([
            'success' => true,
            'data' => array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            ),
            'message' => $isNew ? 'Bairro criado com sucesso' : 'Bairro encontrado',
            'is_new' => $isNew
        ]);
    }
    
    /**
     * Buscar bairro por nome, cidade e UF ou criar se não existir
     * Útil quando não se tem o ID da cidade, apenas o nome e UF
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarOuCriarPorCidadeUf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cidade_nome' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $resultado = Bairro::buscarOuCriarPorCidadeUf(
            $request->nome,
            $request->cidade_nome,
            $request->uf
        );
        
        $bairro = $resultado['bairro'];
        $isNew = $resultado['is_new'];
        
        $bairro->load('cidade');
        
        return response()->json([
            'success' => true,
            'data' => array_merge(
                $bairro->toArray(),
                [
                    'value' => (string) $bairro->id,
                    'label' => $bairro->nome
                ]
            ),
            'message' => $isNew ? 'Bairro criado com sucesso' : 'Bairro encontrado',
            'is_new' => $isNew
        ]);
    }
}
