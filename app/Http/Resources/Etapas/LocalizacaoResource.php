<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class LocalizacaoResource extends JsonResource
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
            'cep' => $this->cep,
            'uf' => $this->uf,
            'cidade' => $this->cidade,
            'bairro' => $this->bairro,
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'mostrar_endereco' => (bool) $this->mostrar_endereco,
            'mostrar_numero' => (bool) $this->mostrar_numero,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'endereco_formatado' => $this->formatarEndereco(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
