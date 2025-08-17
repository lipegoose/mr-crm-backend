<?php

// Este script deve ser executado diretamente via PHP
// docker exec mrcrm-app php teste_manual_localizacao.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Http\Controllers\ImovelEtapasController;
use App\Models\Imovel;
use App\Models\Cidade;
use App\Models\Bairro;
use Illuminate\Http\Request;

// Buscar um imóvel existente para teste
$imovel = Imovel::first();

if (!$imovel) {
    echo "Nenhum imóvel encontrado para teste.\n";
    return;
}

echo "Imóvel encontrado: ID {$imovel->id}, UF: {$imovel->uf}, Cidade: {$imovel->cidade}, Bairro: {$imovel->bairro}\n";
echo "cidade_id: " . ($imovel->cidade_id ?? 'null') . ", bairro_id: " . ($imovel->bairro_id ?? 'null') . "\n\n";

// Buscar ou criar uma cidade para teste
$cidade = Cidade::firstOrCreate(
    ['nome' => 'Cidade Teste', 'uf' => $imovel->uf],
    ['nome' => 'Cidade Teste', 'uf' => $imovel->uf]
);

echo "Cidade para teste: ID {$cidade->id}, Nome: {$cidade->nome}, UF: {$cidade->uf}\n\n";

// Criar um controller para testar
$controller = new ImovelEtapasController();

// Criar uma classe de teste para acessar o método protegido
class TestController extends ImovelEtapasController {
    public function testarProcessarCidadeBairro($dados, $imovel) {
        return $this->processarCidadeBairro($dados, $imovel);
    }
}

$testController = new TestController();

// Cenário 1: Payload com cidade (nome) mas sem cidade_id
echo "Cenário 1: Payload com cidade (nome) mas sem cidade_id\n";
$dados = [
    'uf' => $imovel->uf,
    'cidade' => 'Cidade Teste',
    'bairro' => 'Bairro Teste',
];

echo "Dados antes: " . json_encode($dados) . "\n";
$resultado = $testController->testarProcessarCidadeBairro($dados, $imovel);
echo "Dados depois: " . json_encode($resultado) . "\n\n";

// Cenário 2: Payload sem UF, usando UF do imóvel
echo "Cenário 2: Payload sem UF, usando UF do imóvel\n";
$dados = [
    'cidade' => 'Cidade Teste',
    'bairro' => 'Outro Bairro',
];

echo "Dados antes: " . json_encode($dados) . "\n";
$resultado = $testController->testarProcessarCidadeBairro($dados, $imovel);
echo "Dados depois: " . json_encode($resultado) . "\n\n";

echo "Testes concluídos!\n";
