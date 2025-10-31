<?php

Route::prefix('api/v1')->middleware('api.key')->group(function () {
    Route::get('articles', 'Ferryops\ArticleAPI\Controllers\Articles@list');
    Route::get('articles/search', 'Ferryops\ArticleAPI\Controllers\Articles@search');
    Route::get('articles/{slug}', 'Ferryops\ArticleAPI\Controllers\Articles@show');
    Route::get('categories', 'Ferryops\ArticleAPI\Controllers\Articles@categories');
});
