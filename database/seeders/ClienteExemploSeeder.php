<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ClienteExemploSeeder extends Seeder
{
    public function run(): void
    {
        // Só roda se a tabela estiver vazia OU se a flag SEED_EXEMPLOS estiver habilitada
        $allow = (bool) filter_var(env('SEED_EXEMPLOS', false), FILTER_VALIDATE_BOOLEAN);
        $isEmpty = DB::table('clientes')->count() === 0;
        if (!($allow || $isEmpty)) {
            if (isset($this->command)) {
                $this->command->info('ClienteExemploSeeder: ignorado (sem flag e tabela não vazia).');
            }
            return;
        }

        $now = Carbon::now();
        $adminId = 1; // admin já existe

        // Criar cliente pessoa física de exemplo
        $clientePfId = DB::table('clientes')->insertGetId([
            'nome' => 'João da Silva',
            'tipo' => 'FISICA',
            'cpf_cnpj' => '123.456.789-00',
            'rg_ie' => 'MG-12.345.678',
            'email' => 'joao.silva@exemplo.com',
            'telefone' => '(31) 3333-4444',
            'celular' => '(31) 98888-7777',
            'whatsapp' => '(31) 98888-7777',
            'cep' => '30130110',
            'uf' => 'MG',
            'cidade' => 'Belo Horizonte',
            'bairro' => 'Centro',
            'logradouro' => 'Rua dos Clientes',
            'numero' => '123',
            'complemento' => 'Apto 101',
            'observacoes' => 'Cliente interessado em apartamentos no Centro e Savassi',
            'status' => 'ATIVO',
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $adminId,
            'updated_by' => null,
        ]);

        // Criar cliente pessoa jurídica de exemplo
        $clientePjId = DB::table('clientes')->insertGetId([
            'nome' => 'Imobiliária Exemplo LTDA',
            'tipo' => 'JURIDICA',
            'cpf_cnpj' => '12.345.678/0001-90',
            'rg_ie' => '001.234.567.890',
            'email' => 'contato@imobiliariaexemplo.com',
            'telefone' => '(31) 3333-5555',
            'celular' => '(31) 99999-8888',
            'whatsapp' => '(31) 99999-8888',
            'cep' => '30140110',
            'uf' => 'MG',
            'cidade' => 'Belo Horizonte',
            'bairro' => 'Savassi',
            'logradouro' => 'Avenida do Contorno',
            'numero' => '5000',
            'complemento' => 'Sala 301',
            'observacoes' => 'Parceiro para intermediação de negócios',
            'status' => 'ATIVO',
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $adminId,
            'updated_by' => null,
        ]);

        if (isset($this->command)) {
            $this->command->info("Cliente PF exemplo criado (ID={$clientePfId})");
            $this->command->info("Cliente PJ exemplo criado (ID={$clientePjId})");
        }
    }
}
