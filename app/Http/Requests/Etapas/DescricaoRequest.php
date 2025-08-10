<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DescricaoRequest
{
    /**
     * Valida os dados da requisição para a etapa de Descrição.
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
     * Regras de validação para a etapa de Descrição.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'titulo_anuncio' => 'sometimes|nullable|string|max:200',
            'mostrar_titulo' => 'sometimes|boolean',
            'descricao' => 'sometimes|nullable|string|max:5000',
            'mostrar_descricao' => 'sometimes|boolean',
            'palavras_chave' => 'sometimes|nullable|string|max:500',
            'gerar_titulo_automatico' => 'sometimes|boolean',
            'gerar_descricao_automatica' => 'sometimes|boolean',
        ];
        
        // Se não for rascunho, adiciona regras de obrigatoriedade
        if (!$isRascunho) {
            $rules = array_merge($rules, [
                'titulo_anuncio' => 'required|string|max:200',
                'descricao' => 'required|string|max:5000',
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
            'titulo_anuncio.required' => 'O título do anúncio é obrigatório.',
            'titulo_anuncio.max' => 'O título do anúncio não pode ter mais de 200 caracteres.',
            
            'descricao.required' => 'A descrição do imóvel é obrigatória.',
            'descricao.max' => 'A descrição do imóvel não pode ter mais de 5000 caracteres.',
            
            'palavras_chave.max' => 'As palavras-chave não podem ter mais de 500 caracteres.',
        ];
    }
}
