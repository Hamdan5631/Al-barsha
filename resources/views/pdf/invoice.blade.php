@php
    $fontArabicRegular = str_replace('\\', '/', storage_path('fonts/NotoSansArabic-Regular.ttf'));
    $fontArabicBold = str_replace('\\', '/', storage_path('fonts/NotoSansArabic-Bold.ttf'));
    $invoiceHeaderImage = str_replace('\\', '/', public_path('barshalogo.jpeg'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* Colours from AL BARSHA.pdf: body text rgb(24,66,114) #184272; muted fill #bfc0bf (PyMuPDF) */
        @font-face {
            font-family: 'NotoSansArabic';
            font-style: normal;
            font-weight: normal;
            src: url('{{ $fontArabicRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'NotoSansArabic';
            font-style: normal;
            font-weight: bold;
            src: url('{{ $fontArabicBold }}') format('truetype');
        }
        @page { margin: 10mm 10mm 12mm; }
        * { box-sizing: border-box; }
        html, body {
            border: 0 !important;
            outline: none !important;
        }
        img {
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
        }
        :root {
            --pdf-navy: #184272;
            --pdf-muted: #bfc0bf;
            --pdf-border: #bfc0bf;
            --pdf-pale: #e8e9e8;
            --font-ar: 'NotoSansArabic', 'DejaVu Sans', sans-serif;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: var(--pdf-navy);
            margin: 0;
            line-height: 1.25;
            position: relative;
        }
        .watermark {
            position: fixed;
            left: 0;
            right: 0;
            top: 38%;
            text-align: center;
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 100px;
            font-style: italic;
            color: #c5cad1;
            opacity: 0.35;
            z-index: 0;
            pointer-events: none;
        }
        .page-wrap { position: relative; z-index: 1; }

        .document-frame {
            border: 0 !important;
            outline: none !important;
        }

        .invoice-header {
            width: 100%;
            margin: 0;
            padding: 4px 0 2px;
            text-align: center;
            background: #fff;
            border: 0 !important;
            outline: none !important;
            overflow: hidden;
        }
        .invoice-header-img {
            width: 100%;
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
            border: 0 !important;
            outline: none !important;
            vertical-align: top;
            object-fit: contain;
        }
        .meta-strip {
            background: var(--pdf-pale);
            padding: 8px 10px 10px;
        }
        .meta-inner { width: 100%; border-collapse: collapse; }
        .meta-inner td { vertical-align: middle; padding: 0; }
        .meta-inner tr + tr td { padding-top: 6px; }
        .meta-label { font-weight: bold; color: var(--pdf-navy); }
        .meta-to-val { padding-left: 4px; }
        .meta-right { text-align: right; white-space: nowrap; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .items-table th, .items-table td {
            border: 1px solid var(--pdf-border);
            padding: 4px 3px;
            vertical-align: middle;
        }
        .items-table thead th {
            background-color: #184272 !important;
            color: #ffffff !important;
            font-weight: bold;
            border: 1px solid #ffffff;
            padding: 5px 4px;
            vertical-align: middle;
            text-align: center;
        }
        /* DomPDF: avoid nested tables & display:block in th — use <br> + inline spans */
        .items-table thead .th-ar {
            font-family: var(--font-ar);
            font-size: 8px;
            color: #ffffff !important;
            font-weight: bold;
        }
        .items-table thead .th-en {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 7.5px;
            color: #ffffff !important;
            font-weight: bold;
        }
        .items-table thead tr.head-sub th {
            font-size: 7.5px;
            text-align: center;
            padding: 4px 2px;
            color: #ffffff !important;
        }
        .items-table tbody td {
            text-align: center;
            color: var(--pdf-navy);
            min-height: 14px;
        }
        .items-table tbody td.td-desc { text-align: left; }
        .items-table tbody tr.total-row td {
            font-weight: bold;
            background: var(--pdf-pale);
            border-top: 2px solid var(--pdf-navy);
        }
        .items-table tbody tr.total-row .td-total-label {
            text-align: right;
            padding-right: 8px;
        }
        .col-sl { width: 5%; }
        .col-desc { width: 38%; }
        .col-qty { width: 7%; }
        .col-unit { width: 14%; }
        .col-dhs { width: 9%; }
        .col-fils { width: 9%; }

        .words-box {
            background: var(--pdf-pale);
            padding: 7px 10px;
        }
        .words-row { width: 100%; border-collapse: collapse; }
        .words-row td { vertical-align: top; padding: 2px 0; }
        .words-label { font-weight: bold; width: 22%; color: var(--pdf-navy); }
        .words-val { font-style: italic; font-size: 8.5px; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 12px; padding: 0 8px 10px; }
        .sig-table td { vertical-align: bottom; padding: 4px 2px; width: 50%; }
        .sig-left {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 9px;
            font-weight: bold;
            color: var(--pdf-navy);
        }
        .sig-left .staff-name {
            font-weight: normal;
            display: inline-block;
            margin-top: 6px;
        }
        .sig-right {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 9px;
            font-weight: bold;
            color: var(--pdf-navy);
            text-align: right;
        }
        .staff-sig { margin-top: 4px; margin-bottom: 0; }
        .sig-left .staff-sig { text-align: left; }
        .sig-right .staff-sig { text-align: right; }
        .staff-sig img { max-width: 120px; height: auto; }

        .company-stamp { margin-bottom: 6px; text-align: right; }
        .company-stamp img { max-height: 72px; max-width: 140px; height: auto; }

        .footer-bar {
            margin-top: 0;
            background: var(--pdf-navy);
            color: #fff;
            text-align: center;
            padding: 9px 12px 10px;
            font-size: 8.5px;
            line-height: 1.55;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        }
        .footer-bar a { color: #fff; text-decoration: none; }
    </style>
</head>
<body>
@php
    $invoiceDate = \Illuminate\Support\Carbon::parse($invoice->date)->format('d/m/Y');
    $items = $invoice->items;
    $lineCount = max(18, $items->count());
    $totalDhsFils = \App\Support\AedMoney::splitDhsFils((float) $invoice->total_amount);
    $settings = $settings ?? [
        'company_stamp_image' => null,
        'invoice_company_name' => 'AL BARSHA DOCUMENTS TYPING & COPYING',
        'invoice_footer_line1' => 'Tel: +971 6 5541118, P.O.Box 31864, Butina, Tasheel Center, Sharjah - U.A.E.',
        'invoice_footer_line2' => 'E-mail: albarshatyping333@gmail.com',
    ];
    $stampPath = $settings['company_stamp_image'] ?? null;
    $stampFullPath = $stampPath ? public_path('storage/'.ltrim($stampPath, '/')) : null;
@endphp

<div class="watermark">Bdt</div>
<div class="page-wrap">

<div class="document-frame">

@if(file_exists(public_path('barshalogo.jpeg')))
<div class="invoice-header">
    <img src="{{ $invoiceHeaderImage }}" alt="AL BARSHA DOCUMENTS TYPING &amp; COPYING" class="invoice-header-img">
</div>
@else
<div class="invoice-header" style="font-weight:bold;font-size:11px;color:#184272;padding:8px;">AL BARSHA DOCUMENTS TYPING &amp; COPYING</div>
@endif

<div class="meta-strip">
    <table class="meta-inner">
        <tr>
            <td colspan="2">
                <span class="meta-label">To:</span><span class="meta-to-val">{{ $invoice->customer_name }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Invoice No:</span>
                {{ $invoice->invoice_number }}
            </td>
            <td class="meta-right">
                <span class="meta-label">Date:</span>
                {{ $invoiceDate }}
            </td>
        </tr>
    </table>
</div>

<div class="items-wrap">
<table class="items-table">
    <thead>
    <tr>
        <th rowspan="2" class="col-sl">
            <span class="th-ar" dir="rtl">الرقم</span><br>
            <span class="th-en">SI No.</span>
        </th>
        <th rowspan="2" class="col-desc">
            <span class="th-ar" dir="rtl">التفاصيل</span><br>
            <span class="th-en">Description</span>
        </th>
        <th rowspan="2" class="col-qty">
            <span class="th-ar" dir="rtl">الكمية</span><br>
            <span class="th-en">Qty.</span>
        </th>
        <th rowspan="2" class="col-unit">
            <span class="th-ar" dir="rtl">سعر الوحدة</span><br>
            <span class="th-en">Unit Price (AED)</span>
        </th>
        <th colspan="2">
            <span class="th-ar" dir="rtl">المبلغ</span><br>
            <span class="th-en">Amount</span>
        </th>
    </tr>
    <tr class="head-sub">
        <th class="col-dhs">Dhs.</th>
        <th class="col-fils">Fils</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $index => $item)
        @php
            [$dhs, $fils] = \App\Support\AedMoney::splitDhsFils((float) $item->total_price);
        @endphp
        <tr>
            <td class="col-sl">{{ $index + 1 }}</td>
            <td class="td-desc">{{ $item->product_name }}</td>
            <td class="col-qty">{{ $item->quantity }}</td>
            <td class="col-unit">{{ number_format((float) $item->unit_price, 2) }}</td>
            <td class="col-dhs">{{ $dhs }}</td>
            <td class="col-fils">{{ str_pad((string) $fils, 2, '0', STR_PAD_LEFT) }}</td>
        </tr>
    @endforeach
    @for($i = $items->count(); $i < $lineCount; $i++)
        <tr>
            <td class="col-sl">&nbsp;</td>
            <td class="td-desc">&nbsp;</td>
            <td class="col-qty">&nbsp;</td>
            <td class="col-unit">&nbsp;</td>
            <td class="col-dhs">&nbsp;</td>
            <td class="col-fils">&nbsp;</td>
        </tr>
    @endfor
    <tr class="total-row">
        <td colspan="4" class="td-total-label">Total</td>
        <td class="col-dhs">{{ $totalDhsFils[0] }}</td>
        <td class="col-fils">{{ str_pad((string) $totalDhsFils[1], 2, '0', STR_PAD_LEFT) }}</td>
    </tr>
    </tbody>
</table>
</div>

<div class="words-box">
    <table class="words-row">
        <tr>
            <td class="words-label">Total Amount in words:</td>
            <td class="words-val">{{ \App\Support\AedMoney::inWords((float) $invoice->total_amount) }}</td>
        </tr>
    </table>
</div>

<table class="sig-table">
    <tr>
        <td class="sig-left">
            <strong>Staff Name &amp; Signature</strong><br>
            @if(!empty($staff?->signature))
                <div class="staff-sig">
                    <img src="{{ public_path('storage/'.$staff->signature) }}" alt="">
                </div>
            @endif
            @if(!empty($staff?->name))
                <span class="staff-name">{{ $staff->name }}</span>
            @endif
        </td>
        <td class="sig-right">
            @if($stampFullPath && file_exists($stampFullPath))
                <div class="company-stamp">
                    <img src="{{ str_replace('\\', '/', $stampFullPath) }}" alt="">
                </div>
            @endif
            For {{ $settings['invoice_company_name'] }}
        </td>
    </tr>
</table>

<div class="footer-bar">
    {{ $settings['invoice_footer_line1'] }}<br>
    {{ $settings['invoice_footer_line2'] }}
</div>

</div>{{-- .document-frame --}}

</div>
</body>
</html>
