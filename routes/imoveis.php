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
    Route::post('imoveis/{id}/duplicar', 'ImovelController@duplicar');
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
    
    // Rotas para listar opções do wizard
    Route::group(['prefix' => 'imoveis/opcoes'], function () {
        Route::get('tipos', 'ImovelOpcoesController@getTipos');
        Route::get('subtipos/{tipo}', 'ImovelOpcoesController@getSubtipos');
        Route::get('tipos-negocio', 'ImovelOpcoesController@getTiposNegocio');
        Route::get('caracteristicas/{escopo}', 'ImovelOpcoesController@getCaracteristicas');
        Route::get('proximidades', 'ImovelOpcoesController@getProximidades');
        Route::get('portais', 'ImovelOpcoesController@getPortais');
        Route::get('redes-sociais', 'ImovelOpcoesController@getRedesSociais');
    });
    
    // Rotas para as etapas do wizard de imóveis
    Route::group(['prefix' => 'imoveis/{id}/etapas'], function () {
        // Verificar completude das etapas
        Route::get('completude', 'ImovelEtapasController@verificarCompletude');
        
        // Etapa Informações
        Route::get('informacoes', 'ImovelEtapasController@getInformacoes');
        Route::put('informacoes', 'ImovelEtapasController@updateInformacoes');
        
        // Etapa Cômodos
        Route::get('comodos', 'ImovelEtapasController@getComodos');
        Route::put('comodos', 'ImovelEtapasController@updateComodos');
        
        // Etapa Medidas
        Route::get('medidas', 'ImovelEtapasController@getMedidas');
        Route::put('medidas', 'ImovelEtapasController@updateMedidas');
        
        // Etapa Preço
        Route::get('preco', 'ImovelEtapasController@getPreco');
        Route::put('preco', 'ImovelEtapasController@updatePreco');
        
        // Etapa Características
        Route::get('caracteristicas', 'ImovelEtapasController@getCaracteristicas');
        Route::put('caracteristicas', 'ImovelEtapasController@updateCaracteristicas');
        
        // Etapa Características do Condomínio
        Route::get('caracteristicas-condominio', 'ImovelEtapasController@getCaracteristicasCondominio');
        Route::put('caracteristicas-condominio', 'ImovelEtapasController@updateCaracteristicasCondominio');
        
        // Etapa Localização
        Route::get('localizacao', 'ImovelEtapasController@getLocalizacao');
        Route::put('localizacao', 'ImovelEtapasController@updateLocalizacao');
        
        // Etapa Proximidades
        Route::get('proximidades', 'ImovelEtapasController@getProximidades');
        Route::put('proximidades', 'ImovelEtapasController@updateProximidades');
        
        // Etapa Descrição
        Route::get('descricao', 'ImovelEtapasController@getDescricao');
        Route::put('descricao', 'ImovelEtapasController@updateDescricao');
        
        // Etapa Complementos
        Route::get('complementos', 'ImovelEtapasController@getComplementos');
        Route::put('complementos', 'ImovelEtapasController@updateComplementos');
        
        // Etapa Imagens
        Route::get('imagens', 'ImovelEtapasController@getImagens');
        Route::put('imagens', 'ImovelEtapasController@updateImagens');
        
        // Etapa Publicação
        Route::get('publicacao', 'ImovelEtapasController@getPublicacao');
        Route::put('publicacao', 'ImovelEtapasController@updatePublicacao');
        
        // Etapa Proprietário
        Route::get('proprietario', 'ImovelEtapasController@getProprietario');
        Route::put('proprietario', 'ImovelEtapasController@updateProprietario');
        
        // Etapa SEO
        Route::get('seo', 'ImovelEtapasController@getSeo');
        Route::put('seo', 'ImovelEtapasController@updateSeo');
    });
});
