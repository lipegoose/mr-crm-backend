<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para Imóveis
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas ao CRUD de imóveis,
| incluindo endpoints para gerenciamento de mídias e código de referência.
|
*/

// Grupo de rotas protegidas por autenticação
Route::group(['prefix' => 'api', 'middleware' => 'auth'], function () {
    // Rotas CRUD básicas
    Route::get('imoveis', 'ImovelController@index');
    
    // Rota para busca avançada (deve vir antes de imoveis/{id} para evitar conflitos)
    Route::get('imoveis/search', 'ImovelController@search');
    
    Route::get('imoveis/{id}', 'ImovelController@show');
    Route::post('imoveis/iniciar', 'ImovelController@iniciar');
    Route::put('imoveis/{id}', 'ImovelController@update');
    Route::delete('imoveis/{id}', 'ImovelController@destroy');
    
    // Rotas para código de referência
    Route::get('imoveis/codigo-referencia/{codigo}/{id?}', 'ImovelController@validarCodigoReferencia');
    Route::put('imoveis/{id}/codigo-referencia', 'ImovelController@atualizarCodigoReferencia');
    
    // Rotas para gerenciamento de imagens
    Route::post('imoveis/{id}/imagens', 'ImovelController@uploadImagem');
    Route::put('imoveis/{id}/imagens/reordenar', 'ImovelController@reordenarImagens');
    Route::put('imoveis/{id}/imagens/{imagemId}/principal', 'ImovelController@definirImagemPrincipal');
    Route::delete('imoveis/{id}/imagens/{imagemId}', 'ImovelController@excluirImagem');
});
