<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProprietarioRequest
{
    /**
     * Valida os dados da requisição para a etapa de Proprietário.
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
     * Regras de validação para a etapa de Proprietário.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'proprietario_id' => 'sometimes|nullable|integer|exists:pessoas,id',
            'proprietario_nome' => 'sometimes|nullable|string|max:200',
            'proprietario_email' => 'sometimes|nullable|email|max:200',
            'proprietario_telefone' => 'sometimes|nullable|string|max:20',
            'proprietario_celular' => 'sometimes|nullable|string|max:20',
            'corretor_id' => 'sometimes|nullable|integer|exists:pessoas,id',
            'exclusividade' => 'sometimes|boolean',
            'data_inicio_exclusividade' => 'sometimes|nullable|date',
            'data_fim_exclusividade' => 'sometimes|nullable|date|after_or_equal:data_inicio_exclusividade',
            'comissao_percentual' => 'sometimes|nullable|numeric|min:0|max:100',
            'comissao_valor' => 'sometimes|nullable|numeric|min:0',
            'matricula' => 'sometimes|nullable|string|max:100',
            'inscricao_municipal' => 'sometimes|nullable|string|max:100',
            'anotacoes_privativas' => 'sometimes|nullable|string|max:5000',
        ];
        
        // Se não for rascunho e estiver tentando enviar todos os campos obrigatórios,
        // adiciona regras de obrigatoriedade apenas para os campos presentes na requisição
        if (!$isRascunho) {
            // Verificamos se os campos obrigatórios estão presentes na requisição
            $camposObrigatorios = ['proprietario_id'];
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
                    'proprietario_id' => 'required|integer|exists:pessoas,id',
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
            'proprietario_id.required' => 'O proprietário é obrigatório.',
            'proprietario_id.integer' => 'O ID do proprietário deve ser um número inteiro.',
            'proprietario_id.exists' => 'O proprietário selecionado não existe.',
            
            'proprietario_nome.max' => 'O nome do proprietário não pode ter mais de 200 caracteres.',
            
            'proprietario_email.email' => 'O e-mail do proprietário deve ser um e-mail válido.',
            'proprietario_email.max' => 'O e-mail do proprietário não pode ter mais de 200 caracteres.',
            
            'proprietario_telefone.max' => 'O telefone do proprietário não pode ter mais de 20 caracteres.',
            
            'proprietario_celular.max' => 'O celular do proprietário não pode ter mais de 20 caracteres.',
            
            'corretor_id.integer' => 'O ID do corretor deve ser um número inteiro.',
            'corretor_id.exists' => 'O corretor selecionado não existe.',
            
            'data_inicio_exclusividade.date' => 'A data de início da exclusividade deve ser uma data válida.',
            
            'data_fim_exclusividade.date' => 'A data de fim da exclusividade deve ser uma data válida.',
            'data_fim_exclusividade.after_or_equal' => 'A data de fim da exclusividade deve ser igual ou posterior à data de início.',
            
            'comissao_percentual.numeric' => 'O percentual de comissão deve ser um valor numérico.',
            'comissao_percentual.min' => 'O percentual de comissão não pode ser negativo.',
            'comissao_percentual.max' => 'O percentual de comissão não pode ser maior que 100%.',
            
            'comissao_valor.numeric' => 'O valor da comissão deve ser um valor numérico.',
            'comissao_valor.min' => 'O valor da comissão não pode ser negativo.',
            
            'matricula.max' => 'A matrícula não pode ter mais de 100 caracteres.',
            
            'inscricao_municipal.max' => 'A inscrição municipal não pode ter mais de 100 caracteres.',
            
            'anotacoes_privativas.max' => 'As anotações privativas não podem ter mais de 5000 caracteres.',
        ];
    }
}
