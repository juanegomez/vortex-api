<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt.auth');

Route::middleware(['jwt.auth'])->group(function () {
    // Rutas que requieren autenticación
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/export', [QuestionController::class, 'export']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::post('/questions/validate', [QuestionController::class, 'validateAnswer']);
});
