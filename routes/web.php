<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/movilidad.php';

Route::get('/', function () {
    return view('welcome');
});
