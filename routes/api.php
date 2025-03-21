<?php

use App\Http\Controllers\InfoController;
use App\Http\Controllers\SaveImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    // Route::get('/messages/{userId}', [MessageController::class, 'getMessages']);
    // Route::post('/upload-image', [SaveImageController::class, 'upload'])->name('upload-image');
    // Route::post('/upload-file', [saveImageController::class, 'uploadFile'])->name('upload-file');
    // Route::get('/info',[InfoController::class, 'getInfoUrl'])->name('info');
});
