<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class ImagensResource extends JsonResource
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
            'imagens' => $this->whenLoaded('imagens', function () {
                return $this->imagens->map(function ($imagem) {
                    return [
                        'id' => $imagem->id,
                        'titulo' => $imagem->titulo,
                        'caminho' => $imagem->caminho,
                        'url' => $imagem->url,
                        'ordem' => $imagem->ordem,
                        'principal' => (bool) $imagem->principal,
                    ];
                });
            }),
            'imagem_principal' => $this->whenLoaded('imagens', function () {
                $imagem = $this->imagens->where('principal', true)->first();
                if (!$imagem) {
                    $imagem = $this->imagens->first();
                }
                
                if ($imagem) {
                    return [
                        'id' => $imagem->id,
                        'titulo' => $imagem->titulo,
                        'caminho' => $imagem->caminho,
                        'url' => $imagem->url,
                    ];
                }
                
                return null;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
