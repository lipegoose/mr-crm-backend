<?php

namespace App\Http\Controllers;

use App\Models\Proximidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProximidadeController extends Controller
{
    /**
     * Lista proximidades com paginação
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        return response()->json(
            Proximidade::orderBy('created_at', 'desc')->orderBy('nome')->paginate($perPage)
        );
    }

    /**
     * Busca proximidades por nome
     */
    public function search(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $nome = $request->get('nome');

        $query = Proximidade::query();
        if ($nome) {
            $query->where('nome', 'like', '%' . $nome . '%');
        }

        return response()->json($query->orderBy('created_at', 'desc')->orderBy('nome')->paginate($perPage));
    }

    /**
     * Cria uma proximidade
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $proximidade = Proximidade::create([
            'nome' => $request->nome,
            'sistema' => $request->get('sistema', 0),
        ]);

        return response()->json($proximidade, 201);
    }

    /**
     * Detalhe por id
     */
    public function show($id)
    {
        $proximidade = Proximidade::findOrFail($id);
        return response()->json($proximidade);
    }

    /**
     * Atualiza parcialmente (bloqueia quando sistema=1)
     */
    public function update(Request $request, $id)
    {
        $proximidade = Proximidade::findOrFail($id);

        if ($proximidade->sistema) {
            return response()->json(['message' => 'Registro do sistema não pode ser alterado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $proximidade->update($request->only(['nome']));
        return response()->json($proximidade);
    }

    /**
     * Exclui (bloqueia quando sistema=1)
     */
    public function destroy($id)
    {
        $proximidade = Proximidade::findOrFail($id);

        if ($proximidade->sistema) {
            return response()->json(['message' => 'Registro do sistema não pode ser removido.'], 403);
        }

        $proximidade->delete();
        return response()->json(null, 204);
    }
}
