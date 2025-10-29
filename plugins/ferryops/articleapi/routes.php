<?php

Route::prefix('api/v1')->group(function () {
    // Articles endpoints
    Route::get('articles', 'Ferryops\ArticleAPI\Controllers\Articles@list');
    Route::get('articles/search', 'Ferryops\ArticleAPI\Controllers\Articles@search');
    Route::get('articles/{id}', 'Ferryops\ArticleAPI\Controllers\Articles@show');
    
    // Categories endpoint
    Route::get('categories', 'Ferryops\ArticleAPI\Controllers\Articles@categories');
});