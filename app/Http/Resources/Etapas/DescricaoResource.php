<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class DescricaoResource extends JsonResource
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
            'titulo_anuncio' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->titulo_anuncio;
            }),
            'mostrar_titulo' => $this->whenLoaded('detalhes', function () {
                return (bool) $this->detalhes->mostrar_titulo;
            }),
            'descricao' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->descricao;
            }),
            'mostrar_descricao' => $this->whenLoaded('detalhes', function () {
                return (bool) $this->detalhes->mostrar_descricao;
            }),
            'palavras_chave' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->palavras_chave;
            }),
            'gerar_titulo_automatico' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->gerar_titulo_automatico) ? (bool) $config->gerar_titulo_automatico : false;
            }),
            'gerar_descricao_automatica' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->gerar_descricao_automatica) ? (bool) $config->gerar_descricao_automatica : false;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
