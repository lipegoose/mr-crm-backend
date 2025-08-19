<?php

namespace Tests\Feature;

use App\Models\Imovel;
use App\Models\ImovelDetalhe;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ImovelDetalhesTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar um usuário para autenticação
        $this->user = User::factory()->create();
        
        // Autenticar o usuário
        $this->be($this->user);
    }

    /** @test */
    public function pode_criar_imovel_com_detalhes()
    {
        // Criar um imóvel via API
        $this->post('/api/imoveis/iniciar');
        
        $this->assertResponseStatus(201);
        $responseData = json_decode($this->response->getContent(), true);
        $imovelId = $responseData['imovel']['id'];
        
        // Verificar se o imóvel foi criado
        $this->seeInDatabase('imoveis', [
            'id' => $imovelId
        ]);
        
        // Verificar se os detalhes foram criados com imovel_id correto
        $this->seeInDatabase('imoveis_detalhes', [
            'imovel_id' => $imovelId
        ]);
        
        // Verificar o relacionamento
        $imovel = Imovel::find($imovelId);
        $this->assertNotNull($imovel->detalhes);
        $this->assertEquals($imovelId, $imovel->detalhes->imovel_id);
    }

    /** @test */
    public function pode_atualizar_detalhes_do_imovel()
    {
        // Criar um imóvel via API
        $this->post('/api/imoveis/iniciar');
        $responseData = json_decode($this->response->getContent(), true);
        $imovelId = $responseData['imovel']['id'];
        
        // Atualizar os detalhes do imóvel
        $dadosAtualizacao = [
            'detalhes' => [
                'titulo_anuncio' => 'Título de teste',
                'descricao' => 'Descrição de teste'
            ]
        ];
        
        $this->put("/api/imoveis/{$imovelId}", $dadosAtualizacao);
        $this->assertResponseStatus(200);
        
        // Verificar se os detalhes foram atualizados
        $this->seeInDatabase('imoveis_detalhes', [
            'imovel_id' => $imovelId,
            'titulo_anuncio' => 'Título de teste',
            'descricao' => 'Descrição de teste'
        ]);
    }

    /** @test */
    public function pode_duplicar_imovel_com_detalhes()
    {
        // Criar um imóvel via API
        $this->post('/api/imoveis/iniciar');
        $responseData = json_decode($this->response->getContent(), true);
        $imovelId = $responseData['imovel']['id'];
        
        // Atualizar os detalhes do imóvel original
        $dadosAtualizacao = [
            'detalhes' => [
                'titulo_anuncio' => 'Título original',
                'descricao' => 'Descrição original'
            ]
        ];
        
        $this->put("/api/imoveis/{$imovelId}", $dadosAtualizacao);
        
        // Duplicar o imóvel
        $this->post("/api/imoveis/{$imovelId}/duplicar");
        $this->assertResponseStatus(201);
        
        $duplicarData = json_decode($this->response->getContent(), true);
        $novoImovelId = $duplicarData['imovel']['id'];
        
        // Verificar se o novo imóvel foi criado
        $this->seeInDatabase('imoveis', [
            'id' => $novoImovelId
        ]);
        
        // Verificar se os detalhes foram duplicados com imovel_id correto
        $this->seeInDatabase('imoveis_detalhes', [
            'imovel_id' => $novoImovelId,
            'titulo_anuncio' => 'Título original',
            'descricao' => 'Descrição original'
        ]);
        
        // Verificar o relacionamento
        $novoImovel = Imovel::find($novoImovelId);
        $this->assertNotNull($novoImovel->detalhes);
        $this->assertEquals($novoImovelId, $novoImovel->detalhes->imovel_id);
    }

    /** @test */
    public function pode_atualizar_detalhes_via_etapas()
    {
        // Criar um imóvel via API
        $this->post('/api/imoveis/iniciar');
        $responseData = json_decode($this->response->getContent(), true);
        $imovelId = $responseData['imovel']['id'];
        
        // Atualizar os detalhes do imóvel via etapas
        $dadosDescricao = [
            'titulo_anuncio' => 'Título via etapas',
            'descricao' => 'Descrição via etapas',
            'palavras_chave' => 'teste, etapas'
        ];
        
        $this->put("/api/imoveis/{$imovelId}/etapas/descricao", $dadosDescricao);
        $this->assertResponseStatus(200);
        
        // Verificar se os detalhes foram atualizados
        $this->seeInDatabase('imoveis_detalhes', [
            'imovel_id' => $imovelId,
            'titulo_anuncio' => 'Título via etapas',
            'descricao' => 'Descrição via etapas',
            'palavras_chave' => 'teste, etapas'
        ]);
    }
}
