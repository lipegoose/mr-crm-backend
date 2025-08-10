<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImovelController;

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
Route::group(['middleware' => 'auth'], function () {
    // Rotas CRUD básicas
    Route::get('/imoveis', [ImovelController::class, 'index']);
    Route::get('/imoveis/{id}', [ImovelController::class, 'show']);
    Route::post('/imoveis/iniciar', [ImovelController::class, 'iniciar']);
    Route::put('/imoveis/{id}', [ImovelController::class, 'update']);
    Route::delete('/imoveis/{id}', [ImovelController::class, 'destroy']);
    
    // Rotas para código de referência
    Route::get('/imoveis/codigo-referencia/{codigo}/{id?}', [ImovelController::class, 'validarCodigoReferencia']);
    Route::put('/imoveis/{id}/codigo-referencia', [ImovelController::class, 'atualizarCodigoReferencia']);
    
    // Rotas para gerenciamento de imagens
    Route::post('/imoveis/{id}/imagens', [ImovelController::class, 'uploadImagem']);
    Route::put('/imoveis/{id}/imagens/reordenar', [ImovelController::class, 'reordenarImagens']);
    Route::put('/imoveis/{id}/imagens/{imagemId}/principal', [ImovelController::class, 'definirImagemPrincipal']);
    Route::delete('/imoveis/{id}/imagens/{imagemId}', [ImovelController::class, 'excluirImagem']);
});
