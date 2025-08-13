<?php

namespace App\Http\Controllers;

use App\Models\Situacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SituacoesController extends Controller
{
    /**
     * Exibe uma listagem das situações.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $situacoes = Situacao::orderBy('label')->paginate(15);
        return response()->json($situacoes);
    }

    /**
     * Armazena uma situação recém-criada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:50|unique:situacoes',
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $situacao = Situacao::create($request->all());
        return response()->json($situacao, 201);
    }

    /**
     * Exibe a situação especificada.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $situacao = Situacao::findOrFail($id);
        return response()->json($situacao);
    }

    /**
     * Atualiza a situação especificada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $situacao = Situacao::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:50|unique:situacoes,value,'.$id,
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $situacao->update($request->all());
        return response()->json($situacao);
    }

    /**
     * Remove a situação especificada.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $situacao = Situacao::findOrFail($id);
        $situacao->delete();
        return response()->json(null, 204);
    }

    /**
     * Lista situações para uso em campos select.
     * 
     * @return \Illuminate\Http\Response
     */
    public function listarParaSelect()
    {
        $situacoes = Situacao::orderBy('id')
            ->select('value', 'label')
            ->get()
            ->toArray();

        return response()->json($situacoes);
    }
}
