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
            'quartos' => $this->quartos,
            'suites' => $this->suites,
            'banheiros' => $this->banheiros,
            'vagas' => $this->vagas,
            'salas' => $this->salas,
            'salas_jantar' => $this->salas_jantar,
            'salas_estar' => $this->salas_estar,
            'salas_tv' => $this->salas_tv,
            'lavabos' => $this->lavabos,
            'closets' => $this->closets,
            'cozinhas' => $this->cozinhas,
            'copas' => $this->copas,
            'areas_servico' => $this->areas_servico,
            'despensas' => $this->despensas,
            'dependencias_empregada' => $this->dependencias_empregada,
            'escritorios' => $this->escritorios,
            'varandas' => $this->varandas,
            'sacadas' => $this->sacadas,
            'terraco' => (bool) $this->terraco,
            'jardim' => (bool) $this->jardim,
            'quintal' => (bool) $this->quintal,
            'piscina' => (bool) $this->piscina,
            'churrasqueira' => (bool) $this->churrasqueira,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
