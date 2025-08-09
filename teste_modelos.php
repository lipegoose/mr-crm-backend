<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar o aplicativo Lumen
$app = require_once __DIR__ . '/bootstrap/app.php';

// Inicializar o kernel do aplicativo
$app->boot();
$app->withFacades();
$app->withEloquent();

use App\Models\Imovel;
use App\Models\ImovelDetalhe;
use App\Models\Condominio;
use App\Models\Caracteristica;
use App\Models\Proximidade;

// Teste 1: Criar um imóvel com detalhes
echo "Criando imóvel de teste...\n";

$imovel = new Imovel();
$imovel->fill([
    'titulo' => 'Apartamento Teste',
    'tipo' => 'APARTAMENTO',
    'subtipo' => 'PADRAO',
    'status' => 'ATIVO',
    'condominio_id' => 1, // Condomínio exemplo criado pelo seeder
    'proprietario_id' => 1, // Admin
    'corretor_id' => 1, // Admin
    'area_total' => 120.00,
    'area_privativa' => 100.00,
    'quartos' => 3,
    'banheiros' => 2,
    'suites' => 1,
    'vagas' => 2,
    'valor_venda' => 500000.00,
    'valor_locacao' => 2500.00,
    'valor_condominio' => 800.00,
    'valor_iptu' => 1200.00,
    'aceita_financiamento' => true,
    'aceita_permuta' => false,
    'cep' => '30130110',
    'uf' => 'MG',
    'cidade' => 'Belo Horizonte',
    'bairro' => 'Centro',
    'logradouro' => 'Rua dos Testes',
    'numero' => '123',
    'complemento' => 'Apto 101',
    'mostrar_endereco_site' => true,
    'mostrar_valores_site' => true,
    'publicar_site' => true,
    'destaque_site' => true,
]);

$imovel->save();
echo "Imóvel criado com ID: {$imovel->id}\n";
echo "Código de referência gerado: {$imovel->codigo_referencia}\n";

// Teste 2: Criar detalhes do imóvel
$detalhes = new ImovelDetalhe();
$detalhes->fill([
    'id' => $imovel->id,
    'titulo_anuncio' => 'Excelente Apartamento no Centro',
    'mostrar_titulo' => true,
    'descricao' => 'Apartamento amplo com ótima localização, próximo a comércios e transporte público.',
    'mostrar_descricao' => true,
    'palavras_chave' => 'apartamento, centro, 3 quartos',
    'observacoes_internas' => 'Proprietário aceita negociar valor',
    'exclusividade' => true,
    'data_inicio_exclusividade' => now(),
    'data_fim_exclusividade' => now()->addMonths(3),
    'valor_comissao' => 5.00,
    'tipo_comissao' => 'PORCENTAGEM',
    'config_exibicao' => json_encode(['mostrar_mapa' => true, 'mostrar_tour' => false]),
]);

$detalhes->save();
echo "Detalhes do imóvel criados\n";

// Teste 3: Vincular características ao imóvel
$caracteristicas = Caracteristica::where('escopo', 'IMOVEL')
    ->whereIn('nome', ['Ar condicionado', 'Churrasqueira', 'Piscina'])
    ->limit(3)
    ->get();

foreach ($caracteristicas as $caracteristica) {
    $imovel->caracteristicas()->attach($caracteristica->id, [
        'created_by' => 1,
        'created_at' => now(),
    ]);
}

echo "Vinculadas " . $caracteristicas->count() . " características ao imóvel\n";

// Teste 4: Vincular proximidades ao imóvel
$proximidades = Proximidade::whereIn('nome', ['Shopping', 'Escola', 'Farmácia'])
    ->limit(3)
    ->get();

foreach ($proximidades as $proximidade) {
    $imovel->proximidades()->attach($proximidade->id, [
        'distancia_metros' => rand(100, 1000),
        'distancia_texto' => rand(1, 10) . ' min',
        'created_by' => 1,
        'created_at' => now(),
    ]);
}

echo "Vinculadas " . $proximidades->count() . " proximidades ao imóvel\n";

// Teste 5: Testar formatação de endereço
echo "Endereço formatado: " . $imovel->formatarEndereco() . "\n";

// Teste 6: Testar disponibilidade
echo "Disponível para venda: " . ($imovel->isDisponivelPara('VENDA') ? 'Sim' : 'Não') . "\n";
echo "Disponível para locação: " . ($imovel->isDisponivelPara('LOCACAO') ? 'Sim' : 'Não') . "\n";

echo "Testes concluídos com sucesso!\n";
