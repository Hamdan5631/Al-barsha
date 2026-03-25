<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* Colours from AL BARSHA.pdf: body text rgb(24,66,114) #184272; muted fill #bfc0bf (PyMuPDF) */
        @page { margin: 10mm 10mm 12mm; }
        * { box-sizing: border-box; }
        :root {
            --pdf-navy: #184272;
            --pdf-muted: #bfc0bf;
            --pdf-border: #bfc0bf;
            --pdf-pale: #f1f2f1;
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
            color: var(--pdf-navy);
            opacity: 0.18;
            z-index: 0;
            pointer-events: none;
        }
        .page-wrap { position: relative; z-index: 1; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; padding: 0; }

        .logo-cell { width: 16%; padding-right: 6px; }
        .logo-ring {
            width: 56px;
            height: 56px;
            border: 2.5px solid var(--pdf-navy);
            border-radius: 50%;
            text-align: center;
            line-height: 50px;
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 15px;
            font-style: italic;
            font-weight: bold;
            color: var(--pdf-navy);
        }

        .brand-cell { width: 46%; padding-top: 2px; }
        .brand-name {
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 22px;
            font-weight: bold;
            color: var(--pdf-navy);
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .brand-sub {
            font-size: 8.5px;
            font-weight: bold;
            color: var(--pdf-navy);
            letter-spacing: 0.6px;
            margin-top: 2px;
        }
        .brand-ar {
            font-size: 9px;
            direction: rtl;
            color: var(--pdf-navy);
            margin-top: 4px;
            line-height: 1.35;
        }

        .title-cell { width: 38%; text-align: right; padding-left: 4px; }
        .ar-banner {
            background: var(--pdf-navy);
            color: #fff;
            padding: 8px 10px 8px 16px;
            text-align: right;
            margin-bottom: 6px;
        }
        .ar-banner-text {
            font-size: 9px;
            direction: rtl;
            line-height: 1.4;
        }
        .invoice-en {
            font-size: 20px;
            font-weight: bold;
            color: var(--pdf-navy);
            letter-spacing: 1px;
        }
        .invoice-ar {
            font-size: 14px;
            font-weight: bold;
            color: var(--pdf-navy);
            direction: rtl;
            margin-top: 2px;
        }

        .meta-box {
            border: 1px solid var(--pdf-navy);
            background: var(--pdf-pale);
            padding: 8px 10px;
            margin-bottom: 0;
        }
        .meta-inner { width: 100%; border-collapse: collapse; }
        .meta-inner td { vertical-align: top; padding: 2px 4px; }
        .meta-label { font-weight: bold; color: var(--pdf-navy); }
        .meta-to { width: 52%; }

        .items-wrap { border: 1px solid var(--pdf-navy); border-top: none; }

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
        .items-table thead tr.head-main th {
            background: var(--pdf-navy);
            color: #fff;
            font-weight: bold;
            text-align: center;
            border-color: var(--pdf-navy);
            padding: 5px 2px;
        }
        .items-table thead tr.head-sub th {
            background: var(--pdf-navy);
            color: #fff;
            font-size: 7.5px;
            text-align: center;
            border-color: var(--pdf-navy);
            padding: 3px 2px;
        }
        .items-table thead tr.head-ar th {
            background: var(--pdf-navy);
            color: #fff;
            font-size: 7.5px;
            direction: rtl;
            text-align: center;
            border-color: var(--pdf-navy);
            padding: 3px 2px;
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
            border: 1px solid var(--pdf-border);
            border-top: none;
            background: #fafafa;
            padding: 6px 10px;
        }
        .words-row { width: 100%; border-collapse: collapse; }
        .words-row td { vertical-align: top; padding: 2px 0; }
        .words-label { font-weight: bold; width: 22%; color: var(--pdf-navy); }
        .words-val { font-style: italic; font-size: 8.5px; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .sig-table td { vertical-align: bottom; padding: 4px 6px; width: 50%; }
        .sig-left {
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 9px;
            color: var(--pdf-navy);
        }
        .sig-right {
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 9px;
            font-weight: bold;
            color: var(--pdf-navy);
            text-align: right;
        }
        .staff-sig { margin-top: 6px; text-align: right; }
        .staff-sig img { max-width: 120px; height: auto; }

        .footer-bar {
            margin-top: 16px;
            background: var(--pdf-navy);
            color: #fff;
            text-align: center;
            padding: 8px 10px;
            font-size: 8.5px;
            line-height: 1.5;
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
@endphp

<div class="watermark">Bdt</div>
<div class="page-wrap">

<table class="header-table">
    <tr>
        <td class="logo-cell">
            <div class="logo-ring">Bdt</div>
        </td>
        <td class="brand-cell">
            <div class="brand-name">AL BARSHA</div>
            <div class="brand-sub">DOCUMENTS TYPING &amp; COPYING</div>
            {{-- Exact Arabic line from PDF (combined company line under English) --}}
            <div class="brand-ar">اﻟﺒﺮﺷﺎ ﻟﻄﺒﺎﻋﺔ وﺗﺼﻮﻳﺮ اﻟﻤﺴﺘﻨﺪات</div>
        </td>
        <td class="title-cell">
            <div class="ar-banner">
                <div class="ar-banner-text">اﻟﺒﺮﺷﺎ</div>
                <div class="ar-banner-text">ﻟﻄﺒﺎﻋﺔ وﺗﺼﻮﻳﺮ اﻟﻤﺴﺘﻨﺪات</div>
            </div>
            <div class="invoice-en">INVOICE</div>
            {{-- Exact Arabic title from PDF (ﻓﺎﺗﻮﺭﺓ with tatweel stretch) --}}
            <div class="invoice-ar">ﻓﺎﺗــــــــﻮرة</div>
        </td>
    </tr>
</table>

<div class="meta-box">
    <table class="meta-inner">
        <tr>
            <td class="meta-to">
                <span class="meta-label">To:</span>
                {{ $invoice->customer_name }}
            </td>
            <td style="text-align: right;">
                <span class="meta-label">Invoice No:</span>
                {{ $invoice->invoice_number }}<br>
                <span class="meta-label">Date:</span>
                {{ $invoiceDate }}
            </td>
        </tr>
    </table>
</div>

<div class="items-wrap">
<table class="items-table">
    <thead>
    <tr class="head-main">
        <th rowspan="2" class="col-sl">Sl No.</th>
        <th rowspan="2" class="col-desc">Description</th>
        <th rowspan="2" class="col-qty">Qty.</th>
        <th rowspan="2" colspan="2">Unit Price<br>(AED)</th>
        <th colspan="2">Amount</th>
    </tr>
    <tr class="head-sub">
        <th class="col-dhs">Dhs.</th>
        <th class="col-fils">Fils</th>
    </tr>
    <tr class="head-ar">
        <th class="col-sl">اﻟﺮﻗﻢ</th>
        <th class="col-desc">اﻟﺘـﻔـﺎﺻـﻴـﻞ</th>
        <th class="col-qty">اﻟﻜﻤﻴﺔ</th>
        <th>ﺳﻌﺮࢫ</th>
        <th>اﻟﻮﺣﺪة</th>
        <th colspan="2">اﻟـﻤﺒـﻠﻎ</th>
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
            <td colspan="2" class="col-unit">{{ number_format((float) $item->unit_price, 2) }}</td>
            <td class="col-dhs">{{ $dhs }}</td>
            <td class="col-fils">{{ str_pad((string) $fils, 2, '0', STR_PAD_LEFT) }}</td>
        </tr>
    @endforeach
    @for($i = $items->count(); $i < $lineCount; $i++)
        <tr>
            <td class="col-sl">&nbsp;</td>
            <td class="td-desc">&nbsp;</td>
            <td class="col-qty">&nbsp;</td>
            <td colspan="2" class="col-unit">&nbsp;</td>
            <td class="col-dhs">&nbsp;</td>
            <td class="col-fils">&nbsp;</td>
        </tr>
    @endfor
    <tr class="total-row">
        <td colspan="5" class="td-total-label">Total</td>
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
            <strong>Receiver&rsquo;s Name &amp; Signature</strong>
        </td>
        <td class="sig-right">
            For AL BARSHA DOCUMENTS TYPING &amp; COPYING
            @if(!empty($staff?->signature))
                <div class="staff-sig">
                    <img src="{{ public_path('storage/'.$staff->signature) }}" alt="">
                </div>
            @endif
        </td>
    </tr>
</table>

<div class="footer-bar">
    Tel: +971 6 5541118, P.O.Box 31864, Butina, Tasheel Center, Sharjah - U.A.E.<br>
    E-mail: albarshatyping333@gmail.com
</div>

</div>
</body>
</html>
