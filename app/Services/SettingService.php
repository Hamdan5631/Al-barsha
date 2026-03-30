<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /** @return array<string, string|null> */
    public function all(): array
    {
        return Setting::query()
            ->orderBy('key')
            ->pluck('value', 'key')
            ->all();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $row = Setting::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /** @param  array<string, string|null>  $pairs */
    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function storeCompanyStamp(UploadedFile $file): string
    {
        $previous = $this->get(Setting::COMPANY_STAMP_IMAGE);
        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        $path = $file->store('settings', 'public');
        $this->set(Setting::COMPANY_STAMP_IMAGE, $path);

        return $path;
    }

    public function clearCompanyStamp(): void
    {
        $previous = $this->get(Setting::COMPANY_STAMP_IMAGE);
        if ($previous) {
            Storage::disk('public')->delete($previous);
        }
        $this->set(Setting::COMPANY_STAMP_IMAGE, null);
    }

    /** Values for PDF template (with defaults when unset). */
    public function forInvoicePdf(): array
    {
        return [
            'company_stamp_image' => $this->get(Setting::COMPANY_STAMP_IMAGE),
            'invoice_company_name' => $this->get(Setting::INVOICE_COMPANY_NAME)
                ?? 'AL BARSHA DOCUMENTS TYPING & COPYING',
            'invoice_footer_line1' => $this->get(Setting::INVOICE_FOOTER_LINE1)
                ?? 'Tel: +971 6 5541118, P.O.Box 31864, Butina, Tasheel Center, Sharjah - U.A.E.',
            'invoice_footer_line2' => $this->get(Setting::INVOICE_FOOTER_LINE2)
                ?? 'E-mail: albarshatyping333@gmail.com',
        ];
    }

    /** API payload: settings + derived URLs. */
    public function toApiArray(): array
    {
        $all = $this->all();
        $stamp = $all[Setting::COMPANY_STAMP_IMAGE] ?? null;
        $all['company_stamp_url'] = $stamp ? asset('storage/'.ltrim($stamp, '/')) : null;

        return $all;
    }
}
