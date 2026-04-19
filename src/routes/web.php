<?php

use Illuminate\Support\Facades\Route;

Route::get('/docs/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'), ['Content-Type' => 'application/json']);
});

Route::get('/', function () {
    return view('welcome');
});
