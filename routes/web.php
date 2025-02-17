<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/prompt', function () {
    return view('prompts.system', [
        'version' => '11',
    ]);
});
