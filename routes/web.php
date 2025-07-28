<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Text Highlighter routes  
Route::get('/text-highlighter', function () {
    return view('text-highlighter');
});

Route::get('/text-content/{textContent}/highlighter', [App\Http\Controllers\Api\TextContentController::class, 'highlighterView'])->name('text-content.highlighter');
