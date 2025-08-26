<?php

namespace App\Http\Controllers;

use App\Models\Caracteristica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaracteristicaController extends Controller
{
    /**
     * Lista características com paginação e filtro opcional por escopo.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $escopo = $request->get('escopo');

        $query = Caracteristica::query();
        if ($escopo) {
            $query->where('escopo', strtoupper($escopo));
        }

        return response()->json($query->orderBy('created_at', 'desc')->orderBy('nome')->paginate($perPage));
    }

    /**
     * Busca características por nome e escopo, com paginação.
     */
    public function search(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $nome = $request->get('nome');
        $escopo = $request->get('escopo');

        $query = Caracteristica::query();

        if ($nome) {
            $query->where('nome', 'like', '%' . $nome . '%');
        }
        if ($escopo) {
            $query->where('escopo', strtoupper($escopo));
        }

        return response()->json($query->orderBy('created_at', 'desc')->orderBy('nome')->paginate($perPage));
    }

    /**
     * Cria uma característica.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'escopo' => 'required|string|in:IMOVEL,CONDOMINIO',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $caracteristica = Caracteristica::create([
            'nome' => $request->nome,
            'escopo' => strtoupper($request->escopo),
            'sistema' => $request->get('sistema', 0),
        ]);

        return response()->json($caracteristica, 201);
    }

    /**
     * Detalhe por id.
     */
    public function show($id)
    {
        $caracteristica = Caracteristica::findOrFail($id);
        return response()->json($caracteristica);
    }

    /**
     * Atualiza parcialmente.
     * Bloqueia alteração quando sistema=1.
     */
    public function update(Request $request, $id)
    {
        $caracteristica = Caracteristica::findOrFail($id);

        if ($caracteristica->sistema) {
            return response()->json(['message' => 'Registro do sistema não pode ser alterado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            // escopo não é alterável aqui para evitar mudanças de domínio
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $caracteristica->update($request->only(['nome']));
        return response()->json($caracteristica);
    }

    /**
     * Remove uma característica (bloqueia quando sistema=1).
     */
    public function destroy($id)
    {
        $caracteristica = Caracteristica::findOrFail($id);

        if ($caracteristica->sistema) {
            return response()->json(['message' => 'Registro do sistema não pode ser removido.'], 403);
        }

        $caracteristica->delete();
        return response()->json(null, 204);
    }
}
