<?php

namespace Tests\Feature;

use App\Models\Imovel;
use App\Models\Cidade;
use App\Models\Bairro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImovelLocalizacaoTest extends TestCase
{
    /**
     * Testa a atualização da localização com apenas nome da cidade.
     *
     * @return void
     */
    public function testUpdateLocalizacaoComNomeCidade()
    {
        // Criar uma cidade para o teste
        $cidade = Cidade::create([
            'nome' => 'Cidade Teste',
            'uf' => 'MG'
        ]);

        // Criar um imóvel para o teste
        $imovel = Imovel::create([
            'tipo' => 'APARTAMENTO',
            'subtipo' => 'PADRAO',
            'finalidade' => 'VENDA',
            'status' => 'DISPONIVEL',
            'uf' => 'MG',
            'cidade' => 'Outra Cidade',
            'bairro' => 'Outro Bairro'
        ]);

        // Fazer a requisição para atualizar a localização
        $response = $this->json('PUT', "/api/imoveis/{$imovel->id}/etapas/localizacao", [
            'uf' => 'MG',
            'cidade' => 'Cidade Teste', // Nome da cidade existente
            'bairro' => 'Bairro Teste'
        ]);

        // Verificar se a resposta foi bem-sucedida
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Localização do imóvel atualizada com sucesso.'
        ]);

        // Verificar se o cidade_id foi preenchido corretamente
        $imovel->refresh();
        $this->assertEquals($cidade->id, $imovel->cidade_id);
    }

    /**
     * Testa a atualização da localização com apenas nome do bairro.
     *
     * @return void
     */
    public function testUpdateLocalizacaoComNomeBairro()
    {
        // Criar uma cidade para o teste
        $cidade = Cidade::create([
            'nome' => 'Cidade Teste 2',
            'uf' => 'SP'
        ]);

        // Criar um bairro para o teste
        $bairro = Bairro::create([
            'nome' => 'Bairro Teste 2',
            'cidade_id' => $cidade->id
        ]);

        // Criar um imóvel para o teste
        $imovel = Imovel::create([
            'tipo' => 'CASA',
            'subtipo' => 'PADRAO',
            'finalidade' => 'VENDA',
            'status' => 'DISPONIVEL',
            'uf' => 'SP',
            'cidade' => 'Outra Cidade',
            'bairro' => 'Outro Bairro'
        ]);

        // Fazer a requisição para atualizar a localização
        $response = $this->json('PUT', "/api/imoveis/{$imovel->id}/etapas/localizacao", [
            'uf' => 'SP',
            'cidade_id' => $cidade->id,
            'bairro' => 'Bairro Teste 2' // Nome do bairro existente
        ]);

        // Verificar se a resposta foi bem-sucedida
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Localização do imóvel atualizada com sucesso.'
        ]);

        // Verificar se o bairro_id foi preenchido corretamente
        $imovel->refresh();
        $this->assertEquals($bairro->id, $imovel->bairro_id);
    }

    /**
     * Testa a atualização da localização usando a UF atual do imóvel.
     *
     * @return void
     */
    public function testUpdateLocalizacaoComUFAtual()
    {
        // Criar uma cidade para o teste
        $cidade = Cidade::create([
            'nome' => 'Cidade Teste 3',
            'uf' => 'RJ'
        ]);

        // Criar um imóvel para o teste
        $imovel = Imovel::create([
            'tipo' => 'APARTAMENTO',
            'subtipo' => 'PADRAO',
            'finalidade' => 'VENDA',
            'status' => 'DISPONIVEL',
            'uf' => 'RJ',
            'cidade' => 'Outra Cidade',
            'bairro' => 'Outro Bairro'
        ]);

        // Fazer a requisição para atualizar a localização sem informar a UF
        $response = $this->json('PUT', "/api/imoveis/{$imovel->id}/etapas/localizacao", [
            'cidade' => 'Cidade Teste 3', // Nome da cidade existente
            'bairro' => 'Novo Bairro'
        ]);

        // Verificar se a resposta foi bem-sucedida
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Localização do imóvel atualizada com sucesso.'
        ]);

        // Verificar se o cidade_id foi preenchido corretamente
        $imovel->refresh();
        $this->assertEquals($cidade->id, $imovel->cidade_id);
    }
}
