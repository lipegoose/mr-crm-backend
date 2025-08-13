<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ImovelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $imovelId = $this->route('id');
        $isRascunho = false;
        
        // Verificar se é um rascunho
        if ($imovelId) {
            $imovel = \App\Models\Imovel::find($imovelId);
            if ($imovel && $imovel->status === 'RASCUNHO') {
                $isRascunho = true;
            }
        }
        
        // Regras básicas para todos os imóveis
        $rules = [
            'codigo_referencia' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('imoveis')->ignore($imovelId)
            ],
            'tipo' => 'nullable|string|in:APARTAMENTO,CASA,COMERCIAL,TERRENO,RURAL,INDUSTRIAL',
            'subtipo' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:RASCUNHO,ATIVO,INATIVO,VENDIDO,ALUGADO,RESERVADO,EM_NEGOCIACAO',
            'perfil' => 'nullable|string|exists:perfis,value',
            'situacao' => 'nullable|string|exists:situacoes,value',
            'proprietario_id' => 'nullable|integer|exists:users,id',
            'corretor_id' => 'nullable|integer|exists:users,id',
            'condominio_id' => 'nullable|integer|exists:condominios,id',
            
            // Campos de endereço
            'cep' => 'nullable|string|max:10',
            'uf' => 'nullable|string|size:2',
            'cidade' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'logradouro' => 'nullable|string|max:200',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'mostrar_endereco_site' => 'nullable|boolean',
            
            // Campos de valores
            'valor_venda' => 'nullable|numeric|min:0',
            'valor_locacao' => 'nullable|numeric|min:0',
            'valor_condominio' => 'nullable|numeric|min:0',
            'valor_iptu' => 'nullable|numeric|min:0',
            'mostrar_valores_site' => 'nullable|boolean',
            
            // Campos de características físicas
            'area_total' => 'nullable|numeric|min:0',
            'area_privativa' => 'nullable|numeric|min:0',
            'quartos' => 'nullable|integer|min:0',
            'banheiros' => 'nullable|integer|min:0',
            'suites' => 'nullable|integer|min:0',
            'vagas' => 'nullable|integer|min:0',
            
            // Campos de publicação
            'publicar_site' => 'nullable|boolean',
            'destaque_site' => 'nullable|boolean',
            
            // Campos de negociação
            'aceita_financiamento' => 'nullable|boolean',
            'aceita_permuta' => 'nullable|boolean',
            
            // Campos de detalhes
            'detalhes' => 'nullable|array',
            'detalhes.titulo_anuncio' => 'nullable|string|max:200',
            'detalhes.mostrar_titulo' => 'nullable|boolean',
            'detalhes.descricao' => 'nullable|string',
            'detalhes.mostrar_descricao' => 'nullable|boolean',
            'detalhes.palavras_chave' => 'nullable|string|max:255',
            'detalhes.observacoes_internas' => 'nullable|string',
            'detalhes.exclusividade' => 'nullable|boolean',
            'detalhes.data_inicio_exclusividade' => 'nullable|date',
            'detalhes.data_fim_exclusividade' => 'nullable|date|after_or_equal:detalhes.data_inicio_exclusividade',
            'detalhes.valor_comissao' => 'nullable|numeric|min:0|max:100',
            'detalhes.tipo_comissao' => 'nullable|string|in:PORCENTAGEM,VALOR_FIXO',
            'detalhes.config_exibicao' => 'nullable|json',
        ];
        
        // Se não for rascunho e estiver tentando ativar, aplicar regras mais rígidas
        if (!$isRascunho && $this->input('status') === 'ATIVO') {
            $rules = array_merge($rules, [
                'tipo' => 'required|string|in:APARTAMENTO,CASA,COMERCIAL,TERRENO,RURAL,INDUSTRIAL',
                'subtipo' => 'required|string|max:50',
                'perfil' => 'required|string|exists:perfis,value',
                'proprietario_id' => 'required|integer|exists:users,id',
                'corretor_id' => 'required|integer|exists:users,id',
                'area_total' => 'required|numeric|min:0',
                'quartos' => 'required|integer|min:0',
                'banheiros' => 'required|integer|min:0',
                'cep' => 'required|string|max:10',
                'uf' => 'required|string|size:2',
                'cidade' => 'required|string|max:100',
                'bairro' => 'required|string|max:100',
                'logradouro' => 'required|string|max:200',
            ]);
        }
        
        // Regras específicas por tipo de imóvel
        if ($this->input('tipo')) {
            switch ($this->input('tipo')) {
                case 'APARTAMENTO':
                    $rules = array_merge($rules, [
                        'area_privativa' => 'nullable|numeric|min:0',
                        'valor_condominio' => 'nullable|numeric|min:0',
                    ]);
                    break;
                    
                case 'CASA':
                    $rules = array_merge($rules, [
                        'area_terreno' => 'nullable|numeric|min:0',
                        'area_construida' => 'nullable|numeric|min:0',
                    ]);
                    break;
                    
                case 'TERRENO':
                    $rules = array_merge($rules, [
                        'area_total' => 'nullable|numeric|min:0',
                        'frente' => 'nullable|numeric|min:0',
                        'fundo' => 'nullable|numeric|min:0',
                        'lateral_esquerda' => 'nullable|numeric|min:0',
                        'lateral_direita' => 'nullable|numeric|min:0',
                    ]);
                    break;
                    
                case 'COMERCIAL-LOJA':
                case 'COMERCIAL-SALA':
                case 'COMERCIAL-GALPAO':
                    $rules = array_merge($rules, [
                        'area_total' => 'nullable|numeric|min:0',
                        'pe_direito' => 'nullable|numeric|min:0',
                        'valor_condominio' => 'nullable|numeric|min:0',
                    ]);
                    break;
                    
                case 'CHACARA':
                case 'FAZENDA':
                case 'SITIO':
                    $rules = array_merge($rules, [
                        'area_total' => 'nullable|numeric|min:0',
                        'area_construida' => 'nullable|numeric|min:0',
                        'distancia_cidade' => 'nullable|numeric|min:0',
                    ]);
                    break;
            }
        }
        
        return $rules;
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'size' => 'O campo :attribute deve ter exatamente :size caracteres.',
            'in' => 'O valor selecionado para :attribute é inválido.',
            'exists' => 'O :attribute selecionado não existe.',
            'unique' => 'Este :attribute já está em uso.',
            'after_or_equal' => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
            'json' => 'O campo :attribute deve ser um JSON válido.',
            
            'tipo.required' => 'O tipo do imóvel é obrigatório.',
            'subtipo.required' => 'O subtipo do imóvel é obrigatório.',
            'perfil.required' => 'O perfil do imóvel é obrigatório.',
            'proprietario_id.required' => 'O proprietário do imóvel é obrigatório.',
            'corretor_id.required' => 'O corretor responsável é obrigatório.',
            'area_total.required' => 'A área total do imóvel é obrigatória.',
            'quartos.required' => 'O número de quartos é obrigatório.',
            'banheiros.required' => 'O número de banheiros é obrigatório.',
            'cep.required' => 'O CEP é obrigatório.',
            'uf.required' => 'O estado (UF) é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'bairro.required' => 'O bairro é obrigatório.',
            'logradouro.required' => 'O logradouro é obrigatório.',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'codigo_referencia' => 'código de referência',
            'tipo' => 'tipo',
            'subtipo' => 'subtipo',
            'perfil' => 'perfil',
            'status' => 'status',
            'proprietario_id' => 'proprietário',
            'corretor_id' => 'corretor',
            'condominio_id' => 'condomínio',
            'cep' => 'CEP',
            'uf' => 'UF',
            'cidade' => 'cidade',
            'bairro' => 'bairro',
            'logradouro' => 'logradouro',
            'numero' => 'número',
            'complemento' => 'complemento',
            'mostrar_endereco_site' => 'mostrar endereço no site',
            'valor_venda' => 'valor de venda',
            'valor_locacao' => 'valor de locação',
            'valor_condominio' => 'valor do condomínio',
            'valor_iptu' => 'valor do IPTU',
            'mostrar_valores_site' => 'mostrar valores no site',
            'area_total' => 'área total',
            'area_privativa' => 'área privativa',
            'quartos' => 'quartos',
            'banheiros' => 'banheiros',
            'suites' => 'suítes',
            'vagas' => 'vagas',
            'publicar_site' => 'publicar no site',
            'destaque_site' => 'destaque no site',
            'aceita_financiamento' => 'aceita financiamento',
            'aceita_permuta' => 'aceita permuta',
            'detalhes.titulo_anuncio' => 'título do anúncio',
            'detalhes.mostrar_titulo' => 'mostrar título',
            'detalhes.descricao' => 'descrição',
            'detalhes.mostrar_descricao' => 'mostrar descrição',
            'detalhes.palavras_chave' => 'palavras-chave',
            'detalhes.observacoes_internas' => 'observações internas',
            'detalhes.exclusividade' => 'exclusividade',
            'detalhes.data_inicio_exclusividade' => 'data de início da exclusividade',
            'detalhes.data_fim_exclusividade' => 'data de fim da exclusividade',
            'detalhes.valor_comissao' => 'valor da comissão',
            'detalhes.tipo_comissao' => 'tipo de comissão',
            'detalhes.config_exibicao' => 'configurações de exibição',
        ];
    }
}
