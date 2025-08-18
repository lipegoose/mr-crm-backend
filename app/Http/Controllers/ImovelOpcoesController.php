<?php

namespace App\Http\Controllers;

use App\Models\Caracteristica;
use App\Models\Proximidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImovelOpcoesController extends Controller
{
    /**
     * Lista os tipos de imóveis disponíveis.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTipos()
    {
        try {
            $tipos = [
                'APARTAMENTO',
                'CASA',
                'COMERCIAL',
                'TERRENO',
                'RURAL',
                'INDUSTRIAL',
            ];
            
            return response()->json([
                'success' => true,
                'data' => $tipos,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de imóveis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de imóveis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Lista os subtipos de imóveis disponíveis para um tipo específico.
     *
     * @param string $tipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubtipos($tipo)
    {
        try {
            $subtipos = [];
            
            switch (strtoupper($tipo)) {
                case 'APARTAMENTO':
                    $subtipos = [
                        'PADRAO',
                        'COBERTURA',
                        'DUPLEX',
                        'TRIPLEX',
                        'KITNET',
                        'FLAT',
                        'LOFT',
                        'STUDIO',
                    ];
                    break;
                case 'CASA':
                    $subtipos = [
                        'PADRAO',
                        'SOBRADO',
                        'CONDOMINIO',
                        'TERREA',
                        'VILA',
                        'GERMINADA',
                        'KITNET',
                    ];
                    break;
                case 'COMERCIAL':
                    $subtipos = [
                        'SALA',
                        'CONJUNTO',
                        'PREDIO',
                        'LOJA',
                        'GALPAO',
                        'HOTEL',
                        'POUSADA',
                    ];
                    break;
                case 'TERRENO':
                    $subtipos = [
                        'PADRAO',
                        'CONDOMINIO',
                        'LOTEAMENTO',
                        'AREA',
                    ];
                    break;
                case 'RURAL':
                    $subtipos = [
                        'FAZENDA',
                        'SITIO',
                        'CHACARA',
                        'HARAS',
                    ];
                    break;
                case 'INDUSTRIAL':
                    $subtipos = [
                        'GALPAO',
                        'AREA',
                        'PREDIO',
                    ];
                    break;
                default:
                    $subtipos = [];
            }
            
            return response()->json([
                'success' => true,
                'data' => $subtipos,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar subtipos de imóveis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar subtipos de imóveis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Lista os tipos de negócio disponíveis.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTiposNegocio()
    {
        try {
            $tiposNegocio = [
                'VENDA',
                'ALUGUEL',
                'TEMPORADA',
                'VENDA_ALUGUEL',
            ];
            
            return response()->json([
                'success' => true,
                'data' => $tiposNegocio,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de negócio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de negócio.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Lista as características disponíveis para um escopo específico (IMOVEL ou CONDOMINIO).
     *
     * @param string $escopo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaracteristicas($escopo)
    {
        try {
            $escopo = strtoupper($escopo);
            
            if (!in_array($escopo, ['IMOVEL', 'CONDOMINIO'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escopo inválido. Use IMOVEL ou CONDOMINIO.',
                ], 400);
            }
            
            $caracteristicas = Caracteristica::where('escopo', $escopo)
                ->orderBy('nome')
                ->get(['id', 'nome', 'escopo']);
            
            return response()->json([
                'success' => true,
                'data' => $caracteristicas,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar características: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar características.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Lista as proximidades disponíveis.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProximidades()
    {
        try {
            $proximidades = Proximidade::orderBy('nome')
                ->get(['id', 'nome', 'sistema']);
            
            return response()->json([
                'success' => true,
                'data' => $proximidades,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar proximidades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar proximidades.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Lista os portais disponíveis para integração.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPortais()
    {
        try {
            $portais = [
                ['id' => 'vivareal', 'nome' => 'Viva Real'],
                ['id' => 'zap', 'nome' => 'ZAP Imóveis'],
                ['id' => 'imovelweb', 'nome' => 'Imóvel Web'],
                ['id' => 'quintoandar', 'nome' => 'Quinto Andar'],
                ['id' => 'olx', 'nome' => 'OLX'],
                ['id' => 'chaves_na_mao', 'nome' => 'Chaves na Mão'],
            ];
            
            return response()->json([
                'success' => true,
                'data' => $portais,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar portais: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar portais.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Lista as redes sociais disponíveis para integração.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRedesSociais()
    {
        try {
            $redesSociais = [
                ['id' => 'facebook', 'nome' => 'Facebook'],
                ['id' => 'instagram', 'nome' => 'Instagram'],
                ['id' => 'twitter', 'nome' => 'Twitter'],
                ['id' => 'linkedin', 'nome' => 'LinkedIn'],
                ['id' => 'whatsapp', 'nome' => 'WhatsApp'],
                ['id' => 'telegram', 'nome' => 'Telegram'],
            ];
            
            return response()->json([
                'success' => true,
                'data' => $redesSociais,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar redes sociais: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar redes sociais.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
