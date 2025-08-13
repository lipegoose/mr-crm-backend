<?php

require_once __DIR__ . '/vendor/autoload.php';

// Criar uma instância do modelo Imovel
$imovel = new App\Models\Imovel();

// Testar o mutator do campo terreno
$imovel->terreno = 'declive';
echo "Valor após mutator: " . $imovel->getAttributes()['terreno'] . PHP_EOL;

// Testar com outros valores
$imovel->terreno = 'plano';
echo "Valor após mutator (plano): " . $imovel->getAttributes()['terreno'] . PHP_EOL;

$imovel->terreno = 'ACLIVE';
echo "Valor após mutator (ACLIVE): " . $imovel->getAttributes()['terreno'] . PHP_EOL;

?>
