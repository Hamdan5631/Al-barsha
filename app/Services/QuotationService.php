<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Staff;
use App\Repositories\QuotationRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuotationService
{
    public function __construct(
        private readonly QuotationRepository $quotationRepository,
        private readonly SettingService $settingService) {}

    public function list(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $paginator = $this->quotationRepository->paginateWithFilters($filters, $perPage);

        foreach ($paginator->items() as $quotation) {
            $this->ensurePdfExists($quotation);
        }

        return $paginator;
    }

    /**
     * Generate and persist the PDF if missing or the file was removed from storage.
     */
    public function ensurePdfExists(Quotation $quotation): void
    {
        $quotation->loadMissing(['items', 'staff']);

        if ($quotation->pdf_path && Storage::disk('public')->exists($quotation->pdf_path)) {
            return;
        }

        $path = $this->generatePdf($quotation);
        $quotation->update(['pdf_path' => $path]);
    }

    public function create(array $data): Quotation
    {
        return DB::transaction(function () use ($data): Quotation {
            $quotation = $this->quotationRepository->create([
                'quotation_number' => $this->generateQuotationNumber(),
                'customer_name' => $data['customer_name'],
                'date' => $data['date'],
                'staff_id' => $data['staff_id'],
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $totalAmount += $totalPrice;

                $quotation->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            $quotation->update(['total_amount' => $totalAmount]);
            $quotation->load(['items', 'staff']);

            $pdfPath = $this->generatePdf($quotation);
            $quotation->update(['pdf_path' => $pdfPath]);

            return $quotation->fresh(['items', 'staff']);
        });
    }

    public function update(Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($quotation, $data): Quotation {
            $quotation->update([
                'customer_name' => $data['customer_name'],
                'date' => $data['date'],
                'staff_id' => $data['staff_id'],
            ]);

            $quotation->items()->delete();

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $totalAmount += $totalPrice;

                $quotation->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            $quotation->update(['total_amount' => $totalAmount]);
            $quotation->load(['items', 'staff']);

            $pdfPath = $this->generatePdf($quotation);
            $quotation->update(['pdf_path' => $pdfPath]);

            return $quotation->fresh(['items', 'staff']);
        });
    }

    public function delete(Quotation $quotation): void
    {
        DB::transaction(function () use ($quotation): void {
            $pdfPath = $quotation->pdf_path;
            $quotation->delete();

            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
            }
        });
    }

    private function generateQuotationNumber(): string
    {
        $prefix = 'QT-'.now()->format('Ymd');

        $last = Quotation::query()
            ->whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $lastSequence = 0;
        if ($last && preg_match('/-(\d+)$/', $last->quotation_number, $matches) === 1) {
            $lastSequence = (int) $matches[1];
        }
        $sequence = max($lastSequence + 1, 200);

        return sprintf('%s-%d', $prefix, $sequence);
    }

    private function generatePdf(Quotation $quotation): string
    {
        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'staff' => $quotation->staff ?? Staff::find($quotation->staff_id),
            'settings' => $this->settingService->forInvoicePdf(),
        ]);
        $fileName = 'quotations/'.$quotation->quotation_number.'.pdf';

        Storage::disk('public')->put($fileName, $pdf->output());

        return $fileName;
    }
}
