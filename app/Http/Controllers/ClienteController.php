<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Exibe uma listagem dos clientes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clientes = Cliente::orderBy('nome')->paginate(15);
        return response()->json($clientes);
    }

    /**
     * Armazena um cliente recém-criado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|in:PESSOA_FISICA,PESSOA_JURIDICA',
            'cpf_cnpj' => 'required|string|max:20|unique:clientes',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'status' => 'required|string|in:ATIVO,INATIVO',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente = Cliente::create($request->all());
        return response()->json($cliente, 201);
    }

    /**
     * Exibe o cliente especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
    }

    /**
     * Atualiza o cliente especificado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|in:PESSOA_FISICA,PESSOA_JURIDICA',
            'cpf_cnpj' => 'required|string|max:20|unique:clientes,cpf_cnpj,'.$id,
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'status' => 'required|string|in:ATIVO,INATIVO',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente->update($request->all());
        return response()->json($cliente);
    }

    /**
     * Remove o cliente especificado.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return response()->json(null, 204);
    }

    /**
     * Busca avançada de clientes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Cliente::query();

        if ($request->has('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('cpf_cnpj')) {
            $query->where('cpf_cnpj', 'like', '%' . $request->cpf_cnpj . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('nome')->paginate(15));
    }

    /**
     * Lista clientes para uso em campos select de proprietários.
     * 
     * @return \Illuminate\Http\Response
     */
    public function listarParaSelect()
    {
        $clientes = Cliente::where('status', 'ATIVO')
            ->orderBy('nome')
            ->select('id', 'nome', 'tipo', 'cpf_cnpj')
            ->get()
            ->map(function ($cliente) {
                // Formata o nome com CPF/CNPJ para melhor identificação
                $documento = $cliente->cpf_cnpj ? " ({$cliente->cpf_cnpj})" : "";
                return [
                    'value' => $cliente->id,
                    'label' => $cliente->nome . $documento
                ];
            });

        return response()->json($clientes);
    }
}
