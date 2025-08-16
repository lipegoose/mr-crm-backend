<?php

namespace Database\Seeders;

use App\Models\Cidade;
use App\Models\Bairro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CidadesBairrosSeeder extends Seeder
{
    /**
     * Executa o seeder de cidades e bairros.
     * Implementa upsert idempotente para evitar duplicações.
     *
     * @return void
     */
    public function run(): void
    {
        // Flag para determinar se deve carregar dados de exemplo
        $carregarExemplos = env('SEED_EXEMPLOS', false);
        
        // Verificar se já existem cidades cadastradas
        $cidadesExistentes = Cidade::count();
        
        // Só carrega dados de exemplo se a flag estiver ativa ou se não houver cidades cadastradas
        if ($carregarExemplos || $cidadesExistentes === 0) {
            $this->command->info('Iniciando seed de cidades e bairros...');
            
            // Iniciar transação para garantir consistência dos dados
            DB::beginTransaction();
            
            try {
                // Cadastrar cidades e bairros de exemplo
                $this->seedCidadesBairrosExemplo();
                
                DB::commit();
                $this->command->info('Seed de cidades e bairros concluído com sucesso!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao executar seed de cidades e bairros: ' . $e->getMessage());
                $this->command->error('Erro ao executar seed de cidades e bairros: ' . $e->getMessage());
            }
        } else {
            $this->command->info('Pulando seed de cidades e bairros. Para forçar, defina SEED_EXEMPLOS=true no .env');
        }
    }
    
    /**
     * Cadastra cidades e bairros de exemplo.
     *
     * @return void
     */
    private function seedCidadesBairrosExemplo(): void
    {
        // Dados de exemplo para cidades e bairros
        $dadosCidades = [
            // Minas Gerais
            [
                'nome' => 'Belo Horizonte',
                'uf' => 'MG',
                'bairros' => [
                    'Centro', 'Savassi', 'Lourdes', 'Funcionários', 'Buritis',
                    'Belvedere', 'Mangabeiras', 'Santa Efigênia', 'Santo Antônio',
                    'Sion'
                ]
            ],
            [
                'nome' => 'Nova Lima',
                'uf' => 'MG',
                'bairros' => [
                    'Vila da Serra', 'Vale dos Cristais', 'Jardim Canadá', 'Alphaville',
                    'Vale do Sereno'
                ]
            ],
            [
                'nome' => 'Contagem',
                'uf' => 'MG',
                'bairros' => [
                    'Eldorado', 'Industrial', 'Cidade Industrial', 'Riacho'
                ]
            ],
            
            // São Paulo
            [
                'nome' => 'São Paulo',
                'uf' => 'SP',
                'bairros' => [
                    'Jardins', 'Moema', 'Itaim Bibi', 'Vila Olímpia', 'Morumbi',
                    'Pinheiros', 'Higienópolis', 'Consolação', 'Vila Mariana'
                ]
            ],
            [
                'nome' => 'Campinas',
                'uf' => 'SP',
                'bairros' => [
                    'Cambuí', 'Nova Campinas', 'Taquaral', 'Barão Geraldo'
                ]
            ],
            
            // Rio de Janeiro
            [
                'nome' => 'Rio de Janeiro',
                'uf' => 'RJ',
                'bairros' => [
                    'Copacabana', 'Ipanema', 'Leblon', 'Barra da Tijuca', 'Botafogo',
                    'Flamengo', 'Tijuca', 'Recreio dos Bandeirantes'
                ]
            ],
            [
                'nome' => 'Niterói',
                'uf' => 'RJ',
                'bairros' => [
                    'Icaraí', 'São Francisco', 'Charitas', 'Itacoatiara'
                ]
            ]
        ];
        
        // Contador de itens processados
        $cidadesCriadas = 0;
        $cidadesAtualizadas = 0;
        $bairrosCriados = 0;
        $bairrosAtualizados = 0;
        
        // Processar cada cidade e seus bairros
        foreach ($dadosCidades as $dadosCidade) {
            // Buscar ou criar a cidade
            $cidade = Cidade::firstOrNew([
                'nome' => $dadosCidade['nome'],
                'uf' => $dadosCidade['uf']
            ]);
            
            $isNovaCidade = !$cidade->exists;
            
            // Se for nova, definir campos de auditoria
            if ($isNovaCidade) {
                $cidade->created_by = 1; // Usuário sistema
                $cidadesCriadas++;
            } else {
                $cidadesAtualizadas++;
            }
            
            // Salvar a cidade
            $cidade->save();
            
            // Processar bairros da cidade
            foreach ($dadosCidade['bairros'] as $nomeBairro) {
                $bairro = Bairro::firstOrNew([
                    'nome' => $nomeBairro,
                    'cidade_id' => $cidade->id
                ]);
                
                $isNovoBairro = !$bairro->exists;
                
                // Se for novo, definir campos de auditoria
                if ($isNovoBairro) {
                    $bairro->created_by = 1; // Usuário sistema
                    $bairrosCriados++;
                } else {
                    $bairrosAtualizados++;
                }
                
                // Salvar o bairro
                $bairro->save();
            }
        }
        
        // Exibir estatísticas
        $this->command->info("Cidades: {$cidadesCriadas} criadas, {$cidadesAtualizadas} atualizadas");
        $this->command->info("Bairros: {$bairrosCriados} criados, {$bairrosAtualizados} atualizados");
    }
}
