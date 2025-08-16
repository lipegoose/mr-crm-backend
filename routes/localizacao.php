<?php

use Illuminate\Support\Facades\Route;

/** 
 * Rotas para gerenciamento de cidades e bairros
 */

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function () {
	// Rotas para cidades
	Route::get('cidades', 'CidadesController@index');
	Route::get('cidades/select', 'CidadesController@select');
	Route::get('cidades/uf/{uf}', 'CidadesController@porUf');
	Route::get('cidades/uf/{uf}/select', 'CidadesController@selectPorUf');
	Route::get('cidades/nome/{nome}', 'CidadesController@porNome');
	Route::post('cidades', 'CidadesController@store');
	Route::get('cidades/{id}', 'CidadesController@show');
	Route::put('cidades/{id}', 'CidadesController@update');
	Route::delete('cidades/{id}', 'CidadesController@destroy');
	Route::post('cidades/buscar-ou-criar', 'CidadesController@buscarOuCriar');

	// Rotas para bairros
	Route::get('bairros', 'BairrosController@index');
	Route::get('bairros/select', 'BairrosController@select');
	Route::get('bairros/cidade/{cidade_id}', 'BairrosController@porCidade');
	Route::get('bairros/cidade/{cidade_id}/select', 'BairrosController@selectPorCidade');
	Route::get('bairros/nome/{nome}', 'BairrosController@porNome');
	Route::post('bairros', 'BairrosController@store');
	Route::get('bairros/{id}', 'BairrosController@show');
	Route::put('bairros/{id}', 'BairrosController@update');
	Route::delete('bairros/{id}', 'BairrosController@destroy');
	Route::post('bairros/buscar-ou-criar', 'BairrosController@buscarOuCriar');
	Route::post('bairros/buscar-ou-criar-por-cidade-uf', 'BairrosController@buscarOuCriarPorCidadeUf');
});
