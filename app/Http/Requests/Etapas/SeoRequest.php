<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SeoRequest
{
    /**
     * Valida os dados da requisição para a etapa de SEO.
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
     * Regras de validação para a etapa de SEO.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'seo_title' => 'sometimes|nullable|string|max:70',
            'seo_description' => 'sometimes|nullable|string|max:160',
            'seo_keywords' => 'sometimes|nullable|string|max:255',
            'url_amigavel' => 'sometimes|nullable|string|max:255|regex:/^[a-z0-9\-]+$/',
            'gerar_url_automatica' => 'sometimes|boolean',
            'gerar_seo_automatico' => 'sometimes|boolean',
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
            'seo_title.max' => 'O título SEO não pode ter mais de 70 caracteres.',
            
            'seo_description.max' => 'A descrição SEO não pode ter mais de 160 caracteres.',
            
            'seo_keywords.max' => 'As palavras-chave SEO não podem ter mais de 255 caracteres.',
            
            'url_amigavel.max' => 'A URL amigável não pode ter mais de 255 caracteres.',
            'url_amigavel.regex' => 'A URL amigável deve conter apenas letras minúsculas, números e hífens.',
        ];
    }
}
