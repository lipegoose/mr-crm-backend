<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProximidadesRequest
{
    /**
     * Valida os dados da requisição para a etapa de Proximidades.
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
     * Regras de validação para a etapa de Proximidades.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'proximidades' => 'sometimes|array',
            'proximidades.*.id' => 'required|integer|exists:proximidades,id',
            'proximidades.*.distancia_texto' => 'required|string|max:50',
            'proximidades.*.distancia_metros' => 'sometimes|nullable|integer|min:0',
            'novas_proximidades' => 'sometimes|array',
            'novas_proximidades.*.nome' => 'required|string|max:100',
            'novas_proximidades.*.distancia_texto' => 'required|string|max:50',
            'novas_proximidades.*.distancia_metros' => 'sometimes|nullable|integer|min:0',
            'mostrar_proximidades' => 'sometimes|boolean',
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
            'proximidades.array' => 'As proximidades devem ser enviadas como uma lista.',
            'proximidades.*.id.required' => 'O ID da proximidade é obrigatório.',
            'proximidades.*.id.integer' => 'O ID da proximidade deve ser um número inteiro.',
            'proximidades.*.id.exists' => 'Uma das proximidades selecionadas não existe.',
            'proximidades.*.distancia_texto.required' => 'A distância textual é obrigatória.',
            'proximidades.*.distancia_texto.max' => 'A distância textual não pode ter mais de 50 caracteres.',
            'proximidades.*.distancia_metros.integer' => 'A distância em metros deve ser um número inteiro.',
            'proximidades.*.distancia_metros.min' => 'A distância em metros não pode ser negativa.',
            
            'novas_proximidades.array' => 'As novas proximidades devem ser enviadas como uma lista.',
            'novas_proximidades.*.nome.required' => 'O nome da nova proximidade é obrigatório.',
            'novas_proximidades.*.nome.max' => 'O nome da nova proximidade não pode ter mais de 100 caracteres.',
            'novas_proximidades.*.distancia_texto.required' => 'A distância textual é obrigatória.',
            'novas_proximidades.*.distancia_texto.max' => 'A distância textual não pode ter mais de 50 caracteres.',
            'novas_proximidades.*.distancia_metros.integer' => 'A distância em metros deve ser um número inteiro.',
            'novas_proximidades.*.distancia_metros.min' => 'A distância em metros não pode ser negativa.',
        ];
    }
}
