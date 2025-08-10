<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class ProprietarioResource extends JsonResource
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
            'proprietario_id' => $this->proprietario_id,
            'proprietario' => $this->whenLoaded('proprietario', function () {
                return [
                    'id' => $this->proprietario->id,
                    'nome' => $this->proprietario->name,
                    'email' => $this->proprietario->email,
                    'telefone' => $this->proprietario->telefone,
                    'celular' => $this->proprietario->celular,
                ];
            }),
            'corretor_id' => $this->corretor_id,
            'corretor' => $this->whenLoaded('corretor', function () {
                return $this->corretor ? [
                    'id' => $this->corretor->id,
                    'nome' => $this->corretor->name,
                    'email' => $this->corretor->email,
                ] : null;
            }),
            'exclusividade' => $this->whenLoaded('detalhes', function () {
                return (bool) $this->detalhes->exclusividade;
            }),
            'data_inicio_exclusividade' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->data_inicio_exclusividade;
            }),
            'data_fim_exclusividade' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->data_fim_exclusividade;
            }),
            'comissao_percentual' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->comissao_percentual;
            }),
            'comissao_valor' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->comissao_valor;
            }),
            'matricula' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->matricula;
            }),
            'inscricao_municipal' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->inscricao_municipal;
            }),
            'anotacoes_privativas' => $this->whenLoaded('detalhes', function () {
                return $this->detalhes->anotacoes_privativas;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
