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
Route::group(['prefix' => 'api', 'middleware' => 'auth'], function () {
    // Rotas CRUD básicas
    Route::get('clientes', 'ClienteController@index');
    Route::get('clientes/{id}', 'ClienteController@show');
    Route::post('clientes', 'ClienteController@store');
    Route::put('clientes/{id}', 'ClienteController@update');
    Route::delete('clientes/{id}', 'ClienteController@destroy');
    
    // Rota para busca avançada
    Route::get('clientes/search', 'ClienteController@search');
    
    // Rota específica para o select de proprietários no formulário de imóveis
    Route::get('clientes/select/proprietarios', 'ClienteController@listarParaSelect');
});
