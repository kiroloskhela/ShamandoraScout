@extends('layouts.app', ['pageTitle' => 'حجوزات الفعالية'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                {{ $errors->first('general') }}
            </div>
        @endif

        <div class="mb-6 bg-white rounded-lg shadow p-6 border border-blue-200">
            <h2 class="text-xl font-bold text-gray-800 mb-3">حجوزات الفعالية</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-700">
                <div><strong>الموسم:</strong> {{ $event->SeasonName }} ({{ $event->SeasonYear }})</div>
                <div><strong>الفعالية:</strong> {{ $event->EventTypeName }} - {{ $event->EventName }}</div>
                <div><strong>بداية الفعالية:</strong> {{ $event->EventStartDate }}</div>
                <div><strong>نهاية الفعالية:</strong> {{ $event->EventEndDate }}</div>
            </div>

            <div class="mt-4 flex gap-3">
                <a href="{{ route('eventBookingFinance.create', $event->SeasonEventID) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    إضافة حجز جديد
                </a>

                <a href="{{ route('eventBookingFinance.selector') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    رجوع
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm text-right">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3">الاسم</th>
                        <th class="px-4 py-3">PersonID</th>
                        <th class="px-4 py-3">الموبايل</th>
                        <th class="px-4 py-3">القطاع</th>
                        <th class="px-4 py-3">الحالة</th>
                        <th class="px-4 py-3">السعر الأصلي</th>
                        <th class="px-4 py-3">الخصم</th>
                        <th class="px-4 py-3">المطلوب النهائي</th>
                        <th class="px-4 py-3">المدفوع</th>
                        <th class="px-4 py-3">المتبقي</th>
                        <th class="px-4 py-3">عدد الأقساط</th>
                        <th class="px-4 py-3">أول دفعة</th>
                        <th class="px-4 py-3">آخر دفعة</th>
                        <th class="px-4 py-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr class="border-t">
                            <td class="px-4 py-3">
                                {{ $booking->PersonFullName }}
                            </td>
                            <td class="px-4 py-3">{{ $booking->PersonID }}</td>
                            <td class="px-4 py-3">{{ $booking->PersonPersonalMobileNumber }}</td>
                            <td class="px-4 py-3">{{ $booking->QetaaNames }}</td>
                            <td class="px-4 py-3">
                                @if ($booking->SpecialCaseType === 'AKHOH_RAB')
                                    <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded">أخوه
                                        رب</span>
                                @elseif($booking->SpecialCaseType === 'HAS_BROTHERS')
                                    <span class="inline-block bg-orange-100 text-orange-800 px-2 py-1 rounded">له
                                        إخوة</span>
                                @elseif($booking->SpecialCaseType === 'OTHER')
                                    <span class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded">أخرى</span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded">عادي</span>
                                @endif

                                @if ((int) $booking->IsRefunded === 1)
                                    <span class="inline-block bg-red-100 text-red-700 px-2 py-1 rounded mt-1">مسترد</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ number_format($booking->OriginalPrice, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($booking->DiscountAmount, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($booking->FinalRequiredAmount, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($booking->AmountPaid, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($booking->RemainingAmount, 2) }}</td>
                            <td class="px-4 py-3">{{ $booking->PaymentsCount }} / {{ $booking->InstallmentsNumber }}</td>
                            <td class="px-4 py-3">{{ $booking->FirstPaymentDate }}</td>
                            <td class="px-4 py-3">{{ $booking->LastPaymentDate }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    @if (
                                        (int) $booking->IsRefunded === 0 &&
                                            (float) $booking->RemainingAmount > 0 &&
                                            (int) $booking->PaymentsCount < (int) $booking->InstallmentsNumber)
                                        <a href="{{ route('eventBookingFinance.createInstallment', $booking->SeasonEventParticipantFinanceID) }}"
                                            class="bg-blue-50 text-blue-700 px-3 py-2 rounded text-center hover:bg-blue-100">
                                            إضافة دفعة
                                        </a>
                                    @endif

                                    @if ($booking->LastPaymentID)
                                        <a href="{{ route('eventBookingFinance.editLastPayment', $booking->LastPaymentID) }}"
                                            class="bg-green-50 text-green-700 px-3 py-2 rounded text-center hover:bg-green-100">
                                            تعديل آخر دفعة
                                        </a>

                                        <a href="{{ route('eventBookingFinance.printReceipt', $booking->LastPaymentID) }}"
                                            class="bg-gray-50 text-gray-700 px-3 py-2 rounded text-center hover:bg-gray-100">
                                            طباعة آخر إيصال
                                        </a>
                                    @endif

                                    @if ((int) $booking->IsRefunded === 0 && (float) $booking->AmountPaid > 0)
                                        <a href="{{ route('eventBookingFinance.refundPage', $booking->SeasonEventParticipantFinanceID) }}"
                                            class="bg-red-50 text-red-700 px-3 py-2 rounded text-center hover:bg-red-100">
                                            استرداد كامل
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-6 text-center text-gray-500">لا توجد حجوزات حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
