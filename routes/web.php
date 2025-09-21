<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ReceiptController;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Receipt Management
    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/scan', [ReceiptController::class, 'scan'])->name('receipts.scan');
    Route::post('/receipts/scan', [ReceiptController::class, 'processScan'])->name('receipts.process-scan');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
