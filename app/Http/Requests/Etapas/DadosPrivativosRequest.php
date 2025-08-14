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
        // Usando 'sometimes' para garantir que apenas os campos enviados sejam validados
        $rules = [
            'matricula' => 'sometimes|nullable|string|max:100',
            'inscricao_municipal' => 'sometimes|nullable|string|max:100',
            'inscricao_estadual' => 'sometimes|nullable|string|max:100',
            'valor_comissao' => 'sometimes|nullable|numeric|min:0',
            'tipo_comissao' => ['sometimes', 'nullable', Rule::in(['PORCENTAGEM', 'VALOR'])],
            'exclusividade' => 'sometimes|boolean',
            'data_inicio_exclusividade' => 'sometimes|nullable|date',
            'data_fim_exclusividade' => 'sometimes|nullable|date|after_or_equal:data_inicio_exclusividade',
            'observacoes_privadas' => 'sometimes|nullable|string',
            'corretor_id' => 'sometimes|nullable|exists:users,id',
        ];

        // Se não for rascunho e tiver exclusividade, as datas são obrigatórias
        // Mas apenas se esses campos estiverem presentes na requisição
        if (!$isRascunho && $request->has('exclusividade') && $request->exclusividade) {
            if ($request->has('data_inicio_exclusividade')) {
                $rules['data_inicio_exclusividade'] = 'required|date';
            }
            
            if ($request->has('data_fim_exclusividade')) {
                $rules['data_fim_exclusividade'] = 'required|date|after_or_equal:data_inicio_exclusividade';
            }
        }

        return validator($request->all(), $rules)->validate();
    }
}
