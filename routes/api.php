<?php

use App\Http\Controllers\UsuariosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Usuario
Route::post('register', [UsuariosController::class, 'register']);
