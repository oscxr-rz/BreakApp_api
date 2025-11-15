<?php

use App\Http\Controllers\Admin\AdminCategoriasController;
use App\Http\Controllers\Admin\AdminProductosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

//Usuario
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum');



//Administradores
Route::middleware('auth:sanctum')->prefix('/admin')->group(function () {
    //Categorias
    Route::prefix('categorias')->group(function () {
        Route::get('/', [AdminCategoriasController::class, 'index']);
        Route::post('/', [AdminCategoriasController::class, 'store']);
        Route::put('/{id}', [AdminCategoriasController::class, 'update']);
        Route::patch('/{id}/estado', [AdminCategoriasController::class, 'cambiarEstado']);
    });

    //Productos
    Route::prefix('/productos')->group(function () {
        Route::get('/', [AdminProductosController::class, 'index']);
        Route::post('/', [AdminProductosController::class, 'store']);
        Route::put('/{id}', [AdminProductosController::class, 'update']);
        Route::patch('/{id}/estado', [AdminProductosController::class, 'cambiarEstado']);
    });
});
