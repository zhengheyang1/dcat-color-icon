<?php

use Zhy\ColorfulIcon\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('auth/menu', Controllers\ColorfulIconController::class, ['except' => ['create', 'show']]);

Route::get('helpers/icons',Controllers\IconController::class.'@index');
