<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
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
            'quotation_number' => $this->quotation_number,
            'customer_name' => $this->customer_name,
            'date' => $this->date,
            'staff_id' => $this->staff_id,
            'total_amount' => (float) $this->total_amount,
            'pdf_path' => $this->pdf_path,
            'pdf_url' => $this->pdf_path
                ? asset('storage/'.ltrim($this->pdf_path, '/'))
                : null,
            'staff' => new StaffResource($this->whenLoaded('staff')),
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
