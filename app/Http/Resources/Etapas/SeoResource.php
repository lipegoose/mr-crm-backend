<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
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
            'seo_title' => $this->whenLoaded('detalhes', function () {
                $seo = $this->detalhes->seo ? json_decode($this->detalhes->seo) : null;
                return $seo && isset($seo->title) ? $seo->title : null;
            }),
            'seo_description' => $this->whenLoaded('detalhes', function () {
                $seo = $this->detalhes->seo ? json_decode($this->detalhes->seo) : null;
                return $seo && isset($seo->description) ? $seo->description : null;
            }),
            'seo_keywords' => $this->whenLoaded('detalhes', function () {
                $seo = $this->detalhes->seo ? json_decode($this->detalhes->seo) : null;
                return $seo && isset($seo->keywords) ? $seo->keywords : null;
            }),
            'url_amigavel' => $this->whenLoaded('detalhes', function () {
                $seo = $this->detalhes->seo ? json_decode($this->detalhes->seo) : null;
                return $seo && isset($seo->url_amigavel) ? $seo->url_amigavel : null;
            }),
            'gerar_url_automatica' => $this->whenLoaded('detalhes', function () {
                $seo = $this->detalhes->seo ? json_decode($this->detalhes->seo) : null;
                return $seo && isset($seo->gerar_url_automatica) ? (bool) $seo->gerar_url_automatica : false;
            }),
            'gerar_seo_automatico' => $this->whenLoaded('detalhes', function () {
                $seo = $this->detalhes->seo ? json_decode($this->detalhes->seo) : null;
                return $seo && isset($seo->gerar_seo_automatico) ? (bool) $seo->gerar_seo_automatico : false;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
