<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Personas;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => ["auth:sanctum"]], function () {
    Route::get('/auth/cantones', [AuthController::class, 'listaCP']);
    Route::get('/auth/provincias', [AuthController::class, 'listPCPR']);
    Route::get('/auth/provis', [AuthController::class, 'listaPC']);
    Route::put('/auth/actualizar', [AuthController::class, 'UpdateRE']);


});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
  Route::get('/auth/users', [AuthController::class, 'listuser']);

