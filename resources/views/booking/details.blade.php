@extends('layouts.app', ['pageTitle' => 'تفاصيل الحجز'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        {{-- Alerts --}}
        @if (session('status'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors && $errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                <ul class="list-disc pr-5 space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $target = $enrollment ? (float) $enrollment->TargetPrice : (float) $eventInfo->SupportedPrice;
            $paid = $enrollment ? (float) $balance : 0.0;
            $remainingRaw = $target - $paid;
            $remaining = $enrollment ? max(0, $remainingRaw) : max(0, (float) $eventInfo->SupportedPrice);

            $isCancelled = $enrollment && ($enrollment->Status ?? '') === 'cancelled';

            $maxPayments = isset($maxPayments) ? (int) $maxPayments : (int) ($eventInfo->InstallmentsNumber ?? 1);
            if ($maxPayments < 1) {
                $maxPayments = 1;
            }

            $paymentsCount = isset($paymentsCount) ? (int) $paymentsCount : 0;

            $limitReached = $enrollment && $paymentsCount >= $maxPayments;
            $isLastPayment = $enrollment && $paymentsCount == $maxPayments - 1;
            $currentPaymentNo = $enrollment ? min($paymentsCount + 1, $maxPayments) : 1;
        @endphp

        <!-- Header Event Info -->
        <div class="bg-white rounded-2xl shadow p-6 border border-slate-200 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">تفاصيل الحجز</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $eventInfo->SeasonName }} ({{ $eventInfo->SeasonYear }}) — {{ $eventInfo->EventName }}
                    </p>

                    @if ($isCancelled)
                        <div
                            class="mt-3 inline-flex items-center px-3 py-1 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                            تم إلغاء الاشتراك — لا يمكن إضافة دفعات جديدة
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-3 text-sm w-full md:w-auto">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
                        <div class="text-gray-500">Target</div>
                        <div class="font-bold text-gray-800">{{ number_format($target, 2) }}</div>
                    </div>

                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
                        <div class="text-gray-500">(Balance) المدفوع</div>
                        <div class="font-bold text-gray-800">{{ number_format($paid, 2) }}</div>
                    </div>

                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
                        <div class="text-gray-500">المتبقي</div>
                        <div class="font-bold text-gray-800">{{ number_format(max(0, $remaining), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment counter info --}}
        @if ($enrollment)
            <div class="mb-6 bg-white rounded-2xl shadow border border-slate-200 p-4 text-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div class="text-gray-700">
                        <span class="font-bold">الدفعة الحالية:</span>
                        <span class="font-bold text-blue-700">{{ $currentPaymentNo }}</span>
                        <span class="text-gray-500">من</span>
                        <span class="font-bold text-gray-800">{{ $maxPayments }}</span>
                    </div>

                    @if ($limitReached)
                        <div class="text-red-700 font-bold">
                            وصلت للحد الأقصى من الدفعات ({{ $maxPayments }}) — لا يمكن إضافة دفعة جديدة.
                        </div>
                    @elseif($isLastPayment && !$isCancelled && $remaining > 0)
                        <div class="text-orange-700 font-bold">
                            هذه آخر دفعة مسموحة — يجب أن تساوي المتبقي بالكامل.
                        </div>
                    @else
                        <div class="text-gray-600">
                            المتبقي: <span class="font-bold text-gray-800">{{ number_format($remaining, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Person Card -->
        <div class="bg-white rounded-2xl shadow border border-slate-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">بيانات الشخص</h3>

                <a href="{{ route('booking.choosePerson', $eventInfo->SeasonEventID) }}"
                    class="text-sm text-blue-600 hover:text-blue-700">
                    تغيير الشخص
                </a>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="text-gray-500 mb-1">الاسم:</div>
                    <div class="font-bold">{{ $person->FullName }}</div>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="text-gray-500 mb-1">PersonID:</div>
                    <div class="font-bold">{{ $person->PersonID }}</div>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="text-gray-500 mb-1">الكود:</div>
                    <div class="font-bold">{{ $person->ShamandoraCode ?? '-' }}</div>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="text-gray-500 mb-1">الموبايل:</div>
                    <div class="font-bold">{{ $person->PersonPersonalMobileNumber ?? '-' }}</div>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="text-gray-500 mb-1">الفصيلة:</div>
                    <div class="font-bold">{{ $person->QetaaName ?? '-' }}</div>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="text-gray-500 mb-1">المرحلة:</div>
                    <div class="font-bold">{{ $person->SanaMarhalaName ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        @if ($enrollment)
            <div class="bg-white rounded-2xl shadow border border-slate-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">الدفعات / المرتجعات</h3>
                    <div class="text-sm text-gray-600">
                        المتبقي: <span class="font-bold text-gray-800">{{ number_format($remaining, 2) }}</span>
                    </div>
                </div>

                <div class="overflow-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="p-3 text-right">التاريخ</th>
                                <th class="p-3 text-right">النوع</th>
                                <th class="p-3 text-right">المبلغ</th>
                                <th class="p-3 text-right">ملاحظات</th>
                                <th class="p-3 text-right">إجراءات</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($transactions as $t)
                                <tr class="border-t">
                                    <td class="p-3">
                                        {{ \Carbon\Carbon::parse($t->TransactionDate)->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="p-3">
                                        @if ($t->TransactionType == 'payment')
                                            <span class="text-blue-700 font-bold">دفع</span>
                                        @elseif($t->TransactionType == 'refund')
                                            <span class="text-orange-700 font-bold">مرتجع</span>
                                        @else
                                            <span class="text-gray-700 font-bold">تعديل</span>
                                        @endif
                                    </td>

                                    <td class="p-3">{{ number_format($t->Amount, 2) }}</td>
                                    <td class="p-3">{{ $t->Notes ?? '-' }}</td>

                                    <td class="p-3">
                                        @if ($t->TransactionType == 'payment')
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('transactions.edit', ['id' => $t->TransactionID, 'return' => url()->current()]) }}"
                                                    class="inline-flex items-center px-3 py-1 rounded-lg bg-green-600 text-white text-xs hover:bg-green-700">
                                                    تعديل
                                                </a>

                                                <a href="{{ route('transactions.delete', ['id' => $t->TransactionID, 'return' => url()->current()]) }}"
                                                    class="inline-flex items-center px-3 py-1 rounded-lg bg-red-600 text-white text-xs hover:bg-red-700">
                                                    حذف
                                                </a>

                                                <a href="{{ route('transactions.refund.form', ['id' => $t->TransactionID, 'return' => url()->current()]) }}"
                                                    class="inline-flex items-center px-3 py-1 rounded-lg bg-orange-600 text-white text-xs hover:bg-orange-700">
                                                    مرتجع
                                                </a>


                                                <a href="{{ route('transactions.invoice', $t->TransactionID) }}"
                                                    class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                                                    طباعة فاتورة
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if (count($transactions) == 0)
                                <tr>
                                    <td colspan="5" class="p-4 text-gray-500">لا يوجد معاملات</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 bg-gray-50 border border-gray-100 rounded-xl p-4 text-sm text-gray-800">
                    <div class="flex justify-between">
                        <span class="text-gray-500">الإجمالي المدفوع:</span>
                        <span class="font-bold">{{ number_format($paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-gray-500">المتبقي:</span>
                        <span class="font-bold">{{ number_format(max(0, $remaining), 2) }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment Card -->
        <div class="bg-white rounded-2xl shadow border border-slate-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">الدفع وتحديث البيانات</h3>

            <form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
                @csrf

                <input type="hidden" name="season_event_id" value="{{ $eventInfo->SeasonEventID }}">
                <input type="hidden" name="person_id" value="{{ $person->PersonID }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <label class="flex items-center gap-3 bg-gray-50 border border-gray-100 rounded-xl p-4 cursor-pointer">
                        <input type="checkbox" id="is_jesus_son" name="is_jesus_son" value="1" class="w-5 h-5"
                            @if ($enrollment && $enrollment->IsJesusSon) checked @endif>
                        <span class="text-sm text-gray-700 font-medium">JesusSons (غير ملزم بسداد كل المبلغ)</span>
                    </label>

                    <label class="flex items-center gap-3 bg-gray-50 border border-gray-100 rounded-xl p-4 cursor-pointer">
                        <input type="checkbox" id="has_brothers" name="has_brothers" value="1" class="w-5 h-5"
                            @if ($enrollment && $enrollment->HasBrothers) checked @endif>
                        <span class="text-sm text-gray-700 font-medium">لديه إخوة (خصم)</span>
                    </label>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">قيمة الخصم (في حالة الإخوة)</label>
                        <input type="number" step="0.01" name="brothers_discount_amount"
                            id="brothers_discount_amount"
                            value="{{ $enrollment ? $enrollment->BrothersDiscountAmount : 0 }}"
                            class="w-full h-12 px-4 border rounded-xl text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: 100">
                        <p class="text-xs text-gray-500 mt-1">سيتم تفعيل الخصم فقط عند اختيار (لديه إخوة).</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">Target Price (ثابت)</label>
                        <input type="number" step="0.01" value="{{ number_format($target, 2, '.', '') }}"
                            class="w-full h-12 px-4 border rounded-xl text-right border-slate-200 text-slate-500 bg-gray-50 focus:outline-none"
                            readonly disabled>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">مبلغ الدفع الآن</label>
                        <input type="number" step="0.01" min="0" name="pay_amount" id="pay_amount"
                            max="{{ number_format($remaining, 2, '.', '') }}"
                            class="w-full h-12 px-4 border rounded-xl text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: 200" @if ($isCancelled || $remaining <= 0 || $limitReached) disabled @endif>

                        <p class="text-xs text-gray-500 mt-1">
                            الحد الأقصى للدفع الآن: {{ number_format($remaining, 2) }}
                        </p>

                        @if ($enrollment && $isLastPayment && !$limitReached && !$isCancelled && $remaining > 0)
                            <p class="text-xs text-orange-700 mt-2 font-bold">
                                هذه آخر دفعة — سيتم إدخال المتبقي تلقائيًا.
                            </p>
                        @endif

                        @if ($limitReached)
                            <p class="text-xs text-red-700 mt-2 font-bold">
                                وصلت للحد الأقصى من الدفعات ({{ $maxPayments }}) — لا يمكن إضافة دفعة جديدة.
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">ملاحظات الدفعة</label>
                        <input type="text" name="pay_notes"
                            class="w-full h-12 px-4 border rounded-xl text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: دفعة أولى / تصحيح / ...">
                    </div>

                </div>

                <div class="mt-6 flex flex-col md:flex-row gap-3 justify-center">
                    <button type="submit" id="submitBtn"
                        class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full bg-blue-600 text-white hover:bg-blue-700 transition
                               @if ($isCancelled) opacity-60 cursor-not-allowed @endif"
                        @if ($isCancelled) disabled @endif>
                        حفظ
                    </button>

                    <a href="{{ route('booking.choosePerson', $eventInfo->SeasonEventID) }}"
                        class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        رجوع
                    </a>
                </div>
            </form>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Discount disable/enable
            const hasBrothers = document.getElementById('has_brothers');
            const discount = document.getElementById('brothers_discount_amount');

            function syncDiscountState() {
                if (!hasBrothers.checked) {
                    discount.value = 0;
                    discount.setAttribute('disabled', 'disabled');
                    discount.classList.add('bg-gray-50', 'text-slate-500');
                } else {
                    discount.removeAttribute('disabled');
                    discount.classList.remove('bg-gray-50', 'text-slate-500');
                }
            }

            if (hasBrothers && discount) {
                hasBrothers.addEventListener('change', syncDiscountState);
                syncDiscountState();
            }

            // Payment button label + last payment behavior
            const payAmount = document.getElementById('pay_amount');
            const submitBtn = document.getElementById('submitBtn');

            const isLastPayment = @json($enrollment ? $paymentsCount == $maxPayments - 1 : false);
            const limitReached = @json($enrollment ? $paymentsCount >= $maxPayments : false);
            const remaining = parseFloat({{ number_format($remaining, 2, '.', '') }});


            function syncSubmitLabel() {
                if (!payAmount || payAmount.disabled) {
                    submitBtn.textContent = 'حفظ';
                    return;
                }
                const v = parseFloat(payAmount.value || '0');
                submitBtn.textContent = (v > 0) ? 'حفظ + فاتورة' : 'حفظ';
            }

            if (payAmount && !payAmount.disabled) {
                if (isLastPayment && !limitReached && remaining > 0) {
                    payAmount.value = remaining.toFixed(2);
                    payAmount.setAttribute('readonly', 'readonly');
                    payAmount.classList.add('bg-gray-50');
                }

                payAmount.addEventListener('input', syncSubmitLabel);
                syncSubmitLabel();
            } else {
                syncSubmitLabel();
            }
        });
    </script>
@endsection
