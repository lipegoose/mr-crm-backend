<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComodosRequest
{
    /**
     * Valida os dados da requisição para a etapa de Cômodos.
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
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Regras de validação para a etapa de Cômodos.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'dormitorios' => 'sometimes|nullable|integer|min:0|max:20',
            'suites' => 'sometimes|nullable|integer|min:0|max:20',
            'banheiros' => 'sometimes|nullable|integer|min:0|max:20',
            'garagens' => 'sometimes|nullable|integer|min:0|max:20',
            'garagem_coberta' => 'sometimes|boolean',
            'box_garagem' => 'sometimes|boolean',
            'sala_tv' => 'sometimes|nullable|integer|min:0|max:10',
            'sala_jantar' => 'sometimes|nullable|integer|min:0|max:10',
            'sala_estar' => 'sometimes|nullable|integer|min:0|max:10',
            'lavabo' => 'sometimes|nullable|integer|min:0|max:10',
            'area_servico' => 'sometimes|nullable|integer|min:0|max:10',
            'cozinha' => 'sometimes|nullable|integer|min:0|max:10',
            'closet' => 'sometimes|nullable|integer|min:0|max:10',
            'escritorio' => 'sometimes|nullable|integer|min:0|max:10',
            'dependencia_servico' => 'sometimes|nullable|integer|min:0|max:10',
            'copa' => 'sometimes|nullable|integer|min:0|max:10',
        ];
        
        // Se não for rascunho, adiciona regras de obrigatoriedade
        if (!$isRascunho) {
            $rules = array_merge($rules, [
                'dormitorios' => 'required|integer|min:0|max:20',
                'banheiros' => 'required|integer|min:0|max:20',
            ]);
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
            'dormitorios.required' => 'O número de dormitórios é obrigatório.',
            'dormitorios.integer' => 'O número de dormitórios deve ser um número inteiro.',
            'dormitorios.min' => 'O número de dormitórios não pode ser negativo.',
            'dormitorios.max' => 'O número de dormitórios não pode ser maior que 20.',
            
            'suites.integer' => 'O número de suítes deve ser um número inteiro.',
            'suites.min' => 'O número de suítes não pode ser negativo.',
            'suites.max' => 'O número de suítes não pode ser maior que 20.',
            
            'banheiros.required' => 'O número de banheiros é obrigatório.',
            'banheiros.integer' => 'O número de banheiros deve ser um número inteiro.',
            'banheiros.min' => 'O número de banheiros não pode ser negativo.',
            'banheiros.max' => 'O número de banheiros não pode ser maior que 20.',
            
            'garagens.integer' => 'O número de garagens deve ser um número inteiro.',
            'garagens.min' => 'O número de garagens não pode ser negativo.',
            'garagens.max' => 'O número de garagens não pode ser maior que 20.',
            
            'sala_tv.integer' => 'O número de salas de TV deve ser um número inteiro.',
            'sala_tv.min' => 'O número de salas de TV não pode ser negativo.',
            'sala_tv.max' => 'O número de salas de TV não pode ser maior que 10.',
            
            'sala_jantar.integer' => 'O número de salas de jantar deve ser um número inteiro.',
            'sala_jantar.min' => 'O número de salas de jantar não pode ser negativo.',
            'sala_jantar.max' => 'O número de salas de jantar não pode ser maior que 10.',
            
            'sala_estar.integer' => 'O número de salas de estar deve ser um número inteiro.',
            'sala_estar.min' => 'O número de salas de estar não pode ser negativo.',
            'sala_estar.max' => 'O número de salas de estar não pode ser maior que 10.',
            
            'lavabo.integer' => 'O número de lavabos deve ser um número inteiro.',
            'lavabo.min' => 'O número de lavabos não pode ser negativo.',
            'lavabo.max' => 'O número de lavabos não pode ser maior que 10.',
            
            'area_servico.integer' => 'O número de áreas de serviço deve ser um número inteiro.',
            'area_servico.min' => 'O número de áreas de serviço não pode ser negativo.',
            'area_servico.max' => 'O número de áreas de serviço não pode ser maior que 10.',
            
            'cozinha.integer' => 'O número de cozinhas deve ser um número inteiro.',
            'cozinha.min' => 'O número de cozinhas não pode ser negativo.',
            'cozinha.max' => 'O número de cozinhas não pode ser maior que 10.',
            
            'closet.integer' => 'O número de closets deve ser um número inteiro.',
            'closet.min' => 'O número de closets não pode ser negativo.',
            'closet.max' => 'O número de closets não pode ser maior que 10.',
            
            'escritorio.integer' => 'O número de escritórios deve ser um número inteiro.',
            'escritorio.min' => 'O número de escritórios não pode ser negativo.',
            'escritorio.max' => 'O número de escritórios não pode ser maior que 10.',
            
            'dependencia_servico.integer' => 'O número de dependências de serviço deve ser um número inteiro.',
            'dependencia_servico.min' => 'O número de dependências de serviço não pode ser negativo.',
            'dependencia_servico.max' => 'O número de dependências de serviço não pode ser maior que 10.',
            
            'copa.integer' => 'O número de copas deve ser um número inteiro.',
            'copa.min' => 'O número de copas não pode ser negativo.',
            'copa.max' => 'O número de copas não pode ser maior que 10.',
        ];
    }
}
