<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\BulkExportQuotationsRequest;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class QuotationBulkExportController extends Controller
{
    public function __construct(private readonly QuotationService $quotationService) {}

    public function __invoke(BulkExportQuotationsRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        $query = Quotation::query()->orderBy('date')->orderBy('id');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        } elseif ($startDate) {
            $query->whereDate('date', '<=', now()->toDateString());
        }

        if (! $startDate && ! $endDate) {
            $query->whereDate('date', '<=', now()->toDateString());
        }

        $quotations = $query->get();

        foreach ($quotations as $quotation) {
            $this->quotationService->ensurePdfExists($quotation);
            $quotation->refresh();
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'qt_zip_');
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create export archive.');
        }

        $usedNames = [];
        $added = 0;
        foreach ($quotations as $quotation) {
            if (! $quotation->pdf_path || ! Storage::disk('public')->exists($quotation->pdf_path)) {
                continue;
            }
            $absolute = Storage::disk('public')->path($quotation->pdf_path);
            $entryName = $quotation->quotation_number.'.pdf';
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
            abort(422, 'No quotation PDFs could be exported.');
        }

        $fileName = 'quotations_export_'.now()->format('Y-m-d_His').'.zip';

        return response()->download($zipPath, $fileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
