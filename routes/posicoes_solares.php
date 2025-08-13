<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Posições Solares
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas ao CRUD de posições solares,
| incluindo endpoints para listagem e gerenciamento.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Rotas CRUD básicas
    Route::get('posicoes-solares', 'PosicoesController@index');
    
    // Rotas específicas (devem vir antes de posicoes-solares/{id} para evitar conflitos)
    Route::get('posicoes-solares/select', 'PosicoesController@listarParaSelect');
    
    // Rotas com parâmetros
    Route::get('posicoes-solares/{id}', 'PosicoesController@show');
    Route::post('posicoes-solares', 'PosicoesController@store');
    Route::put('posicoes-solares/{id}', 'PosicoesController@update');
    Route::delete('posicoes-solares/{id}', 'PosicoesController@destroy');
});
