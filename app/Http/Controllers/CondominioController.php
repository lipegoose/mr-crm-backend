<?php

namespace App\Http\Controllers;

use App\Models\Condominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CondominioController extends Controller
{
    /**
     * Exibe uma listagem dos condomínios.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $condominios = Condominio::orderBy('nome')->paginate(15);
        return response()->json($condominios);
    }

    /**
     * Armazena um condomínio recém-criado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cep' => 'nullable|string|max:10',
            'uf' => 'nullable|string|max:2',
            'cidade' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $condominio = Condominio::create($request->all());
        return response()->json($condominio, 201);
    }

    /**
     * Exibe o condomínio especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $condominio = Condominio::findOrFail($id);
        return response()->json($condominio);
    }

    /**
     * Atualiza o condomínio especificado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $condominio = Condominio::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cep' => 'nullable|string|max:10',
            'uf' => 'nullable|string|max:2',
            'cidade' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $condominio->update($request->all());
        return response()->json($condominio);
    }

    /**
     * Remove o condomínio especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $condominio = Condominio::findOrFail($id);
        $condominio->delete();
        return response()->json(null, 204);
    }

    /**
     * Busca avançada de condomínios.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Condominio::query();

        if ($request->has('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        if ($request->has('cidade')) {
            $query->where('cidade', 'like', '%' . $request->cidade . '%');
        }

        if ($request->has('bairro')) {
            $query->where('bairro', 'like', '%' . $request->bairro . '%');
        }

        if ($request->has('uf')) {
            $query->where('uf', $request->uf);
        }

        return response()->json($query->orderBy('nome')->paginate(15));
    }

    /**
     * Lista condomínios para uso em campos select.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function listarParaSelect(Request $request)
    {
        $query = Condominio::query()
            ->orderBy('nome')
            ->select('id', 'nome', 'bairro', 'cidade', 'uf');
        
        // Filtro por termo de busca (nome, bairro ou cidade)
        if ($request->has('q') && !empty($request->q)) {
            $termo = $request->q;
            $query->where(function($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                  ->orWhere('bairro', 'like', "%{$termo}%")
                  ->orWhere('cidade', 'like', "%{$termo}%");
            });
        }
        
        // Filtros opcionais por localização
        if ($request->has('cidade') && !empty($request->cidade)) {
            $query->where('cidade', $request->cidade);
        }
        
        if ($request->has('bairro') && !empty($request->bairro)) {
            $query->where('bairro', $request->bairro);
        }
        
        $condominios = $query->get()->map(function ($condominio) {
            return [
                'value' => $condominio->id,
                'label' => $condominio->nome . ' - ' . $condominio->bairro . ', ' . $condominio->cidade . '/' . $condominio->uf
            ];
        });

        return response()->json($condominios);
    }
}
