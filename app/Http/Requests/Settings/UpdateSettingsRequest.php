<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_stamp_url' => ['sometimes', 'file', 'image', 'max:4096'],
            'company_stamp' => ['sometimes', 'file', 'image', 'max:4096'],
            'remove_company_stamp' => ['sometimes', 'boolean'],
            'invoice_company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'invoice_footer_line1' => ['sometimes', 'nullable', 'string', 'max:600'],
            'invoice_footer_line2' => ['sometimes', 'nullable', 'string', 'max:600'],
        ];
    }
}
