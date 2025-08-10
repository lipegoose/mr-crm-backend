<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class MedidasResource extends JsonResource
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
            'area_total' => $this->area_total,
            'area_privativa' => $this->area_privativa,
            'area_construida' => $this->area_construida,
            'unidade_medida' => $this->unidade_medida,
            'area_total_formatada' => $this->formatarArea($this->area_total),
            'area_privativa_formatada' => $this->formatarArea($this->area_privativa),
            'area_construida_formatada' => $this->formatarArea($this->area_construida),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    /**
     * Formata a área de acordo com a unidade de medida.
     *
     * @param float|null $area
     * @return string|null
     */
    protected function formatarArea($area)
    {
        if ($area === null) {
            return null;
        }
        
        $unidade = $this->unidade_medida ?? 'm2';
        $simbolo = $unidade === 'm2' ? 'm²' : ($unidade === 'ha' ? 'ha' : 'alq');
        
        return number_format($area, 2, ',', '.') . ' ' . $simbolo;
    }
}
