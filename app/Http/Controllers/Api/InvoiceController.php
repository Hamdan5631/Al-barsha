<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(): AnonymousResourceCollection
    {
        return InvoiceResource::collection($this->invoiceService->list(request()->only([
            'invoice_number',
            'customer_name',
            'date',
        ])));
    }

    public function store(StoreInvoiceRequest $request): InvoiceResource
    {
        return new InvoiceResource($this->invoiceService->create($request->validated()));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($this->invoiceService->update($invoice, $request->validated()));
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $invoice->load(['staff', 'items']);
        $this->invoiceService->ensurePdfExists($invoice);

        return new InvoiceResource($invoice->fresh(['staff', 'items']));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->invoiceService->delete($invoice);

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }
}
