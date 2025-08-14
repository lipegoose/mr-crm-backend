<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MedidasRequest
{
    /**
     * Valida os dados da requisição para a etapa de Medidas.
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
        
        // Validação adicional para consistência entre áreas
        $validator->after(function ($validator) use ($request) {
            // Verificar se área total >= área privativa >= área construída
            $areaTotal = $request->input('area_total');
            $areaPrivativa = $request->input('area_privativa');
            $areaConstruida = $request->input('area_construida');
            
            if ($areaTotal && $areaPrivativa && $areaTotal < $areaPrivativa) {
                $validator->errors()->add('area_total', 'A área total deve ser maior ou igual à área privativa.');
            }
            
            if ($areaPrivativa && $areaConstruida && $areaPrivativa < $areaConstruida) {
                $validator->errors()->add('area_privativa', 'A área privativa deve ser maior ou igual à área construída.');
            }
        });
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Regras de validação para a etapa de Medidas.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        // Usando 'sometimes' para garantir que apenas os campos enviados sejam validados
        $rules = [
            'area_construida' => 'sometimes|nullable|numeric|min:0',
            'area_privativa' => 'sometimes|nullable|numeric|min:0',
            'area_total' => 'sometimes|nullable|numeric|min:0',
            'unidade_medida' => 'sometimes|string|in:m2,ha,alq',
        ];
        
        // Se não for rascunho e estiver tentando enviar todos os campos obrigatórios,
        // adiciona regras de obrigatoriedade apenas para os campos presentes na requisição
        if (!$isRascunho) {
            // Verificamos se os campos obrigatórios estão presentes na requisição
            $camposObrigatorios = ['area_total', 'unidade_medida'];
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
                    'area_total' => 'required|numeric|min:0',
                    'unidade_medida' => 'required|string|in:m2,ha,alq',
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
            'area_construida.numeric' => 'A área construída deve ser um valor numérico.',
            'area_construida.min' => 'A área construída não pode ser negativa.',
            
            'area_privativa.numeric' => 'A área privativa deve ser um valor numérico.',
            'area_privativa.min' => 'A área privativa não pode ser negativa.',
            
            'area_total.required' => 'A área total é obrigatória.',
            'area_total.numeric' => 'A área total deve ser um valor numérico.',
            'area_total.min' => 'A área total não pode ser negativa.',
            
            'unidade_medida.required' => 'A unidade de medida é obrigatória.',
            'unidade_medida.in' => 'A unidade de medida deve ser m² (metros quadrados), ha (hectares) ou alq (alqueires).',
        ];
    }
}
