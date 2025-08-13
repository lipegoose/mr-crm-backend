<?php

namespace App\Http\Controllers;

use App\Models\PosicaoSolar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosicoesController extends Controller
{
    /**
     * Exibe uma listagem das posições solares.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posicoes = PosicaoSolar::orderBy('label')->paginate(15);
        return response()->json($posicoes);
    }

    /**
     * Armazena uma posição solar recém-criada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:50|unique:posicoes_solares',
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $posicao = PosicaoSolar::create($request->all());
        return response()->json($posicao, 201);
    }

    /**
     * Exibe a posição solar especificada.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $posicao = PosicaoSolar::findOrFail($id);
        return response()->json($posicao);
    }

    /**
     * Atualiza a posição solar especificada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $posicao = PosicaoSolar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:50|unique:posicoes_solares,value,'.$id,
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $posicao->update($request->all());
        return response()->json($posicao);
    }

    /**
     * Remove a posição solar especificada.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $posicao = PosicaoSolar::findOrFail($id);
        $posicao->delete();
        return response()->json(null, 204);
    }

    /**
     * Lista posições solares para uso em campos select.
     * 
     * @return \Illuminate\Http\Response
     */
    public function listarParaSelect()
    {
        $posicoes = PosicaoSolar::orderBy('id')
            ->select('value', 'label')
            ->get()
            ->toArray();

        return response()->json($posicoes);
    }
}
