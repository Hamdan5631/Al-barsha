<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->settingService->toApiArray(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->boolean('remove_company_stamp')) {
            $this->settingService->clearCompanyStamp();
        }

        $companyStampFile = $request->file('company_stamp') ?? $request->file('company_stamp_url');
        if ($companyStampFile) {
            $this->settingService->storeCompanyStamp($companyStampFile);
        }

        $mapping = [
            'invoice_company_name' => Setting::INVOICE_COMPANY_NAME,
            'invoice_footer_line1' => Setting::INVOICE_FOOTER_LINE1,
            'invoice_footer_line2' => Setting::INVOICE_FOOTER_LINE2,
        ];

        foreach ($mapping as $input => $key) {
            if (array_key_exists($input, $validated)) {
                $this->settingService->set($key, $validated[$input]);
            }
        }

        return response()->json([
            'message' => 'Settings updated.',
            'data' => $this->settingService->toApiArray(),
        ]);
    }
}
