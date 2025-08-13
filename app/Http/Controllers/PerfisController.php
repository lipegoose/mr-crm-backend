<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfisController extends Controller
{
    /**
     * Exibe uma listagem dos perfis.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perfis = Perfil::orderBy('label')->paginate(15);
        return response()->json($perfis);
    }

    /**
     * Armazena um perfil recém-criado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:50|unique:perfis',
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perfil = Perfil::create($request->all());
        return response()->json($perfil, 201);
    }

    /**
     * Exibe o perfil especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $perfil = Perfil::findOrFail($id);
        return response()->json($perfil);
    }

    /**
     * Atualiza o perfil especificado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $perfil = Perfil::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:50|unique:perfis,value,'.$id,
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perfil->update($request->all());
        return response()->json($perfil);
    }

    /**
     * Remove o perfil especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $perfil = Perfil::findOrFail($id);
        $perfil->delete();
        return response()->json(null, 204);
    }

    /**
     * Lista perfis para uso em campos select.
     * 
     * @return \Illuminate\Http\Response
     */
    public function listarParaSelect()
    {
        $perfis = Perfil::orderBy('id')
            ->select('value', 'label')
            ->get()
            ->toArray();

        return response()->json($perfis);
    }
}
