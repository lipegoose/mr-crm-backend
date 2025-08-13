<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Situações
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas ao CRUD de situações,
| incluindo endpoints para listagem e gerenciamento.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Rotas CRUD básicas
    Route::get('situacoes', 'SituacoesController@index');
    
    // Rotas específicas (devem vir antes de situacoes/{id} para evitar conflitos)
    Route::get('situacoes/select', 'SituacoesController@listarParaSelect');
    
    // Rotas com parâmetros
    Route::get('situacoes/{id}', 'SituacoesController@show');
    Route::post('situacoes', 'SituacoesController@store');
    Route::put('situacoes/{id}', 'SituacoesController@update');
    Route::delete('situacoes/{id}', 'SituacoesController@destroy');
});
