<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Proximidades
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
    // Listagem e busca (colocar antes de {id})
    Route::get('proximidades', 'ProximidadeController@index');
    Route::get('proximidades/search', 'ProximidadeController@search');

    // CRUD por id
    Route::get('proximidades/{id}', 'ProximidadeController@show');
    Route::post('proximidades', 'ProximidadeController@store');
    Route::put('proximidades/{id}', 'ProximidadeController@update');
    Route::delete('proximidades/{id}', 'ProximidadeController@destroy');
});
