<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LocalizacaoRequest
{
    /**
     * Valida os dados da requisição para a etapa de Localização.
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
     * Regras de validação para a etapa de Localização.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'cep' => 'sometimes|nullable|string|size:8',
            'uf' => 'sometimes|nullable|string|size:2',
            'cidade' => 'sometimes|nullable|string|max:100',
            'bairro' => 'sometimes|nullable|string|max:100',
            'cidade_id' => 'sometimes|nullable|integer|exists:cidades,id',
            'bairro_id' => 'sometimes|nullable|integer|exists:bairros,id',
            'logradouro' => 'sometimes|nullable|string|max:200',
            'numero' => 'sometimes|nullable|string|max:20',
            'complemento' => 'sometimes|nullable|string|max:100',
            'mostrar_endereco' => 'sometimes|boolean',
            'mostrar_numero' => 'sometimes|boolean',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
        ];
        
        // Se não for rascunho e estiver tentando enviar todos os campos obrigatórios,
        // adiciona regras de obrigatoriedade apenas para os campos presentes na requisição
        if (!$isRascunho) {
            // Verificamos se os campos obrigatórios estão presentes na requisição
            $camposObrigatorios = ['uf', 'cidade', 'bairro'];
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
                    'uf' => 'required|string|size:2',
                    'cidade' => 'required|string|max:100',
                    'bairro' => 'required|string|max:100',
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
            'cep.size' => 'O CEP deve ter 8 dígitos, sem traço ou ponto.',
            
            'uf.required' => 'O estado (UF) é obrigatório.',
            'uf.size' => 'O estado (UF) deve ter 2 caracteres.',
            
            'cidade.required' => 'A cidade é obrigatória.',
            'cidade.max' => 'A cidade não pode ter mais de 100 caracteres.',
            
            'bairro.required' => 'O bairro é obrigatório.',
            'bairro.max' => 'O bairro não pode ter mais de 100 caracteres.',
            
            'cidade_id.integer' => 'O ID da cidade deve ser um número inteiro.',
            'cidade_id.exists' => 'A cidade selecionada não existe no sistema.',
            
            'bairro_id.integer' => 'O ID do bairro deve ser um número inteiro.',
            'bairro_id.exists' => 'O bairro selecionado não existe no sistema.',
            
            'logradouro.max' => 'O logradouro não pode ter mais de 200 caracteres.',
            
            'numero.max' => 'O número não pode ter mais de 20 caracteres.',
            
            'complemento.max' => 'O complemento não pode ter mais de 100 caracteres.',
            
            'latitude.numeric' => 'A latitude deve ser um valor numérico.',
            'latitude.between' => 'A latitude deve estar entre -90 e 90.',
            
            'longitude.numeric' => 'A longitude deve ser um valor numérico.',
            'longitude.between' => 'A longitude deve estar entre -180 e 180.',
        ];
    }
}
