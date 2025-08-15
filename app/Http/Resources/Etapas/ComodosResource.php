<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class ComodosResource extends JsonResource
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
            'dormitorios' => $this->dormitorios,
            'suites' => $this->suites,
            'banheiros' => $this->banheiros,
            'garagens' => $this->garagens,
            'garagem_coberta' => (bool) $this->garagem_coberta,
            'box_garagem' => (bool) $this->box_garagem,
            'sala_tv' => $this->sala_tv,
            'sala_jantar' => $this->sala_jantar,
            'sala_estar' => $this->sala_estar,
            'lavabo' => $this->lavabo,
            'closet' => $this->closet,
            'cozinha' => $this->cozinha,
            'copa' => $this->copa,
            'area_servico' => $this->area_servico,
            'dependencia_servico' => $this->dependencia_servico,
            'escritorio' => $this->escritorio,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
