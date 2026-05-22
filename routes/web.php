<?php

use App\Http\Controllers\PdfUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PdfUploadController::class, 'index'])->name('imports.upload');
Route::post('/upload', [PdfUploadController::class, 'store'])->name('imports.store');
Route::get('/status/{id}', [PdfUploadController::class, 'status'])->name('imports.status');
Route::get('/status/{id}/json', [PdfUploadController::class, 'statusJson'])->name('imports.status.json');
Route::get('/questions', [PdfUploadController::class, 'questions'])->name('imports.questions');
Route::get('/failed-jobs', [PdfUploadController::class, 'failedJobs'])->name('imports.failed-jobs');
