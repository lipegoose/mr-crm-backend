<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Características
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Listagem e busca (colocar antes de {id})
    Route::get('caracteristicas', 'CaracteristicaController@index');
    Route::get('caracteristicas/search', 'CaracteristicaController@search');

    // CRUD por id
    Route::get('caracteristicas/{id}', 'CaracteristicaController@show');
    Route::post('caracteristicas', 'CaracteristicaController@store');
    Route::put('caracteristicas/{id}', 'CaracteristicaController@update');
    Route::delete('caracteristicas/{id}', 'CaracteristicaController@destroy');
});
