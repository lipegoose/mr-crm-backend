<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicacaoResource extends JsonResource
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
            'publicar_site' => (bool) $this->publicar_site,
            'destaque_site' => (bool) $this->destaque_site,
            'publicar_portais' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->publicar_portais) ? (bool) $config->publicar_portais : false;
            }),
            'portais' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->portais) ? $config->portais : [];
            }),
            'publicar_redes_sociais' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->publicar_redes_sociais) ? (bool) $config->publicar_redes_sociais : false;
            }),
            'redes_sociais' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->redes_sociais) ? $config->redes_sociais : [];
            }),
            'data_publicacao' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->data_publicacao) ? $config->data_publicacao : null;
            }),
            'data_expiracao' => $this->whenLoaded('detalhes', function () {
                $config = $this->detalhes->config_exibicao ? json_decode($this->detalhes->config_exibicao) : null;
                return $config && isset($config->data_expiracao) ? $config->data_expiracao : null;
            }),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
