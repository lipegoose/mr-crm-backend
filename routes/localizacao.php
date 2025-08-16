<?php

/** 
 * Rotas para gerenciamento de cidades e bairros
 */

// Rotas para cidades
$router->get('/cidades', 'CidadesController@index');
$router->get('/cidades/select', 'CidadesController@select');
$router->get('/cidades/uf/{uf}', 'CidadesController@porUf');
$router->get('/cidades/uf/{uf}/select', 'CidadesController@selectPorUf');
$router->get('/cidades/nome/{nome}', 'CidadesController@porNome');
$router->post('/cidades', 'CidadesController@store');
$router->get('/cidades/{id}', 'CidadesController@show');
$router->put('/cidades/{id}', 'CidadesController@update');
$router->delete('/cidades/{id}', 'CidadesController@destroy');
$router->post('/cidades/buscar-ou-criar', 'CidadesController@buscarOuCriar');

// Rotas para bairros
$router->get('/bairros', 'BairrosController@index');
$router->get('/bairros/select', 'BairrosController@select');
$router->get('/bairros/cidade/{cidade_id}', 'BairrosController@porCidade');
$router->get('/bairros/cidade/{cidade_id}/select', 'BairrosController@selectPorCidade');
$router->get('/bairros/nome/{nome}', 'BairrosController@porNome');
$router->post('/bairros', 'BairrosController@store');
$router->get('/bairros/{id}', 'BairrosController@show');
$router->put('/bairros/{id}', 'BairrosController@update');
$router->delete('/bairros/{id}', 'BairrosController@destroy');
$router->post('/bairros/buscar-ou-criar', 'BairrosController@buscarOuCriar');
$router->post('/bairros/buscar-ou-criar-por-cidade-uf', 'BairrosController@buscarOuCriarPorCidadeUf');
