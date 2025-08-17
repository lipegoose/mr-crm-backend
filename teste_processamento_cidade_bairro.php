<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Http\Controllers\ImovelEtapasController;
use App\Models\Imovel;
use App\Models\Cidade;
use App\Models\Bairro;

// Classe de teste que estende o controller para acessar o método protegido
class TestImovelEtapasController extends ImovelEtapasController
{
    // Torna o método público para testes
    public function testarProcessarCidadeBairro(array $dados, $imovel)
    {
        return $this->processarCidadeBairro($dados, $imovel);
    }
}

// Função para testar o método processarCidadeBairro
function testarProcessamento() {
    // Criar um controller para testar
    $controller = new TestImovelEtapasController();
    
    // Criar um imóvel fictício para teste
    $imovel = new Imovel();
    $imovel->uf = 'MG';
    $imovel->cidade = 'Belo Horizonte';
    $imovel->bairro = 'Centro';
    
    echo "Iniciando testes de processamento de cidade e bairro...\n\n";
    
    // Cenário 1: Payload com cidade (nome) mas sem cidade_id
    echo "Cenário 1: Payload com cidade (nome) mas sem cidade_id\n";
    $dados = [
        'uf' => 'MG',
        'cidade' => 'Belo Horizonte',
        'bairro' => 'Savassi',
    ];
    
    echo "Dados antes: " . json_encode($dados) . "\n";
    $resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
    echo "Dados depois: " . json_encode($resultado) . "\n\n";
    
    // Cenário 2: Payload sem UF, usando UF do imóvel
    echo "Cenário 2: Payload sem UF, usando UF do imóvel\n";
    $dados = [
        'cidade' => 'Belo Horizonte',
        'bairro' => 'Funcionários',
    ];
    
    echo "Dados antes: " . json_encode($dados) . "\n";
    $resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
    echo "Dados depois: " . json_encode($resultado) . "\n\n";
    
    // Cenário 3: Payload com cidade_id mas sem bairro_id
    echo "Cenário 3: Payload com cidade_id mas sem bairro_id\n";
    $dados = [
        'cidade_id' => 1, // Assumindo que existe uma cidade com ID 1
        'bairro' => 'Centro',
    ];
    
    echo "Dados antes: " . json_encode($dados) . "\n";
    $resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
    echo "Dados depois: " . json_encode($resultado) . "\n\n";
    
    // Cenário 4: Payload com cidade e bairro, sem UF e sem IDs
    echo "Cenário 4: Payload com cidade e bairro, sem UF e sem IDs\n";
    $dados = [
        'cidade' => 'Belo Horizonte',
        'bairro' => 'Lourdes',
    ];
    
    echo "Dados antes: " . json_encode($dados) . "\n";
    $resultado = $controller->testarProcessarCidadeBairro($dados, $imovel);
    echo "Dados depois: " . json_encode($resultado) . "\n\n";
    
    echo "Testes concluídos!\n";
}

// Executar os testes
testarProcessamento();
