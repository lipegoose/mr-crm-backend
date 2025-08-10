<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublicacaoRequest
{
    /**
     * Valida os dados da requisição para a etapa de Publicação.
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
     * Regras de validação para a etapa de Publicação.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'publicar_site' => 'sometimes|boolean',
            'destaque_site' => 'sometimes|boolean',
            'publicar_portais' => 'sometimes|boolean',
            'portais' => 'sometimes|array',
            'portais.*' => 'string|max:100',
            'publicar_redes_sociais' => 'sometimes|boolean',
            'redes_sociais' => 'sometimes|array',
            'redes_sociais.*' => 'string|max:100',
            'data_publicacao' => 'sometimes|nullable|date',
            'data_expiracao' => 'sometimes|nullable|date|after_or_equal:data_publicacao',
            'status' => 'sometimes|string|in:ATIVO,INATIVO,VENDIDO,ALUGADO,RESERVADO,CANCELADO',
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
            'portais.array' => 'Os portais devem ser enviados como uma lista.',
            'portais.*.max' => 'O nome do portal não pode ter mais de 100 caracteres.',
            
            'redes_sociais.array' => 'As redes sociais devem ser enviadas como uma lista.',
            'redes_sociais.*.max' => 'O nome da rede social não pode ter mais de 100 caracteres.',
            
            'data_publicacao.date' => 'A data de publicação deve ser uma data válida.',
            
            'data_expiracao.date' => 'A data de expiração deve ser uma data válida.',
            'data_expiracao.after_or_equal' => 'A data de expiração deve ser igual ou posterior à data de publicação.',
            
            'status.in' => 'O status selecionado não é válido.',
        ];
    }
}
