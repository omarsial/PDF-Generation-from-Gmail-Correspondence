<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PdfGeneratorController;
use App\Http\Controllers\GoogleAuthController;

Route::get('/', [PdfGeneratorController::class, 'index']);
Route::post('/generate-pdf', [PdfGeneratorController::class, 'generate']);


Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);



