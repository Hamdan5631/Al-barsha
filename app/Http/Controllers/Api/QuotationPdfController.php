<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class QuotationPdfController extends Controller
{
    public function show(Quotation $quotation): Response
    {
        abort_if(! $quotation->pdf_path || ! Storage::disk('public')->exists($quotation->pdf_path), 404, 'PDF not found.');

        return response(Storage::disk('public')->get($quotation->pdf_path), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
