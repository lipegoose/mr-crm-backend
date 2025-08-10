<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class PrecoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tipo_negocio' => $this->tipo_negocio,
            'preco_venda' => $this->preco_venda,
            'preco_aluguel' => $this->preco_aluguel,
            'preco_temporada' => $this->preco_temporada,
            'mostrar_preco' => (bool) $this->mostrar_preco,
            'preco_alternativo' => $this->preco_alternativo,
            'preco_anterior' => $this->preco_anterior,
            'mostrar_preco_anterior' => (bool) $this->mostrar_preco_anterior,
            'preco_iptu' => $this->preco_iptu,
            'periodo_iptu' => $this->periodo_iptu,
            'preco_condominio' => $this->preco_condominio,
            'financiado' => (bool) $this->financiado,
            'aceita_financiamento' => (bool) $this->aceita_financiamento,
            'minha_casa_minha_vida' => (bool) $this->minha_casa_minha_vida,
            'total_taxas' => $this->total_taxas,
            'descricao_taxas' => $this->descricao_taxas,
            'aceita_permuta' => (bool) $this->aceita_permuta,
            'preco_venda_formatado' => $this->preco_venda ? 'R$ ' . number_format($this->preco_venda, 2, ',', '.') : null,
            'preco_aluguel_formatado' => $this->preco_aluguel ? 'R$ ' . number_format($this->preco_aluguel, 2, ',', '.') : null,
            'preco_temporada_formatado' => $this->preco_temporada ? 'R$ ' . number_format($this->preco_temporada, 2, ',', '.') : null,
            'preco_anterior_formatado' => $this->preco_anterior ? 'R$ ' . number_format($this->preco_anterior, 2, ',', '.') : null,
            'preco_iptu_formatado' => $this->preco_iptu ? 'R$ ' . number_format($this->preco_iptu, 2, ',', '.') : null,
            'preco_condominio_formatado' => $this->preco_condominio ? 'R$ ' . number_format($this->preco_condominio, 2, ',', '.') : null,
            'total_taxas_formatado' => $this->total_taxas ? 'R$ ' . number_format($this->total_taxas, 2, ',', '.') : null,
            'historico_precos' => $this->whenLoaded('precosHistorico', function () {
                return $this->precosHistorico->map(function ($historico) {
                    return [
                        'id' => $historico->id,
                        'tipo' => $historico->tipo,
                        'valor_anterior' => $historico->valor_anterior,
                        'valor_novo' => $historico->valor_novo,
                        'motivo' => $historico->motivo,
                        'data' => $historico->created_at,
                        'usuario' => $historico->usuario ? [
                            'id' => $historico->usuario->id,
                            'nome' => $historico->usuario->name,
                        ] : null,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
