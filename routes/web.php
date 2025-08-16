<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rota de teste
Route::get('/', function () {
    return response()->json([
        'message' => 'Mr.CRM API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// Rotas de autenticação
Route::group(['prefix' => 'api/auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    
    // Rotas protegidas
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
    });
});

// Rota de teste protegida
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    Route::get('test', function () {
        return response()->json([
            'message' => 'Rota protegida funcionando!',
            'user' => auth()->user()
        ]);
    });
}); 

// Incluir rotas de imóveis
require __DIR__ . '/imoveis.php';

// Incluir rotas de clientes
require __DIR__ . '/clientes.php';

// Incluir rotas de condomínios
require __DIR__ . '/condominios.php';

// Incluir rotas de perfis
require __DIR__ . '/perfis.php';

// Incluir rotas de situações
require __DIR__ . '/situacoes.php';

// Incluir rotas de posições solares
require __DIR__ . '/posicoes_solares.php';

// Incluir rotas de localização (cidades e bairros)
require __DIR__ . '/localizacao.php';