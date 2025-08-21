<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ImovelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Verificar se é uma solicitação detalhada ou resumida
        $isDetailed = $request->has('detailed') && $request->detailed === 'true';
        
        // Dados básicos (sempre incluídos)
        $data = [
            'id' => $this->id,
            'codigo_referencia' => $this->codigo_referencia,
            'tipo' => $this->tipo,
            'subtipo' => $this->subtipo,
            'perfil' => $this->perfil,
            'status' => $this->status,
            'endereco' => [
                'cep' => $this->cep,
                'uf' => $this->uf,
                'cidade' => $this->cidade,
                'bairro' => $this->bairro,
                'logradouro' => $this->logradouro,
                'numero' => $this->numero,
                'complemento' => $this->complemento,
                'mostrar_endereco_site' => (bool) $this->mostrar_endereco_site,
                'endereco_formatado' => $this->formatarEndereco(),
            ],
            'caracteristicas_fisicas' => [
                'area_total' => $this->area_total,
                'area_privativa' => $this->area_privativa,
                'dormitorios' => $this->dormitorios,
                'banheiros' => $this->banheiros,
                'suites' => $this->suites,
                'vagas' => $this->vagas,
            ],
            'valores' => [
                // Mantém as mesmas chaves na resposta para compatibilidade,
                // mas lê dos campos atuais do modelo (preco_*)
                'valor_venda' => $this->preco_venda,
                'valor_locacao' => $this->preco_aluguel,
                'valor_condominio' => $this->preco_condominio,
                'valor_iptu' => $this->preco_iptu,
                // No modelo atual o flag é 'mostrar_preco'; manter a chave antiga na resposta
                'mostrar_valores_site' => (bool) $this->mostrar_preco,
                'valor_venda_formatado' => $this->preco_venda ? 'R$ ' . number_format($this->preco_venda, 2, ',', '.') : null,
                'valor_locacao_formatado' => $this->preco_aluguel ? 'R$ ' . number_format($this->preco_aluguel, 2, ',', '.') : null,
                'valor_condominio_formatado' => $this->preco_condominio ? 'R$ ' . number_format($this->preco_condominio, 2, ',', '.') : null,
                'valor_iptu_formatado' => $this->preco_iptu ? 'R$ ' . number_format($this->preco_iptu, 2, ',', '.') : null,
            ],
            'publicacao' => [
                'publicar_site' => (bool) $this->publicar_site,
                'destaque_site' => (bool) $this->destaque_site,
            ],
            'negociacao' => [
                'aceita_financiamento' => (bool) $this->aceita_financiamento,
                'aceita_permuta' => (bool) $this->aceita_permuta,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        
        // Imagem principal (se existir)
        $imagemPrincipal = $this->whenLoaded('imagens', function () {
            $imagem = $this->imagens->where('principal', true)->first();
            if (!$imagem) {
                $imagem = $this->imagens->first();
            }
            
            if ($imagem) {
                return [
                    'id' => $imagem->id,
                    'titulo' => $imagem->titulo,
                    'url' => $imagem->url,
                ];
            }
            
            return null;
        });
        
        $data['imagem_principal'] = $imagemPrincipal;
        
        // Se for detalhado, incluir mais informações
        if ($isDetailed) {
            $data = array_merge($data, [
                'proprietario' => $this->whenLoaded('proprietario', function () {
                    return [
                        'id' => $this->proprietario->id,
                        'nome' => $this->proprietario->name,
                        'email' => $this->proprietario->email,
                    ];
                }),
                'corretor' => $this->whenLoaded('corretor', function () {
                    return [
                        'id' => $this->corretor->id,
                        'nome' => $this->corretor->name,
                        'email' => $this->corretor->email,
                    ];
                }),
                'condominio' => $this->whenLoaded('condominio', function () {
                    return [
                        'id' => $this->condominio->id,
                        'nome' => $this->condominio->nome,
                        'endereco' => $this->condominio->formatarEndereco(),
                    ];
                }),
                'detalhes' => $this->whenLoaded('detalhes', function () {
                    return [
                        'titulo_anuncio' => $this->detalhes->titulo_anuncio,
                        'mostrar_titulo' => (bool) $this->detalhes->mostrar_titulo,
                        'descricao' => $this->detalhes->descricao,
                        'mostrar_descricao' => (bool) $this->detalhes->mostrar_descricao,
                        'palavras_chave' => $this->detalhes->palavras_chave,
                        'observacoes_internas' => $this->detalhes->observacoes_internas,
                        'exclusividade' => (bool) $this->detalhes->exclusividade,
                        'data_inicio_exclusividade' => $this->detalhes->data_inicio_exclusividade,
                        'data_fim_exclusividade' => $this->detalhes->data_fim_exclusividade,
                        'valor_comissao' => $this->detalhes->valor_comissao,
                        'tipo_comissao' => $this->detalhes->tipo_comissao,
                        'config_exibicao' => $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null,
                    ];
                }),
                'caracteristicas' => $this->whenLoaded('caracteristicas', function () {
                    return $this->caracteristicas->map(function ($caracteristica) {
                        return [
                            'id' => $caracteristica->id,
                            'nome' => $caracteristica->nome,
                            'descricao' => $caracteristica->descricao,
                            'icone' => $caracteristica->icone,
                            'categoria' => $caracteristica->categoria,
                        ];
                    });
                }),
                'proximidades' => $this->whenLoaded('proximidades', function () {
                    return $this->proximidades->map(function ($proximidade) {
                        return [
                            'id' => $proximidade->id,
                            'nome' => $proximidade->nome,
                            'descricao' => $proximidade->descricao,
                            'icone' => $proximidade->icone,
                            'categoria' => $proximidade->categoria,
                            'distancia_metros' => $proximidade->pivot->distancia_metros,
                            'distancia_texto' => $proximidade->pivot->distancia_texto,
                            'distancia_formatada' => $proximidade->pivot->formatarDistancia(),
                        ];
                    });
                }),
                'imagens' => $this->whenLoaded('imagens', function () {
                    return $this->imagens->map(function ($imagem) {
                        return [
                            'id' => $imagem->id,
                            'titulo' => $imagem->titulo,
                            'url' => $imagem->url,
                            'ordem' => $imagem->ordem,
                            'principal' => (bool) $imagem->principal,
                        ];
                    });
                }),
                'videos' => $this->whenLoaded('videos', function () {
                    return $this->videos->map(function ($video) {
                        return [
                            'id' => $video->id,
                            'titulo' => $video->titulo,
                            'url' => $video->url,
                            'ordem' => $video->ordem,
                            'video_id' => $video->video_id,
                            'thumbnail_url' => $video->thumbnail_url,
                            'embed_url' => $video->embed_url,
                        ];
                    });
                }),
                'plantas' => $this->whenLoaded('plantas', function () {
                    return $this->plantas->map(function ($planta) {
                        return [
                            'id' => $planta->id,
                            'titulo' => $planta->titulo,
                            'url' => $planta->url_completa,
                            'ordem' => $planta->ordem,
                        ];
                    });
                }),
                'auditoria' => [
                    'criado_por' => $this->whenLoaded('criadoPor', function () {
                        return [
                            'id' => $this->criadoPor->id,
                            'nome' => $this->criadoPor->name,
                        ];
                    }),
                    'atualizado_por' => $this->whenLoaded('atualizadoPor', function () {
                        return $this->atualizadoPor ? [
                            'id' => $this->atualizadoPor->id,
                            'nome' => $this->atualizadoPor->name,
                        ] : null;
                    }),
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                    'deleted_at' => $this->deleted_at,
                ],
            ]);
        }
        
        return $data;
    }
}
