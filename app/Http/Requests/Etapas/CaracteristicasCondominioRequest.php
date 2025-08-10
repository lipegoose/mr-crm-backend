<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CaracteristicasCondominioRequest
{
    /**
     * Valida os dados da requisição para a etapa de Características do Condomínio.
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
     * Regras de validação para a etapa de Características do Condomínio.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'condominio_id' => 'sometimes|nullable|integer|exists:condominios,id',
            'caracteristicas' => 'sometimes|array',
            'caracteristicas.*' => 'integer|exists:caracteristicas,id',
            'novas_caracteristicas' => 'sometimes|array',
            'novas_caracteristicas.*' => 'string|max:100',
        ];
        
        // Não há regras adicionais para modo não-rascunho nesta etapa
        
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
            'condominio_id.integer' => 'O ID do condomínio deve ser um número inteiro.',
            'condominio_id.exists' => 'O condomínio selecionado não existe.',
            'caracteristicas.array' => 'As características devem ser enviadas como uma lista.',
            'caracteristicas.*.integer' => 'O ID da característica deve ser um número inteiro.',
            'caracteristicas.*.exists' => 'Uma das características selecionadas não existe.',
            'novas_caracteristicas.array' => 'As novas características devem ser enviadas como uma lista.',
            'novas_caracteristicas.*.string' => 'O nome da nova característica deve ser um texto.',
            'novas_caracteristicas.*.max' => 'O nome da nova característica não pode ter mais de 100 caracteres.',
        ];
    }
}
