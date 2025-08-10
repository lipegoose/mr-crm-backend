<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ImovelPrecoHistoricoRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'tipo_negocio' => 'required|string|in:VENDA,LOCACAO,TEMPORADA',
            'valor' => 'required|numeric|min:0',
            'data_inicio' => 'required|date|before_or_equal:today',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'motivo' => 'required|string|max:255',
            'observacao' => 'nullable|string|max:1000',
        ];

        // Se for uma atualização, verificar se o registro não está encerrado
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Regras adicionais para atualização
            $rules['data_inicio'] = 'sometimes|required|date|before_or_equal:today';
        }

        return $rules;
    }

    /**
     * Obtém as mensagens de erro personalizadas para as regras de validação.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'tipo_negocio.required' => 'O tipo de negócio é obrigatório.',
            'tipo_negocio.in' => 'O tipo de negócio deve ser VENDA, LOCACAO ou TEMPORADA.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser um número.',
            'valor.min' => 'O valor deve ser maior ou igual a zero.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'A data de início deve ser uma data válida.',
            'data_inicio.before_or_equal' => 'A data de início não pode ser futura.',
            'data_fim.date' => 'A data de fim deve ser uma data válida.',
            'data_fim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
            'motivo.required' => 'O motivo da alteração é obrigatório.',
            'motivo.max' => 'O motivo não pode ter mais de 255 caracteres.',
            'observacao.max' => 'A observação não pode ter mais de 1000 caracteres.',
        ];
    }

    /**
     * Prepara os dados para validação.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Converter datas para o formato correto
        if ($this->has('data_inicio')) {
            $this->merge([
                'data_inicio' => Carbon::parse($this->data_inicio)->startOfDay(),
            ]);
        }

        if ($this->has('data_fim')) {
            $this->merge([
                'data_fim' => Carbon::parse($this->data_fim)->endOfDay(),
            ]);
        }
    }
}
