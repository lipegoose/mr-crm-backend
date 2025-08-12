<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Condomínios
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas ao CRUD de condomínios,
| incluindo endpoints para listagem, busca e gerenciamento.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Rotas CRUD básicas
    Route::get('condominios', 'CondominioController@index');
    
    // Rotas específicas (devem vir antes de condominios/{id} para evitar conflitos)
    Route::get('condominios/search', 'CondominioController@search');
    Route::get('condominios/select', 'CondominioController@listarParaSelect');
    
    // Rotas com parâmetros
    Route::get('condominios/{id}', 'CondominioController@show');
    Route::post('condominios', 'CondominioController@store');
    Route::put('condominios/{id}', 'CondominioController@update');
    Route::delete('condominios/{id}', 'CondominioController@destroy');
});
