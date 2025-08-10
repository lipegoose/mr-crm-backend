<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class CaracteristicasCondominioResource extends JsonResource
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
            'condominio_id' => $this->condominio_id,
            'condominio' => $this->whenLoaded('condominio', function () {
                return [
                    'id' => $this->condominio->id,
                    'nome' => $this->condominio->nome,
                    'endereco' => $this->condominio->formatarEndereco(),
                    'caracteristicas' => $this->condominio->caracteristicas->map(function ($caracteristica) {
                        return [
                            'id' => $caracteristica->id,
                            'nome' => $caracteristica->nome,
                            'descricao' => $caracteristica->descricao,
                            'icone' => $caracteristica->icone,
                            'categoria' => $caracteristica->categoria,
                        ];
                    }),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
