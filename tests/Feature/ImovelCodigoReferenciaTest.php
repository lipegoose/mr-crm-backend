<?php

namespace Tests\Feature;

use App\Models\Imovel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ImovelCodigoReferenciaTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar um usuário para autenticação
        $this->user = User::factory()->create([
            'email' => 'teste@mrcrm.com.br',
            'password' => bcrypt('senha123')
        ]);

        // Obter token JWT
        $response = $this->postJson('/api/auth/login', [
            'email' => 'teste@mrcrm.com.br',
            'password' => 'senha123'
        ]);

        $this->token = $response->json('access_token');
    }

    /**
     * Testa o fluxo completo de criação e atualização de um imóvel com foco no código de referência.
     *
     * @return void
     */
    public function testFluxoCodigoReferencia()
    {
        // 1. Iniciar um novo imóvel (rascunho)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/imoveis/iniciar');

        $response->assertStatus(201);
        $imovelId = $response->json('imovel.id');
        $codigoReferencia = $response->json('imovel.codigo_referencia');

        // Verificar se o código de referência foi gerado automaticamente
        $this->assertNotNull($codigoReferencia);

        // 2. Atualizar o imóvel sem alterar o código de referência
        $dadosAtualizacao = [
            'tipo' => 'APARTAMENTO',
            'subtipo' => 'PADRAO',
            'perfil' => 'RESIDENCIAL',
            'status' => 'RASCUNHO'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/imoveis/{$imovelId}", $dadosAtualizacao);

        $response->assertStatus(200);
        $this->assertEquals($codigoReferencia, $response->json('imovel.codigo_referencia'));

        // 3. Atualizar o tipo do imóvel para verificar se o código é regenerado
        $dadosAtualizacao = [
            'tipo' => 'CASA',
            'subtipo' => 'PADRAO',
            'perfil' => 'RESIDENCIAL',
            'status' => 'RASCUNHO'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/imoveis/{$imovelId}", $dadosAtualizacao);

        $response->assertStatus(200);
        $novoCodigoReferencia = $response->json('imovel.codigo_referencia');
        $this->assertNotEquals($codigoReferencia, $novoCodigoReferencia);

        // 4. Atualizar manualmente o código de referência
        $codigoPersonalizado = 'TESTE-123';
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/imoveis/{$imovelId}/codigo-referencia", [
            'codigo_referencia' => $codigoPersonalizado
        ]);

        $response->assertStatus(200);
        $this->assertEquals($codigoPersonalizado, $response->json('imovel.codigo_referencia'));

        // 5. Verificar se a flag codigo_referencia_editado foi definida
        $imovel = Imovel::find($imovelId);
        $this->assertTrue($imovel->codigo_referencia_editado);

        // 6. Atualizar o tipo novamente e verificar se o código personalizado é mantido
        $dadosAtualizacao = [
            'tipo' => 'TERRENO',
            'subtipo' => 'PADRAO',
            'perfil' => 'RESIDENCIAL',
            'status' => 'RASCUNHO'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/imoveis/{$imovelId}", $dadosAtualizacao);

        $response->assertStatus(200);
        $this->assertEquals($codigoPersonalizado, $response->json('imovel.codigo_referencia'));

        // 7. Atualizar o imóvel enviando um novo código de referência diretamente
        $outroCodigoPersonalizado = 'CUSTOM-456';
        $dadosAtualizacao = [
            'tipo' => 'TERRENO',
            'subtipo' => 'PADRAO',
            'perfil' => 'RESIDENCIAL',
            'status' => 'RASCUNHO',
            'codigo_referencia' => $outroCodigoPersonalizado
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/imoveis/{$imovelId}", $dadosAtualizacao);

        $response->assertStatus(200);
        $this->assertEquals($outroCodigoPersonalizado, $response->json('imovel.codigo_referencia'));

        // Verificar novamente a flag codigo_referencia_editado
        $imovel->refresh();
        $this->assertTrue($imovel->codigo_referencia_editado);
    }
}
