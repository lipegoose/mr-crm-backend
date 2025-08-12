<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DadosPrivativosRequest
{
    /**
     * Valida os dados da requisição para a etapa Dados Privativos.
     *
     * @param \Illuminate\Http\Request $request
     * @param bool $isRascunho
     * @return array
     */
    public function validate(Request $request, $isRascunho = false)
    {
        $rules = [
            'matricula' => 'nullable|string|max:100',
            'inscricao_municipal' => 'nullable|string|max:100',
            'inscricao_estadual' => 'nullable|string|max:100',
            'valor_comissao' => 'nullable|numeric|min:0',
            'tipo_comissao' => ['nullable', Rule::in(['PORCENTAGEM', 'VALOR'])],
            'exclusividade' => 'boolean',
            'data_inicio_exclusividade' => 'nullable|date',
            'data_fim_exclusividade' => 'nullable|date|after_or_equal:data_inicio_exclusividade',
            'observacoes_privadas' => 'nullable|string',
            'corretor_id' => 'nullable|exists:users,id',
        ];

        // Se não for rascunho e tiver exclusividade, as datas são obrigatórias
        if (!$isRascunho && $request->has('exclusividade') && $request->exclusividade) {
            $rules['data_inicio_exclusividade'] = 'required|date';
            $rules['data_fim_exclusividade'] = 'required|date|after_or_equal:data_inicio_exclusividade';
        }

        return validator($request->all(), $rules)->validate();
    }
}
