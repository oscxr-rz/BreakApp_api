<?php

use App\Http\Controllers\UsuariosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Usuario
Route::post('/login', [UsuariosController::class, 'login']);
Route::post('/register', [UsuariosController::class, 'register']);
Route::get('/logout', [UsuariosController::class, 'logout'])->middleware('auth:sanctum');
