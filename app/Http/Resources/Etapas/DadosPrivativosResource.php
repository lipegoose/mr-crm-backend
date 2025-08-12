<?php

namespace App\Http\Resources\Etapas;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class DadosPrivativosResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Obtém os detalhes do imóvel
        $detalhes = $this->detalhes;

        return [
            'id' => $this->id,
            'matricula' => $detalhes->matricula ?? null,
            'inscricao_municipal' => $detalhes->inscricao_municipal ?? null,
            'inscricao_estadual' => $detalhes->inscricao_estadual ?? null,
            'valor_comissao' => $detalhes->valor_comissao ?? null,
            'tipo_comissao' => $detalhes->tipo_comissao ?? null,
            'exclusividade' => $detalhes->exclusividade ?? false,
            'data_inicio_exclusividade' => $detalhes->data_inicio_exclusividade ? $detalhes->data_inicio_exclusividade->format('Y-m-d') : null,
            'data_fim_exclusividade' => $detalhes->data_fim_exclusividade ? $detalhes->data_fim_exclusividade->format('Y-m-d') : null,
            'observacoes_privadas' => $detalhes->observacoes_privadas ?? null,
            'corretor_id' => $this->corretor_id,
            'corretor' => $this->when($this->corretor_id && $this->corretor, function () {
                return [
                    'id' => $this->corretor->id,
                    'nome' => $this->corretor->nome,
                    'email' => $this->corretor->email,
                    'avatar' => $this->corretor->avatar_url,
                ];
            }),
            'exclusividade_vigente' => $detalhes && $detalhes->exclusividade ? $detalhes->isExclusividadeVigente() : false,
            'comissao_formatada' => $detalhes && $detalhes->valor_comissao ? $detalhes->getComissaoFormatada() : null,
        ];
    }
}
