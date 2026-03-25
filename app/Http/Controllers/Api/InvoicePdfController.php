<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class InvoicePdfController extends Controller
{
    public function show(Invoice $invoice): Response
    {
        abort_if(! $invoice->pdf_path || ! Storage::disk('public')->exists($invoice->pdf_path), 404, 'PDF not found.');

        return response(Storage::disk('public')->get($invoice->pdf_path), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
