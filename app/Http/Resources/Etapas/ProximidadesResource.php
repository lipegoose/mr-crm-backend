<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso para a etapa de Proximidades
 * 
 * Atualizado para MVP: funcionamento simplificado sem distância, apenas marcação de proximidades
 * Código relacionado à distância mantido comentado para implementação futura
 */
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
                    // Versão MVP: Sem informações de distância
                    $dados = [
                        'id' => $proximidade->id,
                        'nome' => $proximidade->nome,
                        'sistema' => $proximidade->sistema,
                    ];
                    
                    // Adicionar informações de distância se existirem (para compatibilidade)
                    if (isset($proximidade->pivot->distancia_metros)) {
                        $dados['distancia_metros'] = $proximidade->pivot->distancia_metros;
                    }
                    
                    if (isset($proximidade->pivot->distancia_texto)) {
                        $dados['distancia_texto'] = $proximidade->pivot->distancia_texto;
                        $dados['distancia_formatada'] = $this->formatarDistancia(
                            $proximidade->pivot->distancia_metros ?? null, 
                            $proximidade->pivot->distancia_texto
                        );
                    }
                    
                    return $dados;
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
