<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Staff;
use App\Repositories\InvoiceRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly SettingService $settingService) {}

    public function list(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $paginator = $this->invoiceRepository->paginateWithFilters($filters, $perPage);

        foreach ($paginator->items() as $invoice) {
            $this->ensurePdfExists($invoice);
        }

        return $paginator;
    }

    /**
     * Generate and persist the PDF if missing or the file was removed from storage.
     */
    public function ensurePdfExists(Invoice $invoice): void
    {
        $invoice->loadMissing(['items', 'staff']);

        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            return;
        }

        $path = $this->generatePdf($invoice);
        $invoice->update(['pdf_path' => $path]);
    }

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data): Invoice {
            $invoice = $this->invoiceRepository->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_name' => $data['customer_name'],
                'date' => $data['date'],
                'staff_id' => $data['staff_id'],
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $totalAmount += $totalPrice;

                $invoice->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            $invoice->update(['total_amount' => $totalAmount]);
            $invoice->load(['items', 'staff']);

            $pdfPath = $this->generatePdf($invoice);
            $invoice->update(['pdf_path' => $pdfPath]);

            return $invoice->fresh(['items', 'staff']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data): Invoice {
            $invoice->update([
                'customer_name' => $data['customer_name'],
                'date' => $data['date'],
                'staff_id' => $data['staff_id'],
            ]);

            $invoice->items()->delete();

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $totalAmount += $totalPrice;

                $invoice->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            $invoice->update(['total_amount' => $totalAmount]);
            $invoice->load(['items', 'staff']);

            $pdfPath = $this->generatePdf($invoice);
            $invoice->update(['pdf_path' => $pdfPath]);

            return $invoice->fresh(['items', 'staff']);
        });
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd');

        $last = Invoice::query()
            ->whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $sequence = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    private function generatePdf(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'staff' => $invoice->staff ?? Staff::find($invoice->staff_id),
            'settings' => $this->settingService->forInvoicePdf(),
        ]);
        $fileName = 'invoices/'.$invoice->invoice_number.'.pdf';

        Storage::disk('public')->put($fileName, $pdf->output());

        return $fileName;
    }
}
