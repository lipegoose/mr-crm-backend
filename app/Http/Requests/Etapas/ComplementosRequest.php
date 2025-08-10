<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComplementosRequest
{
    /**
     * Valida os dados da requisição para a etapa de Complementos.
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
     * Regras de validação para a etapa de Complementos.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'observacoes_internas' => 'sometimes|nullable|string|max:5000',
            'tour_virtual_url' => 'sometimes|nullable|string|max:500|url',
            'videos' => 'sometimes|array',
            'videos.*.id' => 'sometimes|nullable|integer|exists:imoveis_videos,id',
            'videos.*.titulo' => 'required|string|max:200',
            'videos.*.url' => 'required|string|max:500|url',
            'videos.*.ordem' => 'sometimes|nullable|integer|min:0',
            'videos_removidos' => 'sometimes|array',
            'videos_removidos.*' => 'integer|exists:imoveis_videos,id',
            'plantas' => 'sometimes|array',
            'plantas.*.id' => 'sometimes|nullable|integer|exists:imoveis_plantas,id',
            'plantas.*.titulo' => 'required|string|max:200',
            'plantas.*.caminho' => 'sometimes|nullable|string|max:500',
            'plantas.*.ordem' => 'sometimes|nullable|integer|min:0',
            'plantas_removidas' => 'sometimes|array',
            'plantas_removidas.*' => 'integer|exists:imoveis_plantas,id',
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
            'observacoes_internas.max' => 'As observações internas não podem ter mais de 5000 caracteres.',
            
            'tour_virtual_url.max' => 'A URL do tour virtual não pode ter mais de 500 caracteres.',
            'tour_virtual_url.url' => 'A URL do tour virtual deve ser uma URL válida.',
            
            'videos.array' => 'Os vídeos devem ser enviados como uma lista.',
            'videos.*.id.integer' => 'O ID do vídeo deve ser um número inteiro.',
            'videos.*.id.exists' => 'Um dos vídeos selecionados não existe.',
            'videos.*.titulo.required' => 'O título do vídeo é obrigatório.',
            'videos.*.titulo.max' => 'O título do vídeo não pode ter mais de 200 caracteres.',
            'videos.*.url.required' => 'A URL do vídeo é obrigatória.',
            'videos.*.url.max' => 'A URL do vídeo não pode ter mais de 500 caracteres.',
            'videos.*.url.url' => 'A URL do vídeo deve ser uma URL válida.',
            'videos.*.ordem.integer' => 'A ordem do vídeo deve ser um número inteiro.',
            'videos.*.ordem.min' => 'A ordem do vídeo não pode ser negativa.',
            
            'videos_removidos.array' => 'Os vídeos removidos devem ser enviados como uma lista.',
            'videos_removidos.*.integer' => 'O ID do vídeo removido deve ser um número inteiro.',
            'videos_removidos.*.exists' => 'Um dos vídeos removidos não existe.',
            
            'plantas.array' => 'As plantas devem ser enviadas como uma lista.',
            'plantas.*.id.integer' => 'O ID da planta deve ser um número inteiro.',
            'plantas.*.id.exists' => 'Uma das plantas selecionadas não existe.',
            'plantas.*.titulo.required' => 'O título da planta é obrigatório.',
            'plantas.*.titulo.max' => 'O título da planta não pode ter mais de 200 caracteres.',
            'plantas.*.caminho.max' => 'O caminho da planta não pode ter mais de 500 caracteres.',
            'plantas.*.ordem.integer' => 'A ordem da planta deve ser um número inteiro.',
            'plantas.*.ordem.min' => 'A ordem da planta não pode ser negativa.',
            
            'plantas_removidas.array' => 'As plantas removidas devem ser enviadas como uma lista.',
            'plantas_removidas.*.integer' => 'O ID da planta removida deve ser um número inteiro.',
            'plantas_removidas.*.exists' => 'Uma das plantas removidas não existe.',
        ];
    }
}
