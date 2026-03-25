<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    public function paginateWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['staff', 'items'])
            ->when($filters['invoice_number'] ?? null, fn ($query, $value) => $query->where('invoice_number', 'like', "%{$value}%"))
            ->when($filters['customer_name'] ?? null, fn ($query, $value) => $query->where('customer_name', 'like', "%{$value}%"))
            ->when($filters['date'] ?? null, fn ($query, $value) => $query->whereDate('date', $value))
            ->latest()
            ->paginate($perPage);
    }
}
