<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class InformacoesResource extends JsonResource
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
            'codigo_referencia' => $this->codigo_referencia,
            'tipo' => $this->tipo,
            'subtipo' => $this->subtipo,
            'perfil' => $this->perfil,
            'finalidade' => $this->finalidade,
            'tipo_negocio' => $this->tipo_negocio,
            'status' => $this->status,
            'data_captacao' => $this->data_captacao,
            'data_disponibilidade' => $this->data_disponibilidade,
            'ano_construcao' => $this->ano_construcao,
            'ocupacao' => $this->ocupacao,
            'mobiliado' => (bool) $this->mobiliado,
            'reformado' => (bool) $this->reformado,
            'ano_reforma' => $this->ano_reforma,
            'condominio_id' => $this->condominio_id,
            'condominio' => $this->whenLoaded('condominio', function () {
                return [
                    'id' => $this->condominio->id,
                    'nome' => $this->condominio->nome,
                    'endereco' => $this->condominio->formatarEndereco(),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
