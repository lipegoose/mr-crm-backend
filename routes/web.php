<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rota de teste
Route::get('/', function () {
    return response()->json([
        'message' => 'Mr.CRM API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// Rotas de autenticação
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    
    // Rotas protegidas
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
    });
});

// Rota de teste protegida
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('test', function () {
        return response()->json([
            'message' => 'Rota protegida funcionando!',
            'user' => auth()->user()
        ]);
    });
}); 