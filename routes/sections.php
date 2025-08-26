<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas para CMS de Seções
|--------------------------------------------------------------------------
*/

// Rotas CMS protegidas por auth
Route::group(['prefix' => 'api/cms/sections', 'middleware' => 'auth:api'], function () {
    // Keywords (rotas estáticas primeiro para evitar conflito com rotas dinâmicas)
    Route::get('keywords', 'KeywordController@index');
    Route::post('keywords', 'KeywordController@store');
    Route::put('keywords/{id:[0-9]+}', 'KeywordController@update');
    Route::delete('keywords/{id:[0-9]+}', 'KeywordController@destroy');

    // Seções
    Route::get('/', 'SectionController@index');
    Route::post('/', 'SectionController@store');
    Route::get('{sectionId:[0-9]+}', 'SectionController@show');
    Route::put('{sectionId:[0-9]+}', 'SectionController@update');
    Route::delete('{sectionId:[0-9]+}', 'SectionController@destroy');

    // Fotos da Seção
    Route::get('{sectionId:[0-9]+}/photos', 'SectionPhotoController@index');
    Route::post('{sectionId:[0-9]+}/photos', 'SectionPhotoController@upload');
    Route::put('{sectionId:[0-9]+}/photos/{photoId:[0-9]+}', 'SectionPhotoController@update');
    Route::delete('{sectionId:[0-9]+}/photos/{photoId:[0-9]+}', 'SectionPhotoController@delete');

    // Itens da Seção
    Route::get('{sectionId:[0-9]+}/items', 'SectionItemController@index');
    Route::post('{sectionId:[0-9]+}/items', 'SectionItemController@store');
    Route::get('{sectionId:[0-9]+}/items/{itemId:[0-9]+}', 'SectionItemController@show');
    Route::put('{sectionId:[0-9]+}/items/{itemId:[0-9]+}', 'SectionItemController@update');
    Route::delete('{sectionId:[0-9]+}/items/{itemId:[0-9]+}', 'SectionItemController@destroy');

    // Fotos do Item
    Route::get('{sectionId:[0-9]+}/items/{itemId:[0-9]+}/photos', 'SectionItemPhotoController@index');
    Route::post('{sectionId:[0-9]+}/items/{itemId:[0-9]+}/photos', 'SectionItemPhotoController@upload');
    Route::put('{sectionId:[0-9]+}/items/{itemId:[0-9]+}/photos/{photoId:[0-9]+}', 'SectionItemPhotoController@update');
    Route::delete('{sectionId:[0-9]+}/items/{itemId:[0-9]+}/photos/{photoId:[0-9]+}', 'SectionItemPhotoController@delete');

    // Vincular keywords
    Route::post('{sectionId:[0-9]+}/items/{itemId:[0-9]+}/keywords', 'SectionItemKeywordController@attach');
    Route::delete('{sectionId:[0-9]+}/items/{itemId:[0-9]+}/keywords/{keywordId:[0-9]+}', 'SectionItemKeywordController@detach');
});

// Endpoint público para o site (sem auth)
Route::group(['prefix' => 'api/public'], function () {
    Route::get('sections', 'PublicSectionController@index');
});
