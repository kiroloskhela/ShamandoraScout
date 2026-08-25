<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
@endphp
<html lang="{{ $locale }}" dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <title>{{ $fileName }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 6mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #222;
            font-family: Arial, Tahoma, sans-serif;
            direction: rtl;
        }

        body {
            padding: 0;
        }

        .page {
            width: 190mm;
            margin: 0 auto;
        }

        .receipt {
            position: relative;
            min-height: 110mm;
            height: auto;
            border: 1.5px solid #222;
            border-radius: 10px;
            padding: 5mm 6mm 4mm 6mm;
            overflow: hidden;
            background: #fff;
        }

        .receipt::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('{{ asset('img/shamandora.webp') }}');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: 400px;
            opacity: 0.2;
            filter: grayscale(100%);
            pointer-events: none;
            z-index: 0;
        }

        .receipt-content {
            position: relative;
            z-index: 1;
            height: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #333;
        }

        .receipt:has(.qr-box) .header {
            padding-left: 24mm;
            padding-right: 24mm;
        }

        .logo-top {
            width: 28px;
            height: auto;
            display: block;
            margin: 0 auto 3px auto;
            filter: grayscale(100%);
        }

        .title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 2px 0;
        }

        .subtitle {
            font-size: 14px;
            color: #444;
            margin: 0 0 3px 0;
        }

        .copy-badge {
            display: inline-block;
            border: 1px solid #333;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 14px;
            font-weight: bolder;
            background: #f3f3f3;
            line-height: 1.4;
        }

        .section {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #aaa;
        }

        .section:first-of-type {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: bolder;
            margin-bottom: 3px;
            color: #111;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 2px;
            flex-wrap: wrap;
        }

        .cell {
            width: 48%;
            min-width: 0;
            font-size: 14px;
            line-height: 1.45;
            word-break: break-word;
        }

        .label {
            font-weight: 700;
            color: #111;
        }

        .amount-box {
            margin-top: 4px;
            border: 1.3px solid #222;
            border-radius: 7px;
            padding: 4px 8px;
            text-align: center;
            background: #fafafa;
        }

        .qr-box {
            position: absolute;
            top: 0;
            left: 0;
            width: 22mm;
            text-align: center;
            background: #fff;
            padding: 1mm;
            border: 1px solid #222;
            border-radius: 7px;
            z-index: 2;
        }

        .qr-box img {
            width: 20mm;
            height: 20mm;
            display: block;
            margin: 0 auto;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .qr-caption {
            font-size: 8px;
            font-weight: 700;
            margin-top: 1px;
            word-break: break-all;
            line-height: 1.2;
        }

        .amount-title {
            font-size: 14px;
            font-weight: bolder;
            margin-bottom: 1px;
        }

        .amount {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.14;
        }

        .footer-note {
            margin-top: 4px;
            text-align: center;
            font-size: 14px;
            color: #555;
            line-height: 1.3;
        }

        .cut-line {
            position: relative;
            margin: 2.5mm 0;
            height: 6mm;
        }

        .cut-line::before {
            content: "";
            position: absolute;
            top: 50%;
            right: 0;
            left: 0;
            border-top: 2px dashed #444;
            transform: translateY(-50%);
        }

        .cut-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 0 8px;
            font-size: 9px;
            color: #444;
        }

        .actions {
            text-align: center;
            margin-top: 8px;
        }

        .actions button {
            border: none;
            background: #222;
            color: #fff;
            padding: 7px 14px;
            margin: 0 4px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .actions button.secondary {
            background: #666;
        }

        @media print {

            html,
            body {
                width: 180mm;

            }

            .page {
                width: 198mm;
                max-width: 198mm;


            }

            .actions {
                display: none;
            }

            .receipt {
                min-height: 130mm;
                height: auto;
            }

            .receipt,
            .cut-line {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        @php
            $copies = [__('Finance copy'), __('Participant copy')];
        @endphp

        @foreach ($copies as $copyLabel)
            <div class="receipt">
                <div class="receipt-content">
                    @if ($qrPayload)
                        <div class="qr-box">
                            @if ($qrPng)
                                <img src="data:image/png;base64,{{ $qrPng }}" alt="{{ __('Attendance QR') }}">
                            @endif
                            <div class="qr-caption">{{ $qrPayload }}</div>
                        </div>
                    @endif
                    <div class="header">
                        <img src="{{ asset('img/shamandora.webp') }}" alt="Logo" class="logo-top">
                        <div class="title">{{ __('Receipt') }} {{ $receipt->PaymentType === 'REFUND' ? __('Refund') : __('Pay') }}</div>
                        <div class="subtitle">{{ __('Shamandora Scout Group') }}</div>
                        <div class="copy-badge">{{ $copyLabel }}</div>
                    </div>

                    <div class="section">
                        <div class="section-title">{{ __('Receipt details') }}</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Receipt number:') }}</span>
                                {{ $receipt->ReceiptNumber }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Issued at:') }}</span>
                                {{ $receipt->IssuedAt }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Season:') }}</span>
                                {{ $receipt->SeasonName }} ({{ $receipt->SeasonYear }})
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Event:') }}</span>
                                {{ $receipt->EventTypeName }} - {{ $receipt->EventName }}
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">{{ __('Participant details') }}</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Person name:') }}</span>
                                {{ $receipt->PersonFullName }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('ID number:') }}</span>
                                {{ $receipt->PersonID }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Mobile:') }}</span>
                                {{ $receipt->PersonPersonalMobileNumber ?: '-' }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Receiving leader:') }}</span>
                                {{ $receipt->ServentFullName }}
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">{{ __('Transaction details') }}</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Transaction type:') }}</span>
                                {{ $receipt->PaymentType === 'REFUND' ? __('Refund') : __('Pay') }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Installment number:') }}</span>
                                {{ $receipt->InstallmentNumber }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Transaction date:') }}</span>
                                {{ $receipt->PaymentDate }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Installments count:') }}</span>
                                {{ $receipt->InstallmentsNumber }}
                            </div>
                        </div>

                        <div class="amount-box">
                            <div class="amount-title">{{ __('Amount') }}</div>
                            <div class="amount">{{ number_format($receipt->Amount, 2) }}</div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">{{ __('Financial details') }}</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Original price:') }}</span>
                                {{ number_format($receipt->OriginalPrice, 2) }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Discount:') }}</span>
                                {{ number_format($receipt->DiscountAmount, 2) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Final required:') }}</span>
                                {{ number_format($receipt->FinalRequiredAmount, 2) }}
                            </div>
                            <div class="cell">
                                <span class="label">{{ __('Total paid now:') }}</span>
                                {{ number_format($receipt->AmountPaid, 2) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">{{ __('Remaining:') }}</span>
                                {{ number_format($receipt->RemainingAmount, 2) }}
                            </div>
                            <div class="cell"></div>
                        </div>
                    </div>

                    <div class="footer-note">{{ __('This receipt is approved as proof of') }} {{ $receipt->PaymentType === 'REFUND' ? __('Refund') : __('Pay') }}.
                    </div>

                </div>
            </div>

            @if (!$loop->last)
                <div class="cut-line">

                </div>
            @endif
        @endforeach

        <div class="actions">
            <button onclick="window.print()">{{ __('Print') }}</button>
            <button class="secondary" onclick="window.history.back()">{{ __('Back') }}</button>
        </div>
    </div>
</body>

</html>
