@extends('layouts.app', ['pageTitle' => 'إضافة دفعة'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl mx-auto border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة دفعة جديدة</h2>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <div><strong>الاسم:</strong> {{ $booking->PersonFullName }}</div>
                <div><strong>الفعالية:</strong> {{ $booking->EventTypeName }} - {{ $booking->EventName }}</div>
                <div><strong>السعر الأصلي:</strong> {{ number_format($booking->OriginalPrice, 2) }}</div>
                <div><strong>الخصم:</strong> {{ number_format($booking->DiscountAmount, 2) }}</div>
                <div><strong>السعر الفعلي بعد الخصم:</strong> {{ number_format($booking->FinalRequiredAmount, 2) }}</div>
                <div><strong>المطلوب النهائي:</strong> {{ number_format($booking->FinalRequiredAmount, 2) }}</div>
                <div><strong>المدفوع:</strong> {{ number_format($booking->AmountPaid, 2) }}</div>
                <div><strong>المتبقي:</strong> {{ number_format($booking->RemainingAmount, 2) }}</div>
                <div><strong>القسط الحالي:</strong> {{ $nextInstallmentNumber }} من {{ $booking->InstallmentsNumber }}
                </div>
                <div><strong>تاريخ الدفعة:</strong> {{ now()->format('Y-m-d H:i') }}</div>

                @if ($isLastInstallment)
                    <div class="mt-2 text-red-700 font-bold">
                        هذه آخر دفعة ويجب أن تساوي كامل المتبقي.
                    </div>
                @endif
            </div>

            @if ($previousPayments->count() > 0)
                <div class="mb-6 bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
                    <div class="px-4 py-3 bg-gray-50 border-b font-bold text-gray-800">
                        الدفعات السابقة
                    </div>

                    <table class="min-w-full text-sm text-right">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3">رقم القسط</th>
                                <th class="px-4 py-3">التاريخ</th>
                                <th class="px-4 py-3">المبلغ</th>
                                <th class="px-4 py-3">رقم الإيصال</th>
                                <th class="px-4 py-3">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($previousPayments as $payment)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ $payment->InstallmentNumber }}</td>
                                    <td class="px-4 py-3">{{ $payment->PaymentDate }}</td>
                                    <td class="px-4 py-3">{{ number_format($payment->Amount, 2) }}</td>
                                    <td class="px-4 py-3">{{ $payment->ReceiptNumber ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $payment->Notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST"
                action="{{ route('eventBookingFinance.storeInstallment', $booking->SeasonEventParticipantFinanceID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">المبلغ</label>

                        @if ($isLastInstallment)
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                value="{{ number_format($booking->RemainingAmount, 2, '.', '') }}" readonly
                                class="w-full h-12 px-4 border rounded-lg text-right bg-gray-100 border-slate-200 text-slate-600">

                            <p class="text-xs text-red-600 mt-2">
                                هذه آخر دفعة، لذلك تم ضبط المبلغ تلقائيًا على كامل المتبقي.
                            </p>
                        @else
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                value="{{ old('amount') }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                        @endif
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">ملاحظات</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600">
                    </div>

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('eventBookingFinance.index', $booking->SeasonEventID) }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            رجوع
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            حفظ وطباعة الإيصال
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
