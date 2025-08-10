<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

class ProximidadesResource extends JsonResource
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
            'proximidades' => $this->whenLoaded('proximidades', function () {
                return $this->proximidades->map(function ($proximidade) {
                    return [
                        'id' => $proximidade->id,
                        'nome' => $proximidade->nome,
                        'descricao' => $proximidade->descricao,
                        'icone' => $proximidade->icone,
                        'categoria' => $proximidade->categoria,
                        'distancia_metros' => $proximidade->pivot->distancia_metros,
                        'distancia_texto' => $proximidade->pivot->distancia_texto,
                        'distancia_formatada' => $this->formatarDistancia($proximidade->pivot->distancia_metros, $proximidade->pivot->distancia_texto),
                    ];
                });
            }),
            'mostrar_proximidades' => (bool) ($this->detalhes->config_exibicao ? 
                json_decode($this->detalhes->config_exibicao)->mostrar_proximidades ?? true : true),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    /**
     * Formata a distância para exibição.
     *
     * @param int|null $distanciaMetros
     * @param string|null $distanciaTexto
     * @return string
     */
    protected function formatarDistancia($distanciaMetros, $distanciaTexto)
    {
        if ($distanciaTexto) {
            return $distanciaTexto;
        }
        
        if ($distanciaMetros === null) {
            return '';
        }
        
        if ($distanciaMetros < 1000) {
            return $distanciaMetros . ' m';
        }
        
        return number_format($distanciaMetros / 1000, 1, ',', '.') . ' km';
    }
}
