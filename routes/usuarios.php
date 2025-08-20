<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Usuários
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas aos usuários do sistema,
| incluindo endpoints para listagem e gerenciamento.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Rotas específicas para usuários
    Route::get('usuarios/select', 'AuthController@listarParaSelect');
});
