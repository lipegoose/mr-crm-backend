<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Imovel;
use App\Models\ImovelDetalhe;
use App\Models\Condominio;
use App\Models\Caracteristica;
use App\Models\Proximidade;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestarModelos extends Command
{
    /**
     * Nome do comando.
     *
     * @var string
     */
    protected $signature = 'modelos:testar';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Testa os modelos Eloquent principais';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('Iniciando testes dos modelos Eloquent...');

        try {
            DB::beginTransaction();
            
            // Teste 1: Criar um imóvel em modo rascunho (primeira etapa do wizard)
            $this->info('1. Criando imóvel em modo rascunho (etapa 1 do wizard)...');

            // Campos mínimos na primeira etapa
            // Incluindo campos obrigatórios (perfil e situacao) devido às restrições do banco de dados
            $imovel = new Imovel();
            $imovel->fill([
                'tipo' => 'APARTAMENTO',
                'proprietario_id' => 1, // Cliente 1 ou Cliente Exemplo
                'perfil' => 'RESIDENCIAL', // Campo obrigatório no banco de dados
                'situacao' => 'RASCUNHO', // Campo obrigatório no banco de dados
                // Status RASCUNHO será definido automaticamente pelo modelo
            ]);

            $imovel->save();
            
            $this->info("Imóvel rascunho criado com ID: {$imovel->id}");
            $this->info("Código de referência gerado: {$imovel->codigo_referencia}");
            $this->info("Status inicial: {$imovel->status}");
            
            // Teste 2: Atualizar imóvel rascunho (segunda etapa do wizard)
            $this->info('\n2. Atualizando imóvel rascunho (etapa 2 do wizard)...');
            
            $imovel->fill([
                'titulo' => 'Apartamento Teste',
                'tipo' => 'APARTAMENTO',
                'subtipo' => 'PADRAO',
                'perfil' => 'RESIDENCIAL',
                'situacao' => 'PRONTO',
                'condominio_id' => 1, // Condomínio exemplo criado pelo seeder
                'proprietario_id' => 1, // Admin
                'corretor_id' => 1, // Admin
            ]);
            
            $imovel->save();
            $this->info("Imóvel atualizado na etapa 2");
            
            // Teste 3: Atualizar imóvel rascunho (terceira etapa do wizard - dados físicos)
            $this->info('\n3. Atualizando imóvel rascunho (etapa 3 do wizard - dados físicos)...');
            
            $imovel->fill([
                'area_total' => 120.00,
                'area_privativa' => 100.00,
                'dormitorios' => 3,
                'banheiros' => 2,
                'suites' => 1,
                'vagas' => 2,
            ]);
            
            $imovel->save();
            $this->info("Imóvel atualizado na etapa 3");
            
            // Teste 4: Atualizar imóvel rascunho (quarta etapa do wizard - valores)
            $this->info('\n4. Atualizando imóvel rascunho (etapa 4 do wizard - valores)...');
            
            $imovel->fill([
                'valor_venda' => 500000.00,
                'valor_locacao' => 2500.00,
                'valor_condominio' => 800.00,
                'valor_iptu' => 1200.00,
                'aceita_financiamento' => true,
                'aceita_permuta' => false,
            ]);
            
            $imovel->save();
            $this->info("Imóvel atualizado na etapa 4");
            
            // Teste 5: Finalizar cadastro do imóvel (ativar)
            $this->info('\n5. Finalizando cadastro do imóvel (ativando)...');
            
            $imovel->fill([
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
                'status' => 'ATIVO', // Mudar de RASCUNHO para ATIVO
            ]);
            
            $imovel->save();
            $this->info("Imóvel finalizado com ID: {$imovel->id}");
            $this->info("Status final: {$imovel->status}");

            // Teste 6: Criar detalhes do imóvel em etapas
            $this->info('\n6. Criando detalhes do imóvel em etapas...');
            
            // Etapa 1: Criar detalhes básicos
            $this->info('6.1. Criando detalhes básicos...');
            $detalhes = new ImovelDetalhe();
            $detalhes->fill([
                'id' => $imovel->id,
                'titulo_anuncio' => 'Excelente Apartamento no Centro',
                'mostrar_titulo' => true,
            ]);

            $detalhes->save();
            $this->info("Detalhes básicos criados");
            
            // Etapa 2: Adicionar descrição e palavras-chave
            $this->info('6.2. Adicionando descrição e palavras-chave...');
            $detalhes->fill([
                'descricao' => 'Apartamento amplo com ótima localização, próximo a comércios e transporte público.',
                'mostrar_descricao' => true,
                'palavras_chave' => 'apartamento, centro, 3 quartos',
            ]);
            
            $detalhes->save();
            $this->info("Descrição e palavras-chave adicionadas");
            
            // Etapa 3: Adicionar informações de exclusividade e comissão
            $this->info('6.3. Adicionando informações de exclusividade e comissão...');
            $detalhes->fill([
                'observacoes_internas' => 'Proprietário aceita negociar valor',
                'exclusividade' => true,
                'data_inicio_exclusividade' => Carbon::now(),
                'data_fim_exclusividade' => Carbon::now()->addMonths(3),
                'valor_comissao' => 5.00,
                'tipo_comissao' => 'PORCENTAGEM',
                'config_exibicao' => json_encode(['mostrar_mapa' => true, 'mostrar_tour' => false]),
            ]);
            
            $detalhes->save();
            $this->info("Detalhes do imóvel completos");

            // Teste 3: Vincular características ao imóvel
            $this->info('3. Vinculando características ao imóvel...');
            
            $caracteristicas = Caracteristica::where('escopo', 'IMOVEL')
                ->whereIn('nome', ['Ar condicionado', 'Churrasqueira', 'Piscina'])
                ->limit(3)
                ->get();

            foreach ($caracteristicas as $caracteristica) {
                $imovel->caracteristicas()->attach($caracteristica->id, [
                    'created_by' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $this->info("Vinculadas " . $caracteristicas->count() . " características ao imóvel");

            // Teste 4: Vincular proximidades ao imóvel
            $this->info('4. Vinculando proximidades ao imóvel...');
            
            $proximidades = Proximidade::whereIn('nome', ['Shopping', 'Escola', 'Farmácia'])
                ->limit(3)
                ->get();

            foreach ($proximidades as $proximidade) {
                $imovel->proximidades()->attach($proximidade->id, [
                    'distancia_metros' => rand(100, 1000),
                    'distancia_texto' => rand(1, 10) . ' min',
                    'created_by' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $this->info("Vinculadas " . $proximidades->count() . " proximidades ao imóvel");

            // Teste 5: Testar formatação de endereço
            $this->info('5. Testando formatação de endereço...');
            $this->info("Endereço formatado: " . $imovel->formatarEndereco());

            // Teste 6: Testar disponibilidade
            $this->info('6. Testando disponibilidade...');
            $this->info("Disponível para venda: " . ($imovel->isDisponivelPara('VENDA') ? 'Sim' : 'Não'));
            $this->info("Disponível para locação: " . ($imovel->isDisponivelPara('LOCACAO') ? 'Sim' : 'Não'));

            // Teste 7: Testar condomínio
            $this->info('7. Testando condomínio...');
            $condominio = Condominio::find(1);
            if ($condominio) {
                $this->info("Condomínio: " . $condominio->nome);
                $this->info("Endereço do condomínio: " . $condominio->formatarEndereco());
                $this->info("Características do condomínio: " . $condominio->caracteristicas()->count());
            } else {
                $this->error("Condomínio não encontrado");
            }
            
            // Tudo ok, fazemos rollback para não deixar dados de teste no banco
            DB::rollBack();
            $this->info('Testes concluídos com sucesso! (Rollback executado para não deixar dados de teste)');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro durante os testes: ' . $e->getMessage());
            $this->error('Arquivo: ' . $e->getFile() . ' (Linha: ' . $e->getLine() . ')');
        }
    }
}
