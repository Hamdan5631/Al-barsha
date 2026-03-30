<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\BulkExportInvoicesRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class InvoiceBulkExportController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function __invoke(BulkExportInvoicesRequest $request): BinaryFileResponse
    {
        $ids = $request->validated()['invoice_ids'];

        $invoices = Invoice::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Invoice $invoice) => array_search($invoice->id, $ids, true))
            ->values();

        foreach ($invoices as $invoice) {
            $this->invoiceService->ensurePdfExists($invoice);
            $invoice->refresh();
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'inv_zip_');
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create export archive.');
        }

        $usedNames = [];
        $added = 0;
        foreach ($invoices as $invoice) {
            if (! $invoice->pdf_path || ! Storage::disk('public')->exists($invoice->pdf_path)) {
                continue;
            }
            $absolute = Storage::disk('public')->path($invoice->pdf_path);
            $entryName = $invoice->invoice_number.'.pdf';
            $base = $entryName;
            $n = 1;
            while (isset($usedNames[$entryName])) {
                $entryName = pathinfo($base, PATHINFO_FILENAME).'_'.$n.'.pdf';
                $n++;
            }
            $usedNames[$entryName] = true;
            if ($zip->addFile($absolute, $entryName)) {
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(422, 'No invoice PDFs could be exported.');
        }

        $fileName = 'invoices_export_'.now()->format('Y-m-d_His').'.zip';

        return response()->download($zipPath, $fileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
