<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Clientes
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas ao CRUD de clientes,
| incluindo endpoints para listagem, busca e gerenciamento.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Rotas CRUD básicas
    Route::get('clientes', 'ClienteController@index');
    
    // Rotas específicas (devem vir antes de clientes/{id} para evitar conflitos)
    Route::get('clientes/search', 'ClienteController@search');
    Route::get('clientes/select/proprietarios', 'ClienteController@listarParaSelect');
    
    // Rotas com parâmetros
    Route::get('clientes/{id}', 'ClienteController@show');
    Route::post('clientes', 'ClienteController@store');
    Route::put('clientes/{id}', 'ClienteController@update');
    Route::delete('clientes/{id}', 'ClienteController@destroy');
});
