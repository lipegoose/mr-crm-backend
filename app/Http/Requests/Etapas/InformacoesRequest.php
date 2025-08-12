<?php

namespace App\Http\Requests\Etapas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InformacoesRequest
{
    /**
     * Valida os dados da requisição para a etapa de Informações Iniciais.
     *
     * @param Request $request
     * @param bool $isRascunho
     * @return array
     * @throws ValidationException
     */
    public function validate(Request $request, bool $isRascunho = false)
    {
        // Normalizar os valores antes da validação
        $dados = $request->all();
        
        // Lista de valores válidos para cada campo
        $tiposValidos = [
            'APARTAMENTO', 'APARTAMENTO-COBERTURA', 'APARTAMENTO-DUPLEX', 
            'CASA', 'CASA-CONDOMINIO', 'CHACARA', 'COMERCIAL-LOJA', 
            'COMERCIAL-SALA', 'COMERCIAL-GALPAO', 'TERRENO', 'FAZENDA', 'SITIO'
        ];
        
        $perfisValidos = ['RESIDENCIAL', 'COMERCIAL', 'INDUSTRIAL', 'RURAL'];
        $situacoesValidas = ['NOVO', 'USADO', 'PLANTA', 'CONSTRUCAO', 'REFORMA'];
        
        // Normalizar tipo
        if (isset($dados['tipo'])) {
            $tipoUpper = strtoupper($dados['tipo']);
            foreach ($tiposValidos as $tipo) {
                if ($tipoUpper === strtoupper($tipo)) {
                    $dados['tipo'] = $tipo;
                    break;
                }
            }
        }
        
        // Normalizar perfil
        if (isset($dados['perfil'])) {
            $perfilUpper = strtoupper($dados['perfil']);
            foreach ($perfisValidos as $perfil) {
                if ($perfilUpper === $perfil) {
                    $dados['perfil'] = $perfil;
                    break;
                }
            }
        }
        
        // Normalizar situação
        if (isset($dados['situacao'])) {
            $situacaoUpper = strtoupper($dados['situacao']);
            foreach ($situacoesValidas as $situacao) {
                if ($situacaoUpper === $situacao) {
                    $dados['situacao'] = $situacao;
                    break;
                }
            }
        }
        
        $rules = $this->rules($isRascunho);
        $messages = $this->messages();
        
        $validator = Validator::make($dados, $rules, $messages);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Regras de validação para a etapa de Informações Iniciais.
     *
     * @param bool $isRascunho
     * @return array
     */
    protected function rules(bool $isRascunho = false)
    {
        // Regras básicas que se aplicam mesmo em modo rascunho
        $rules = [
            'tipo' => 'sometimes|string|in:APARTAMENTO,APARTAMENTO-COBERTURA,APARTAMENTO-DUPLEX,CASA,CASA-CONDOMINIO,CHACARA,COMERCIAL-LOJA,COMERCIAL-SALA,COMERCIAL-GALPAO,TERRENO,FAZENDA,SITIO',
            'subtipo' => 'sometimes|nullable|string|max:50',
            'perfil' => 'sometimes|string|in:RESIDENCIAL,COMERCIAL,INDUSTRIAL,RURAL',
            'situacao' => 'sometimes|string|in:NOVO,USADO,PLANTA,CONSTRUCAO,REFORMA',
            'proprietario_id' => 'sometimes|nullable|integer|exists:clientes,id',
            'condominio_id' => 'sometimes|nullable|integer|exists:condominios,id',
            'ano_construcao' => 'sometimes|nullable|integer|min:1900|max:' . (date('Y') + 10),
            'incorporacao' => 'sometimes|nullable|string|max:100',
            'posicao_solar' => 'sometimes|nullable|string|in:NORTE,SUL,LESTE,OESTE,NORDESTE,NOROESTE,SUDESTE,SUDOESTE',
            'terreno' => 'sometimes|nullable|string|max:100',
            'escriturado' => 'sometimes|boolean',
            'esquina' => 'sometimes|boolean',
            'mobiliado' => 'sometimes|boolean',
            'averbado' => 'sometimes|boolean',
            'corretor_id' => 'sometimes|nullable|integer|exists:users,id',
        ];
        
        // Se não for rascunho, adiciona regras de obrigatoriedade
        if (!$isRascunho) {
            $rules = array_merge($rules, [
                'tipo' => 'required|string|in:APARTAMENTO,APARTAMENTO-COBERTURA,APARTAMENTO-DUPLEX,CASA,CASA-CONDOMINIO,CHACARA,COMERCIAL-LOJA,COMERCIAL-SALA,COMERCIAL-GALPAO,TERRENO,FAZENDA,SITIO',
                'perfil' => 'required|string|in:RESIDENCIAL,COMERCIAL,INDUSTRIAL,RURAL',
                'situacao' => 'required|string|in:NOVO,USADO,PLANTA,CONSTRUCAO,REFORMA',
                'proprietario_id' => 'required|integer|exists:clientes,id',
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
            'tipo.required' => 'O tipo do imóvel é obrigatório.',
            'tipo.in' => 'O tipo do imóvel selecionado não é válido.',
            'perfil.required' => 'O perfil do imóvel é obrigatório.',
            'perfil.in' => 'O perfil do imóvel selecionado não é válido.',
            'situacao.required' => 'A situação do imóvel é obrigatória.',
            'situacao.in' => 'A situação do imóvel selecionada não é válida.',
            'proprietario_id.required' => 'O proprietário do imóvel é obrigatório.',
            'proprietario_id.exists' => 'O proprietário selecionado não existe.',
            'condominio_id.exists' => 'O condomínio selecionado não existe.',
            'ano_construcao.integer' => 'O ano de construção deve ser um número inteiro.',
            'ano_construcao.min' => 'O ano de construção deve ser posterior a 1900.',
            'ano_construcao.max' => 'O ano de construção não pode ser tão futuro.',
            'posicao_solar.in' => 'A posição solar selecionada não é válida.',
            'corretor_id.exists' => 'O corretor selecionado não existe.',
        ];
    }
}
