<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/web/admin.php';
require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/register.php';
require __DIR__ . '/web/transaction.php';
Route::get('/', function () {
    return view('welcome');
});
