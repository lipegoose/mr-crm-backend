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
            'unidade_medida_area_total' => $this->unidade_medida_area_total,
            'area_privativa' => $this->area_privativa,
            'unidade_medida_area_privativa' => $this->unidade_medida_area_privativa,
            'area_construida' => $this->area_construida,
            'unidade_medida_area_construida' => $this->unidade_medida_area_construida,
            'area_total_formatada' => $this->formatarArea($this->area_total, $this->unidade_medida_area_total),
            'area_privativa_formatada' => $this->formatarArea($this->area_privativa, $this->unidade_medida_area_privativa),
            'area_construida_formatada' => $this->formatarArea($this->area_construida, $this->unidade_medida_area_construida),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    /**
     * Formata a área de acordo com a unidade de medida.
     *
     * @param float|null $area
     * @param string|null $unidade
     * @return string|null
     */
    protected function formatarArea($area, $unidade = null)
    {
        if ($area === null) {
            return null;
        }
        
        $unidade = $unidade ?? 'm²';
        $simbolo = $unidade === 'm²' ? 'm²' : 'ha';
        
        return number_format($area, 2, ',', '.') . ' ' . $simbolo;
    }
}
