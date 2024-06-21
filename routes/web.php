<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

Route::get('preview-invoice/{id}', [InvoiceController::class, 'preview'])->name('preview-invoice');
Route::get('download-invoice/{id}', [InvoiceController::class, 'download'])->name('download-invoice');

Route::get('/', function () {
    return view('welcome');
});
