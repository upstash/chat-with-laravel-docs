<?php

use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QueryController::class, 'index']);

