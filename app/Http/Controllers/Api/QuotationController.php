<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\StoreQuotationRequest;
use App\Http\Requests\Quotation\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuotationController extends Controller
{
    public function __construct(private readonly QuotationService $quotationService) {}

    public function index(): AnonymousResourceCollection
    {
        return QuotationResource::collection($this->quotationService->list(request()->only([
            'quotation_number',
            'customer_name',
            'date',
        ])));
    }

    public function store(StoreQuotationRequest $request): QuotationResource
    {
        return new QuotationResource($this->quotationService->create($request->validated()));
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation): QuotationResource
    {
        return new QuotationResource($this->quotationService->update($quotation, $request->validated()));
    }

    public function show(Quotation $quotation): QuotationResource
    {
        $quotation->load(['staff', 'items']);
        $this->quotationService->ensurePdfExists($quotation);

        return new QuotationResource($quotation->fresh(['staff', 'items']));
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $this->quotationService->delete($quotation);

        return response()->json(['message' => 'Quotation deleted successfully.']);
    }
}
