@extends('layouts.app', ['pageTitle' => 'حجوزات الفعالية'])

@section('content')
    <div class="container mx-auto px-4 py-6" dir="rtl">
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

        <div class="mb-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-slate-800">حجوزات الفعالية</h2>

                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1">
                            الموسم: {{ $event->SeasonName }} ({{ $event->SeasonYear }})
                        </span>

                        <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-3 py-1">
                            {{ $event->EventTypeName }} - {{ $event->EventName }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-green-50 text-green-700 px-3 py-1">
                            من: {{ \Carbon\Carbon::parse($event->EventStartDate)->format('Y-m-d') }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-red-50 text-red-700 px-3 py-1">
                            إلى: {{ \Carbon\Carbon::parse($event->EventEndDate)->format('Y-m-d') }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('eventBookingFinance.create', $event->SeasonEventID) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                        إضافة حجز جديد
                    </a>

                    <a href="{{ route('eventBookingFinance.selector') }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                        رجوع
                    </a>
                </div>
            </div>
        </div>

        <details class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden group" open>
            <summary
                class="cursor-pointer list-none px-4 py-3 bg-gradient-to-l from-slate-50 to-white border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold">
                        📊
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">الملخص السريع</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">إحصائيات اليوم المحدد + الإجمالي + التصدير</p>
                    </div>
                </div>

                <span
                    class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs group-open:rotate-180 transition-transform duration-200">
                    ⌄
                </span>
            </summary>

            <div class="p-4 bg-slate-50/40 space-y-4">
                <form method="GET" action="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                    class="bg-white rounded-2xl border border-slate-200 p-3">
                    <div class="flex flex-col md:flex-row md:items-end gap-3">
                        <div class="flex-1">
                            <label for="summary_date" class="block text-xs font-bold text-slate-700 mb-2">
                                اختر يوم الدفع
                            </label>
                            <select name="summary_date" id="summary_date"
                                class="w-full h-11 px-4 border rounded-xl text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                                @forelse($paymentDays as $day)
                                    <option value="{{ $day }}"
                                        {{ $selectedSummaryDate == $day ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($day)->format('Y-m-d') }}
                                    </option>
                                @empty
                                    <option value="{{ now()->format('Y-m-d') }}">
                                        {{ now()->format('Y-m-d') }}
                                    </option>
                                @endforelse
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="h-11 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition-colors duration-200">
                                تطبيق
                            </button>

                            <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                                class="h-11 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold transition-colors duration-200 inline-flex items-center">
                                إعادة ضبط
                            </a>
                        </div>
                    </div>
                </form>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    {{-- اليوم المحدد --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 font-extrabold text-sm">
                                إجمالي يوم {{ \Carbon\Carbon::parse($selectedSummaryDate)->format('Y-m-d') }}
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-3 text-center">
                                <div class="text-[11px] text-slate-500 mb-1">عدد الأشخاص</div>
                                <div class="text-lg font-extrabold text-slate-800">
                                    <span class="blur-sm hover:blur-0 transition duration-200 inline-block">
                                        {{ number_format($selectedDaySummary['people_count']) }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-emerald-700 mb-1">المحصّل</div>
                                <div class="text-lg font-extrabold text-emerald-700">
                                    <span class="blur-sm hover:blur-0 transition duration-200 inline-block">
                                        {{ number_format($selectedDaySummary['payments_amount'], 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-red-100 bg-red-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-red-700 mb-1">المرتجع</div>
                                <div class="text-lg font-extrabold text-red-700">
                                    <span class="blur-sm hover:blur-0 transition duration-200 inline-block">
                                        {{ number_format($selectedDaySummary['refund_amount'], 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الإجمالي --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 font-extrabold text-sm">
                                إجمالي الحجز
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-3 text-center">
                                <div class="text-[11px] text-slate-500 mb-1">عدد الأشخاص</div>
                                <div class="text-lg font-extrabold text-slate-800">
                                    <span class="blur-sm hover:blur-0 transition duration-200 inline-block">
                                        {{ number_format($totalSummary['people_count']) }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-emerald-700 mb-1">المحصّل</div>
                                <div class="text-lg font-extrabold text-emerald-700">
                                    <span class="blur-sm hover:blur-0 transition duration-200 inline-block">
                                        {{ number_format($totalSummary['payments_amount'], 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-red-100 bg-red-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-red-700 mb-1">المرتجع</div>
                                <div class="text-lg font-extrabold text-red-700">
                                    <span class="blur-sm hover:blur-0 transition duration-200 inline-block">
                                        {{ number_format($totalSummary['refund_amount'], 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- التصدير --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 font-extrabold text-sm">
                                تصدير Excel
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <a href="{{ route('eventBookingFinance.exportToday', ['seasonEventID' => $event->SeasonEventID, 'summary_date' => $selectedSummaryDate]) }}"
                                class="flex items-center justify-center w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 text-sm font-bold transition-colors duration-200">
                                طباعة بيانات اليوم المحدد
                            </a>

                            <a href="{{ route('eventBookingFinance.exportAll', $event->SeasonEventID) }}"
                                class="flex items-center justify-center w-full rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-4 py-3 text-sm font-bold transition-colors duration-200">
                                طباعة كل البيانات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </details>
        <x-data-table :data="$bookings" title="حجوزات الفعالية" :columns="[
            [
                'key' => 'PersonFullName',
                'label' => 'الاسم',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'PersonID',
                'label' => 'رقم الهوية',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PersonPersonalMobileNumber',
                'label' => 'الموبايل',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'QetaaNames',
                'label' => 'القطاع',
                'type' => 'label',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'ShirtSize',
                'label' => 'حجم القميص',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'BookingStatusText',
                'label' => 'الحالة',
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
            [
                'key' => 'OriginalPriceFormatted',
                'label' => 'السعر الأصلي',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'DiscountAmountFormatted',
                'label' => 'الخصم',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'FinalRequiredAmountFormatted',
                'label' => 'المطلوب النهائي',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-semibold',
            ],
            [
                'key' => 'AmountPaidFormatted',
                'label' => 'المدفوع',
                'type' => 'text',
                'cssClass' => 'text-sm text-green-700 font-semibold',
            ],
            [
                'key' => 'RemainingAmountFormatted',
                'label' => 'المتبقي',
                'type' => 'text',
                'cssClass' => 'text-sm text-red-700 font-semibold',
            ],
            [
                'key' => 'PaymentsProgress',
                'label' => 'عدد الأقساط',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'FirstPaymentDateFormatted',
                'label' => 'أول دفعة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'LastPaymentDateFormatted',
                'label' => 'آخر دفعة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
        ]" :actions="[
            [
                'name' => 'add_installment',
                'label' => 'إضافة دفعة',
                'route' => route('eventBookingFinance.createInstallment', ':id'),
                'idField' => 'SeasonEventParticipantFinanceID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'edit_last_payment',
                'label' => 'تعديل آخر دفعة',
                'route' => route('eventBookingFinance.editLastPayment', ':id'),
                'idField' => 'LastPaymentID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'print_receipt',
                'label' => 'طباعة آخر إيصال',
                'route' => route('eventBookingFinance.printReceipt', ':id'),
                'idField' => 'LastPaymentID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'refund_full',
                'label' => 'استرداد كامل',
                'route' => route('eventBookingFinance.refundPage', ':id'),
                'idField' => 'SeasonEventParticipantFinanceID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'refund_partial',
                'label' => 'استرداد مع خصم جزء',
                'route' => route('eventBookingFinance.partialRefundPage', ':id'),
                'idField' => 'SeasonEventParticipantFinanceID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-200',
            ],
        ]" :searchable="true"
            :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
