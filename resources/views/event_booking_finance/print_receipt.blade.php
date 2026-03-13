<!DOCTYPE html>
<html lang="ar" dir="rtl">

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
            height: 110mm;
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
            background-image: url('{{ asset('img/shamandora.png') }}');
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

        .logo-top {
            width: 28px;
            height: auto;
            display: block;
            margin: 0 auto 3px auto;
            filter: grayscale(100%);
        }

        .title {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 2px 0;
        }

        .subtitle {
            font-size: 9px;
            color: #444;
            margin: 0 0 3px 0;
        }

        .copy-badge {
            display: inline-block;
            border: 1px solid #333;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 9px;
            font-weight: bold;
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
            font-size: 10px;
            font-weight: 700;
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
            font-size: 10px;
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

        .amount-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .amount {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.15;
        }

        .footer-note {
            margin-top: 4px;
            text-align: center;
            font-size: 8.5px;
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
                height: 130mm;
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
            $copies = ['نسخة الإدارة', 'نسخة المشترك'];
        @endphp

        @foreach ($copies as $copyLabel)
            <div class="receipt">
                <div class="receipt-content">
                    <div class="header">
                        <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="logo-top">
                        <div class="title">إيصال {{ $receipt->PaymentType === 'REFUND' ? 'استرداد' : 'دفع' }}</div>
                        <div class="subtitle">جمعية شمندورة الكشفية</div>
                        <div class="copy-badge">{{ $copyLabel }}</div>
                    </div>

                    <div class="section">
                        <div class="section-title">بيانات الإيصال</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">رقم الإيصال:</span>
                                {{ $receipt->ReceiptNumber }}
                            </div>
                            <div class="cell">
                                <span class="label">وقت الإصدار:</span>
                                {{ $receipt->IssuedAt }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">الموسم:</span>
                                {{ $receipt->SeasonName }} ({{ $receipt->SeasonYear }})
                            </div>
                            <div class="cell">
                                <span class="label">الفعالية:</span>
                                {{ $receipt->EventTypeName }} - {{ $receipt->EventName }}
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">بيانات المشترك</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">اسم الشخص:</span>
                                {{ $receipt->PersonFullName }}
                            </div>
                            <div class="cell">
                                <span class="label">الرقم التعريفي:</span>
                                {{ $receipt->PersonID }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">الموبايل:</span>
                                {{ $receipt->PersonPersonalMobileNumber ?: '-' }}
                            </div>
                            <div class="cell">
                                <span class="label">القائد المستلم:</span>
                                {{ $receipt->ServentFullName }}
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">بيانات العملية</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">نوع العملية:</span>
                                {{ $receipt->PaymentType === 'REFUND' ? 'استرداد' : 'دفع' }}
                            </div>
                            <div class="cell">
                                <span class="label">رقم القسط:</span>
                                {{ $receipt->InstallmentNumber }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">تاريخ العملية:</span>
                                {{ $receipt->PaymentDate }}
                            </div>
                            <div class="cell">
                                <span class="label">عدد الأقساط:</span>
                                {{ $receipt->InstallmentsNumber }}
                            </div>
                        </div>

                        <div class="amount-box">
                            <div class="amount-title">المبلغ</div>
                            <div class="amount">{{ number_format($receipt->Amount, 2) }}</div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">البيانات المالية</div>
                        <div class="row">
                            <div class="cell">
                                <span class="label">السعر الأصلي:</span>
                                {{ number_format($receipt->OriginalPrice, 2) }}
                            </div>
                            <div class="cell">
                                <span class="label">الخصم:</span>
                                {{ number_format($receipt->DiscountAmount, 2) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">المطلوب النهائي:</span>
                                {{ number_format($receipt->FinalRequiredAmount, 2) }}
                            </div>
                            <div class="cell">
                                <span class="label">إجمالي المدفوع الآن:</span>
                                {{ number_format($receipt->AmountPaid, 2) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="cell">
                                <span class="label">المتبقي:</span>
                                {{ number_format($receipt->RemainingAmount, 2) }}
                            </div>
                            <div class="cell"></div>
                        </div>
                    </div>

                    <div class="footer-note">
                        هذا الإيصال معتمد كإثبات {{ $receipt->PaymentType === 'REFUND' ? 'استرداد' : 'دفع' }}.
                    </div>

                </div>
            </div>

            @if (!$loop->last)
                <div class="cut-line">

                </div>
            @endif
        @endforeach

        <div class="actions">
            <button onclick="window.print()">طباعة</button>
            <button class="secondary" onclick="window.history.back()">رجوع</button>
        </div>
    </div>
</body>

</html>
