<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Perfis
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas ao CRUD de perfis,
| incluindo endpoints para listagem e gerenciamento.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Rotas CRUD básicas
    Route::get('perfis', 'PerfisController@index');
    
    // Rotas específicas (devem vir antes de perfis/{id} para evitar conflitos)
    Route::get('perfis/select', 'PerfisController@listarParaSelect');
    
    // Rotas com parâmetros
    Route::get('perfis/{id}', 'PerfisController@show');
    Route::post('perfis', 'PerfisController@store');
    Route::put('perfis/{id}', 'PerfisController@update');
    Route::delete('perfis/{id}', 'PerfisController@destroy');
});
