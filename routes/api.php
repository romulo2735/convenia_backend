<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollaboratorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::apiResource('collaborators', CollaboratorController::class);
    Route::post('/collaborators/import', [CollaboratorController::class, 'importByFile']);
});
