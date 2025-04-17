<?php

use App\Http\Controllers\MyClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/clients', [MyClientController::class, 'index']);
Route::post('/clients', [MyClientController::class, 'store']);
Route::get('/clients/{slug}', [MyClientController::class, 'show']);
Route::put('/clients/{slug}', [MyClientController::class, 'update']);
Route::delete('/clients/{slug}/destroy', [MyClientController::class, 'destroy']);
Route::put('/clients/{slug}/delete', [MyClientController::class, 'softDelete']);
