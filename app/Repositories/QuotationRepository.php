<?php

namespace App\Repositories;

use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuotationRepository
{
    public function create(array $data): Quotation
    {
        return Quotation::query()->create($data);
    }

    public function paginateWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Quotation::query()
            ->with(['staff', 'items'])
            ->when($filters['quotation_number'] ?? null, fn ($query, $value) => $query->where('quotation_number', 'like', "%{$value}%"))
            ->when($filters['customer_name'] ?? null, fn ($query, $value) => $query->where('customer_name', 'like', "%{$value}%"))
            ->when($filters['date'] ?? null, fn ($query, $value) => $query->whereDate('date', $value))
            ->latest()
            ->paginate($perPage);
    }
}
