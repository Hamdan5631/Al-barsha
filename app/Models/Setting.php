<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /** Company stamp image path (public disk), e.g. settings/company-stamp.png */
    public const COMPANY_STAMP_IMAGE = 'company_stamp_image';

    /** Shown in PDF right column: “For …” */
    public const INVOICE_COMPANY_NAME = 'invoice_company_name';

    public const INVOICE_FOOTER_LINE1 = 'invoice_footer_line1';

    public const INVOICE_FOOTER_LINE2 = 'invoice_footer_line2';
}
