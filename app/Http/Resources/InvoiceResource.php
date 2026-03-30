<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name,
            'date' => $this->date,
            'staff_id' => $this->staff_id,
            'total_amount' => (float) $this->total_amount,
            'pdf_path' => $this->pdf_path,
            'pdf_url' => $this->pdf_path
                ? asset('storage/'.ltrim($this->pdf_path, '/'))
                : null,
            'staff' => new StaffResource($this->whenLoaded('staff')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
