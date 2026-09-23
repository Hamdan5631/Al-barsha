<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceBulkExportController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoicePdfController;
use App\Http\Controllers\Api\QuotationBulkExportController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\QuotationPdfController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::post('/invoices/bulk-export', InvoiceBulkExportController::class);
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'show'])->name('invoices.pdf.show');
    Route::post('/quotations/bulk-export', QuotationBulkExportController::class);
    Route::apiResource('quotations', QuotationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/quotations/{quotation}/pdf', [QuotationPdfController::class, 'show'])->name('quotations.pdf.show');
});
