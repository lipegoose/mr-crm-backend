<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PrecoRequest
{
    /**
     * Valida os dados da requisição para a etapa de Preço.
     *
     * @param Request $request
     * @param bool $isRascunho
     * @return array
     * @throws ValidationException
     */
    public function validate(Request $request, bool $isRascunho = false)
    {
        $rules = $this->rules($isRascunho);
        $messages = $this->messages();
        
        $validator = Validator::make($request->all(), $rules, $messages);
        
        // Validação adicional para tipo de negócio
        $validator->after(function ($validator) use ($request) {
            $tipoNegocio = $request->input('tipo_negocio');
            
            // Se não estiver enviando o tipo de negócio, não validamos os preços
            // para permitir atualizações parciais
            if (!$tipoNegocio) {
                return;
            }
            
            // Verificar se pelo menos um preço foi informado de acordo com o tipo de negócio
            // Apenas se estiver enviando o campo de preço correspondente
            if ($tipoNegocio === 'VENDA' && $request->has('preco_venda') && empty($request->input('preco_venda'))) {
                $validator->errors()->add('preco_venda', 'O preço de venda é obrigatório para imóveis à venda.');
            }
            
            if ($tipoNegocio === 'ALUGUEL' && $request->has('preco_aluguel') && empty($request->input('preco_aluguel'))) {
                $validator->errors()->add('preco_aluguel', 'O preço de aluguel é obrigatório para imóveis para locação.');
            }
            
            if ($tipoNegocio === 'TEMPORADA' && $request->has('preco_temporada') && empty($request->input('preco_temporada'))) {
                $validator->errors()->add('preco_temporada', 'O preço de temporada é obrigatório para imóveis para temporada.');
            }
            
            if ($tipoNegocio === 'VENDA_ALUGUEL') {
                if ($request->has('preco_venda') && empty($request->input('preco_venda'))) {
                    $validator->errors()->add('preco_venda', 'O preço de venda é obrigatório para imóveis à venda e locação.');
                }
                if ($request->has('preco_aluguel') && empty($request->input('preco_aluguel'))) {
                    $validator->errors()->add('preco_aluguel', 'O preço de aluguel é obrigatório para imóveis à venda e locação.');
                }
            }
        });
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Regras de validação para a etapa de Preço.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        // Usando 'sometimes' para garantir que apenas os campos enviados sejam validados
        $rules = [
            'tipo_negocio' => 'sometimes|string|in:VENDA,ALUGUEL,TEMPORADA,VENDA_ALUGUEL',
            'preco_venda' => 'sometimes|nullable|numeric|min:0',
            'preco_aluguel' => 'sometimes|nullable|numeric|min:0',
            'preco_temporada' => 'sometimes|nullable|numeric|min:0',
            'mostrar_preco' => 'sometimes|boolean',
            'preco_alternativo' => 'sometimes|nullable|string|max:100',
            'preco_anterior' => 'sometimes|nullable|numeric|min:0',
            'mostrar_preco_anterior' => 'sometimes|boolean',
            'preco_iptu' => 'sometimes|nullable|numeric|min:0',
            'periodo_iptu' => 'sometimes|nullable|string|in:MENSAL,ANUAL,UNICO',
            'preco_condominio' => 'sometimes|nullable|numeric|min:0',
            'financiado' => 'sometimes|boolean',
            'aceita_financiamento' => 'sometimes|boolean',
            'minha_casa_minha_vida' => 'sometimes|boolean',
            'total_taxas' => 'sometimes|nullable|numeric|min:0',
            'descricao_taxas' => 'sometimes|nullable|string|max:500',
            'aceita_permuta' => 'sometimes|boolean',
            'motivo_alteracao' => 'sometimes|nullable|string|max:255',
        ];
        
        // Se não for rascunho e estiver tentando enviar todos os campos obrigatórios,
        // adiciona regras de obrigatoriedade apenas para os campos presentes na requisição
        if (!$isRascunho) {
            // Verificamos se os campos obrigatórios estão presentes na requisição
            $camposObrigatorios = ['tipo_negocio'];
            $todosObrigatoriosPresentes = true;
            
            foreach ($camposObrigatorios as $campo) {
                if (!request()->has($campo)) {
                    $todosObrigatoriosPresentes = false;
                    break;
                }
            }
            
            // Se todos os campos obrigatórios estiverem presentes, aplicamos as regras de obrigatoriedade
            if ($todosObrigatoriosPresentes) {
                $rules = array_merge($rules, [
                    'tipo_negocio' => 'required|string|in:VENDA,ALUGUEL,TEMPORADA,VENDA_ALUGUEL',
                ]);
            }
        }
        
        return $rules;
    }
    
    /**
     * Mensagens de erro personalizadas.
     *
     * @return array
     */
    protected function messages()
    {
        return [
            'tipo_negocio.required' => 'O tipo de negócio é obrigatório.',
            'tipo_negocio.in' => 'O tipo de negócio selecionado não é válido.',
            
            'preco_venda.numeric' => 'O preço de venda deve ser um valor numérico.',
            'preco_venda.min' => 'O preço de venda não pode ser negativo.',
            
            'preco_aluguel.numeric' => 'O preço de aluguel deve ser um valor numérico.',
            'preco_aluguel.min' => 'O preço de aluguel não pode ser negativo.',
            
            'preco_temporada.numeric' => 'O preço de temporada deve ser um valor numérico.',
            'preco_temporada.min' => 'O preço de temporada não pode ser negativo.',
            
            'preco_anterior.numeric' => 'O preço anterior deve ser um valor numérico.',
            'preco_anterior.min' => 'O preço anterior não pode ser negativo.',
            
            'preco_iptu.numeric' => 'O valor do IPTU deve ser um valor numérico.',
            'preco_iptu.min' => 'O valor do IPTU não pode ser negativo.',
            
            'periodo_iptu.in' => 'O período do IPTU deve ser mensal, anual ou único.',
            
            'preco_condominio.numeric' => 'O valor do condomínio deve ser um valor numérico.',
            'preco_condominio.min' => 'O valor do condomínio não pode ser negativo.',
            
            'total_taxas.numeric' => 'O total de taxas deve ser um valor numérico.',
            'total_taxas.min' => 'O total de taxas não pode ser negativo.',
            
            'descricao_taxas.max' => 'A descrição das taxas não pode ter mais de 500 caracteres.',
        ];
    }
}
