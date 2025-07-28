<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/text-highlighter', function () {
    return view('text-highlighter');
});
