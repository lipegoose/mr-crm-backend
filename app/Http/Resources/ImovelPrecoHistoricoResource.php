<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ImovelPrecoHistoricoResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $hoje = Carbon::today();
        $vigente = $this->data_fim === null || $this->data_fim->greaterThanOrEqualTo($hoje);
        
        return [
            'id' => $this->id,
            'imovel_id' => $this->imovel_id,
            'tipo_negocio' => $this->tipo_negocio,
            'valor' => $this->valor,
            'valor_formatado' => 'R$ ' . number_format($this->valor, 2, ',', '.'),
            'data_inicio' => $this->data_inicio->format('Y-m-d'),
            'data_inicio_formatada' => $this->data_inicio->format('d/m/Y'),
            'data_fim' => $this->data_fim ? $this->data_fim->format('Y-m-d') : null,
            'data_fim_formatada' => $this->data_fim ? $this->data_fim->format('d/m/Y') : null,
            'periodo_formatado' => $this->periodo_formatado,
            'motivo' => $this->motivo,
            'observacao' => $this->observacao,
            'vigente' => $vigente,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'usuario_criacao' => $this->whenLoaded('criadoPor', function () {
                return [
                    'id' => $this->criadoPor->id,
                    'nome' => $this->criadoPor->nome,
                ];
            }),
            'usuario_atualizacao' => $this->whenLoaded('atualizadoPor', function () {
                return [
                    'id' => $this->atualizadoPor->id,
                    'nome' => $this->atualizadoPor->nome,
                ];
            }),
        ];
    }
}
