<?php

use App\Http\Controllers\Admin\AdminCategoriasController;
use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\AdminOrdenesController;
use App\Http\Controllers\Admin\AdminProductosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginGoogleController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Broadcasting\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\MenuDiarioController;
use App\Http\Controllers\NotificacionesController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\TarjetaLocalController;
use App\Http\Controllers\UsuariosController;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', [AuthController::class, 'authenticate']);

//Usuario
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login/google', [LoginGoogleController::class, 'login']);
Route::get('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum');

//Usuarios
Route::middleware(['auth:sanctum', 'usuario.validar'])->prefix('/usuario')->group(function () {
    //Usuario
    Route::get('/{id}', [UsuariosController::class, 'show']);
    Route::put('/{id}', [UsuariosController::class, 'update']);
    Route::post('/{id}/imagen', [UsuariosController::class, 'updateImagen']);
    Route::patch('/{id}/password', [UsuariosController::class, 'updatePassword']);
    Route::patch('/{id}/desactivar', [UsuariosController::class, 'desactivar']);

    //Carrito
    Route::prefix('/carrito')->group(function () {
        Route::get('/{id}', [CarritoController::class, 'show']);
        Route::post('/{id}/add', [CarritoController::class, 'addCarrito']);
        Route::post('/{id}/remove', [CarritoController::class, 'removeCarrito']);
        Route::patch('/{id}/eliminar', [CarritoController::class, 'eliminarCarrito']);
    });

    //Tarjeta Digital
    Route::prefix('/tarjeta-local')->group(function () {
        Route::get('/{id}', [TarjetaLocalController::class, 'show']);
    });

    //Ordenes
    Route::prefix('/orden')->group(function () {
        Route::get('/{id}', [OrdenController::class, 'index']);
        Route::post('/{id}', [OrdenController::class, 'store']);
        Route::patch('/{id}/ocultar', [OrdenController::class, 'ocultar']);
    });

    //Notificaciones
    Route::prefix('notificacion')->group(function () {
        Route::get('/{id}', [NotificacionesController::class, 'index']);
        Route::patch('/{id}/ocultar', [NotificacionesController::class, 'ocultar']);
    });
});

//Menú diario
Route::prefix('/menu')->group(function () {
    Route::get('/', [MenuDiarioController::class, 'menuDiario']);
});

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

    //Menús
    Route::prefix('/menus')->group(function () {
        Route::get('/', [AdminMenuController::class, 'index']);
        Route::post('/', [AdminMenuController::class, 'store']);
        Route::put('/{id}', [AdminMenuController::class, 'update']);
        Route::patch('/{id}/estado', [AdminMenuController::class, 'cambiarEstado']);
    });

    //Ordenes
    Route::prefix('ordenes')->group(function () {
        Route::get('/', [AdminOrdenesController::class, 'index']);
        Route::patch('/{id}/estado', [AdminOrdenesController::class, 'cambiarEstado']);
    });
});
