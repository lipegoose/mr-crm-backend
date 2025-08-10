<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImagensRequest
{
    /**
     * Valida os dados da requisição para a etapa de Imagens.
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
     * Regras de validação para a etapa de Imagens.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'imagens' => 'sometimes|array',
            'imagens.*.id' => 'sometimes|nullable|integer|exists:imoveis_imagens,id',
            'imagens.*.titulo' => 'sometimes|nullable|string|max:200',
            'imagens.*.caminho' => 'sometimes|nullable|string|max:500',
            'imagens.*.ordem' => 'sometimes|nullable|integer|min:0',
            'imagens.*.principal' => 'sometimes|boolean',
            'imagens_removidas' => 'sometimes|array',
            'imagens_removidas.*' => 'integer|exists:imoveis_imagens,id',
            'imagem_principal_id' => 'sometimes|nullable|integer|exists:imoveis_imagens,id',
        ];
        
        // Se não for rascunho, adiciona regras de obrigatoriedade
        if (!$isRascunho) {
            // Verificar se há pelo menos uma imagem
            $rules['imagens'] = 'required|array|min:1';
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
            'imagens.required' => 'É necessário adicionar pelo menos uma imagem ao imóvel.',
            'imagens.array' => 'As imagens devem ser enviadas como uma lista.',
            'imagens.min' => 'É necessário adicionar pelo menos uma imagem ao imóvel.',
            'imagens.*.id.integer' => 'O ID da imagem deve ser um número inteiro.',
            'imagens.*.id.exists' => 'Uma das imagens selecionadas não existe.',
            'imagens.*.titulo.max' => 'O título da imagem não pode ter mais de 200 caracteres.',
            'imagens.*.caminho.max' => 'O caminho da imagem não pode ter mais de 500 caracteres.',
            'imagens.*.ordem.integer' => 'A ordem da imagem deve ser um número inteiro.',
            'imagens.*.ordem.min' => 'A ordem da imagem não pode ser negativa.',
            
            'imagens_removidas.array' => 'As imagens removidas devem ser enviadas como uma lista.',
            'imagens_removidas.*.integer' => 'O ID da imagem removida deve ser um número inteiro.',
            'imagens_removidas.*.exists' => 'Uma das imagens removidas não existe.',
            
            'imagem_principal_id.integer' => 'O ID da imagem principal deve ser um número inteiro.',
            'imagem_principal_id.exists' => 'A imagem principal selecionada não existe.',
        ];
    }
}
