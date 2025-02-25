<?php

use App\Http\Controllers\Api\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/articles', [ArticleController::class, 'index']);

// Search Articles API - Allows filtering by search term, categories, sources, authors, and date range
Route::post('/articles/search', [ArticleController::class, 'search']);
