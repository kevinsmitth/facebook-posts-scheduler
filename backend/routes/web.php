<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/test-facebook-config', function() {
    return response()->json([
        'page_id' => config('facebook.page_id'),
        'has_token' => !empty(config('facebook.page_access_token')),
        'token_length' => strlen(config('facebook.page_access_token') ?? ''),
    ]);
});

