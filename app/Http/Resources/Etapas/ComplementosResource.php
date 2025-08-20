<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class ComplementosResource extends JsonResource
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
            'observacoes_internas' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->observacoes_internas;
            }),
            'tour_virtual_url' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->tour_virtual_url;
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
                        'url' => $planta->url,
                        'ordem' => $planta->ordem,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
