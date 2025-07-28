<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin {--name= : Nome do administrador} {--email= : Email do administrador} {--password= : Senha do administrador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria o primeiro usuário administrador do sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Criando Usuário Administrador ===');

        // Verificar se já existe algum usuário
        if (User::count() > 0) {
            $this->warn('Já existem usuários no sistema!');
            if (!$this->confirm('Deseja continuar mesmo assim?')) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        // Coletar dados do usuário
        $name = $this->option('name') ?: $this->ask('Nome do administrador');
        $email = $this->option('email') ?: $this->ask('Email do administrador');
        $password = $this->option('password') ?: $this->secret('Senha do administrador');

        // Validar dados
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $this->error('Dados inválidos:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("- $error");
            }
            return 1;
        }

        try {
            // Criar usuário
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'status' => true,
            ]);

            $this->info('✅ Usuário administrador criado com sucesso!');
            $this->info("ID: {$user->id}");
            $this->info("Nome: {$user->name}");
            $this->info("Email: {$user->email}");
            $this->info("Status: " . ($user->status ? 'Ativo' : 'Inativo'));
            $this->info("Criado em: {$user->created_at}");

            $this->newLine();
            $this->info('🎉 Agora você pode fazer login usando:');
            $this->info("POST /auth/login");
            $this->info("Email: {$email}");
            $this->info("Senha: [a senha que você digitou]");

            return 0;

        } catch (\Exception $e) {
            $this->error('Erro ao criar usuário: ' . $e->getMessage());
            return 1;
        }
    }
}
