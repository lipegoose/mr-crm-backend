<?php

// Este script deve ser executado diretamente via PHP
// docker exec mrcrm-app php teste_processamento_null.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Http\Controllers\ImovelEtapasController;
use App\Models\Imovel;

// Criar uma classe de teste para acessar o método protegido
class TestController extends ImovelEtapasController {
    public function testarProcessarCidadeBairro($dados, $imovel) {
        return $this->processarCidadeBairro($dados, $imovel);
    }
}

// Criar um imóvel fictício para teste
$imovel = new Imovel();
$imovel->uf = 'MG';
$imovel->cidade = 'Belo Horizonte';
$imovel->bairro = 'Centro';
$imovel->cidade_id = 1;
$imovel->bairro_id = 2;

// Criar um controller para testar
$controller = new TestController();

echo "Iniciando testes para valores null...\n\n";

// Cenário 1: Payload com cidade=null
echo "Cenário 1: Payload com cidade=null\n";
$dados = [
    'uf' => 'MG',
    'cidade' => null,
    'bairro' => 'Savassi',
];

echo "Dados antes: " . json_encode($dados) . "\n";
$resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
echo "Dados depois: " . json_encode($resultado) . "\n\n";

// Cenário 2: Payload com bairro=null
echo "Cenário 2: Payload com bairro=null\n";
$dados = [
    'uf' => 'MG',
    'cidade' => 'Belo Horizonte',
    'bairro' => null,
];

echo "Dados antes: " . json_encode($dados) . "\n";
$resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
echo "Dados depois: " . json_encode($resultado) . "\n\n";

// Cenário 3: Payload com cidade=null e bairro=null
echo "Cenário 3: Payload com cidade=null e bairro=null\n";
$dados = [
    'uf' => 'MG',
    'cidade' => null,
    'bairro' => null,
];

echo "Dados antes: " . json_encode($dados) . "\n";
$resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
echo "Dados depois: " . json_encode($resultado) . "\n\n";

echo "Testes concluídos!\n";
