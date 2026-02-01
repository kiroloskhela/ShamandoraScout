@extends('layouts.app', ['pageTitle' => 'فاتورة'])

@section('content')
    <style>
        /* Screen styling */
        .invoice-box {
            border: 2px dashed #9CA3AF;
            /* gray-400 */
            border-radius: 12px;
            padding: 18px;
            background: #fff;
        }

        .two-copies {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* PRINT: print only the invoice area */
        @media print {

            /* A4 page + safe margins */
            @page {
                size: A4;
                margin: 10mm;
            }

            /* Hide everything */
            body * {
                visibility: hidden !important;
            }

            /* Show only print-area */
            #print-area,
            #print-area * {
                visibility: visible !important;
            }

            /* Place print area at top-left of page */
            #print-area {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Remove extra spacing/shadows from layout */
            .print-hidden {
                display: none !important;
            }

            /* Ensure it stays ONE page */
            .two-copies {
                gap: 8mm;
            }

            .invoice-box {
                page-break-inside: avoid;
                break-inside: avoid;
                padding: 12px;
                /* reduce padding for 1 page fit */
                border-radius: 10px;
            }

            /* Optional: slightly smaller font to guarantee one page */
            .invoice-text {
                font-size: 12px !important;
            }

            .invoice-title {
                font-size: 16px !important;
            }
        }
    </style>

    <div class="container mx-auto px-4 py-8" dir="rtl">

        <!-- Print button (won't print) -->
        <div class="flex justify-center mb-6 print-hidden">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                طباعة
            </button>
        </div>

        <!-- ✅ ONLY THIS SECTION WILL PRINT -->
        <div id="print-area">
            <div class="two-copies invoice-text">

                @for ($i = 1; $i <= 2; $i++)
                    <div class="invoice-box">

                        <!-- Top row -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="text-right">
                                <div class="invoice-title font-bold text-gray-800">فاتورة حجز</div>
                                <div class="text-xs text-gray-500">
                                    {{ $i == 1 ? 'نسخة الإدارة' : 'نسخة الشخص' }}
                                </div>
                            </div>

                            <div class="text-left text-sm text-gray-700">
                                <div>
                                    <span class="text-gray-500">التاريخ:</span>
                                    {{ \Carbon\Carbon::now()->format('H:i Y-m-d') }}
                                </div>
                            </div>
                        </div>

                        <!-- Content grid (similar to your screenshot layout) -->
                        <div class="grid grid-cols-2 gap-x-10 gap-y-6">

                            <!-- Right column -->
                            <div class="text-right">
                                <div class="text-gray-500 text-sm mb-1">PersonID</div>
                                <div class="font-bold text-gray-800">{{ $data->PersonID }}</div>
                            </div>

                            <!-- Left column -->
                            <div class="text-left">
                                <div class="text-gray-500 text-sm mb-1">الاسم</div>
                                <div class="font-bold text-gray-800">{{ $data->FullName }}</div>
                            </div>

                            <div class="text-right">
                                <div class="text-gray-500 text-sm mb-1">الفصيلة</div>
                                <div class="font-bold text-gray-800">{{ $data->QetaaName ?? '-' }}</div>
                            </div>

                            <div class="text-left">
                                <div class="text-gray-500 text-sm mb-1">المرحلة</div>
                                <div class="font-bold text-gray-800">{{ $data->SanaMarhalaName ?? '-' }}</div>
                            </div>

                            <div class="col-span-2 text-right">
                                <div class="text-gray-500 text-sm mb-1">الفعالية</div>
                                <div class="font-bold text-gray-800">
                                    {{ $data->SeasonName }} {{ $data->SeasonYear }} — {{ $data->EventName }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-gray-500 text-sm mb-1">تاريخ الحجز</div>
                                <div class="font-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($data->BookingDate)->format('H:i:s Y-m-d') }}
                                </div>
                            </div>

                            <div class="text-left">
                                <div class="text-gray-500 text-sm mb-1">Installment</div>
                                <div class="font-bold text-gray-800">
                                    @if ($lastInstallment)
                                        (ID: {{ $lastInstallment->InstallmentID }}) #{{ $lastInstallment->InstallmentNo }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <div class="col-span-2 text-right mt-2">
                                <div class="text-gray-500 text-sm mb-1">مبلغ الدفع (هذه المرة)</div>
                                <div class="font-bold text-gray-900 text-lg">
                                    @if ($lastPayment)
                                        {{ number_format($lastPayment->Amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <div class="col-span-2 text-right">
                                <div class="text-gray-500 text-sm mb-1">تاريخ الدفع</div>
                                <div class="font-bold text-gray-800">
                                    @if ($lastPayment)
                                        {{ \Carbon\Carbon::parse($lastPayment->TransactionDate)->format('H:i:s Y-m-d') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                        </div>

                        <hr class="my-5">

                        <div class="text-sm text-gray-700 text-right">
                            <span class="font-bold">ملاحظة:</span>
                            هذه الفاتورة تثبت عملية الحجز/الدفع طبقاً للنظام.
                        </div>

                    </div>
                @endfor

            </div>
        </div>
    </div>
@endsection
