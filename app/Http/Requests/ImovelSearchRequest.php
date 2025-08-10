<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ImovelSearchRequest extends Request
{
    /**
     * Valida a requisição de busca avançada.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate()
    {
        $validator = \Illuminate\Support\Facades\Validator::make($this->all(), $this->rules(), $this->messages());
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $this->all();
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'tipoSubtipo' => 'sometimes|string|in:apartamento,apartamento-cobertura,apartamento-duplex,casa,casa-condominio,chacara,comercial-loja,comercial-sala,comercial-galpao,terreno,fazenda,sitio',
            'transacao' => 'sometimes|string|in:VENDA,ALUGUEL',
            'uf' => 'sometimes|string|size:2',
            'cidade' => 'sometimes|string|max:100',
            'bairros' => 'sometimes|array',
            'bairros.*' => 'string|max:100',
            'perfilImovel' => 'sometimes|string|in:residencial,comercial,residencial-comercial,industrial,rural',
            'dormitorios' => 'sometimes|integer|min:0',
            'suites' => 'sometimes|integer|min:0',
            'garagens' => 'sometimes|integer|min:0',
            'situacao' => 'sometimes|array',
            'situacao.*' => 'string|in:pronto,construcao,planta,reforma',
            'precoMin' => 'sometimes|numeric|min:0',
            'precoMax' => 'sometimes|numeric|min:0|gte:precoMin',
            'areaMin' => 'sometimes|numeric|min:0',
            'areaMax' => 'sometimes|numeric|min:0|gte:areaMin',
            'mobiliado' => 'sometimes|boolean',
            'aceitaPermuta' => 'sometimes|boolean',
            'aceitaFinanciamento' => 'sometimes|boolean',
            'requisitosImovel' => 'sometimes|array',
            'requisitosImovel.*' => 'integer|exists:caracteristicas,id',
            'requisitosCondominio' => 'sometimes|array',
            'requisitosCondominio.*' => 'integer|exists:caracteristicas,id',
            'ordenacao' => 'sometimes|string|in:recentes,preco_asc,preco_desc,area_asc,area_desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'fields' => 'sometimes|string',
            'include' => 'sometimes|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    protected function messages()
    {
        return [
            'tipoSubtipo.in' => 'O tipo/subtipo informado não é válido.',
            'transacao.in' => 'O tipo de transação deve ser VENDA ou ALUGUEL.',
            'perfilImovel.in' => 'O perfil do imóvel informado não é válido.',
            'precoMin.min' => 'O preço mínimo deve ser maior ou igual a zero.',
            'precoMax.min' => 'O preço máximo deve ser maior ou igual a zero.',
            'precoMax.gte' => 'O preço máximo deve ser maior ou igual ao preço mínimo.',
            'areaMin.min' => 'A área mínima deve ser maior ou igual a zero.',
            'areaMax.min' => 'A área máxima deve ser maior ou igual a zero.',
            'areaMax.gte' => 'A área máxima deve ser maior ou igual à área mínima.',
            'requisitosImovel.*.exists' => 'Uma ou mais características de imóvel selecionadas não existem.',
            'requisitosCondominio.*.exists' => 'Uma ou mais características de condomínio selecionadas não existem.',
            'ordenacao.in' => 'O critério de ordenação informado não é válido.',
        ];
    }
}
