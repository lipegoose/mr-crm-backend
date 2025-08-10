<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class CaracteristicasResource extends JsonResource
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
            'caracteristicas' => $this->whenLoaded('caracteristicas', function () {
                return $this->caracteristicas
                    ->where('escopo', 'IMOVEL')
                    ->map(function ($caracteristica) {
                        return [
                            'id' => $caracteristica->id,
                            'nome' => $caracteristica->nome,
                            'descricao' => $caracteristica->descricao,
                            'icone' => $caracteristica->icone,
                            'categoria' => $caracteristica->categoria,
                        ];
                    });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
