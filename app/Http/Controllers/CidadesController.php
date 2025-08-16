<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CidadesController extends Controller
{
    /**
     * Listar todas as cidades com opção de filtrar por UF
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Cidade::query();
        
        // Filtrar por UF se fornecido
        if ($request->has('uf')) {
            $query->porUf($request->uf);
        }
        
        // Filtrar por nome se fornecido
        if ($request->has('nome')) {
            $query->porNome($request->nome);
        }
        
        // Ordenar por nome
        $cidades = $query->ordenadoPorNome()->get();
        
        // Adicionar atributos para select
        $cidades = $cidades->map(function ($cidade) {
            return array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            );
        });
        
        return response()->json([
            'success' => true,
            'data' => $cidades
        ]);
    }

    /**
     * Listar cidades para uso em componentes select
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request)
    {
        $query = Cidade::query();
        
        // Filtrar por UF se fornecido
        if ($request->has('uf')) {
            $query->porUf($request->uf);
        }
        
        // Filtrar por nome se fornecido
        if ($request->has('nome')) {
            $query->porNome($request->nome);
        }
        
        $cidades = $query->ordenadoPorNome()->get();
        
        // Formatar para select
        $options = $cidades->map(function ($cidade) {
            return [
                'value' => (string) $cidade->id,
                'label' => $cidade->nome
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Listar cidades de uma UF específica
     *
     * @param string $uf
     * @return \Illuminate\Http\JsonResponse
     */
    public function porUf($uf)
    {
        $cidades = Cidade::porUf($uf)->ordenadoPorNome()->get();
        
        // Adicionar atributos para select
        $cidades = $cidades->map(function ($cidade) {
            return array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            );
        });
        
        return response()->json([
            'success' => true,
            'data' => $cidades
        ]);
    }

    /**
     * Listar cidades de uma UF específica para componente select
     *
     * @param string $uf
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectPorUf($uf)
    {
        $cidades = Cidade::porUf($uf)->ordenadoPorNome()->get();
        
        // Formatar para select
        $options = $cidades->map(function ($cidade) {
            return [
                'value' => (string) $cidade->id,
                'label' => $cidade->nome
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Buscar cidades por nome (busca parcial)
     *
     * @param string $nome
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function porNome($nome, Request $request)
    {
        $query = Cidade::porNome($nome);
        
        // Filtrar por UF se fornecido
        if ($request->has('uf')) {
            $query->porUf($request->uf);
        }
        
        $cidades = $query->ordenadoPorNome()->get();
        
        // Adicionar atributos para select
        $cidades = $cidades->map(function ($cidade) {
            return array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            );
        });
        
        return response()->json([
            'success' => true,
            'data' => $cidades
        ]);
    }

    /**
     * Salvar nova cidade
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar duplicidade
        $existente = Cidade::where('nome', ucwords(mb_strtolower($request->nome)))
            ->where('uf', strtoupper($request->uf))
            ->first();
            
        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe uma cidade com este nome nesta UF',
                'data' => array_merge(
                    $existente->toArray(),
                    [
                        'value' => (string) $existente->id,
                        'label' => $existente->nome
                    ]
                )
            ], 422);
        }
        
        $cidade = Cidade::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Cidade criada com sucesso',
            'data' => array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            )
        ], 201);
    }

    /**
     * Exibir detalhes de uma cidade específica
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cidade = Cidade::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            )
        ]);
    }

    /**
     * Atualizar cidade existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $cidade = Cidade::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'uf' => 'sometimes|required|string|size:2',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar duplicidade se nome ou UF foram alterados
        if (($request->has('nome') && $cidade->nome != $request->nome) || 
            ($request->has('uf') && $cidade->uf != $request->uf)) {
            
            $existente = Cidade::where('nome', ucwords(mb_strtolower($request->nome ?? $cidade->nome)))
                ->where('uf', strtoupper($request->uf ?? $cidade->uf))
                ->where('id', '!=', $id)
                ->first();
                
            if ($existente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma cidade com este nome nesta UF'
                ], 422);
            }
        }
        
        $cidade->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Cidade atualizada com sucesso',
            'data' => array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            )
        ]);
    }

    /**
     * Excluir cidade (soft delete)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $cidade = Cidade::findOrFail($id);
        
        // Verificar se há bairros associados
        if ($cidade->bairros()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir esta cidade pois existem bairros associados a ela'
            ], 422);
        }
        
        $cidade->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cidade excluída com sucesso'
        ]);
    }

    /**
     * Buscar cidade por nome e UF ou criar se não existir
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarOuCriar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar se já existe
        $cidade = Cidade::where('nome', ucwords(mb_strtolower($request->nome)))
            ->where('uf', strtoupper($request->uf))
            ->first();
            
        $isNew = false;
        
        // Se não existir, criar
        if (!$cidade) {
            $cidade = Cidade::create($request->all());
            $isNew = true;
        }
        
        return response()->json([
            'success' => true,
            'data' => array_merge(
                $cidade->toArray(),
                [
                    'value' => (string) $cidade->id,
                    'label' => $cidade->nome
                ]
            ),
            'message' => $isNew ? 'Cidade criada com sucesso' : 'Cidade encontrada',
            'is_new' => $isNew
        ]);
    }
}
