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

        <div class="mb-5 rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-l from-slate-50 via-white to-blue-50/40 px-5 py-5">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl shadow-sm">
                                🎟️
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-extrabold text-slate-800">حجوزات الفعالية</h2>

                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-[11px] font-bold">
                                        {{ $event->EventTypeName }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-slate-500">
                                    متابعة حجوزات الفعالية وإدارة الأشخاص والضيوف والأهالي
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2.5 text-xs font-medium">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">
                                <span class="text-slate-400">الموسم</span>
                                <span class="font-extrabold text-slate-800">{{ $event->SeasonName }}
                                    ({{ $event->SeasonYear }})</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-blue-700">
                                <span class="text-blue-500">الفعالية</span>
                                <span class="font-extrabold">{{ $event->EventName }}</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-700">
                                <span class="text-emerald-500">البداية</span>
                                <span
                                    class="font-extrabold">{{ \Carbon\Carbon::parse($event->EventStartDate)->format('Y-m-d') }}</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700">
                                <span class="text-rose-500">النهاية</span>
                                <span
                                    class="font-extrabold">{{ \Carbon\Carbon::parse($event->EventEndDate)->format('Y-m-d') }}</span>
                            </span>
                        </div>

                        @if (isset($qetaaCounts) && $qetaaCounts->count())
                            <div class="mt-5 rounded-2xl border border-slate-200 bg-white/80 p-4">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-800">عدد الحجوزات حسب القطاع</div>
                                        <div class="text-xs text-slate-500 mt-1">عرض سريع لحالة كل قطاع</div>
                                    </div>

                                    <div class="text-xs text-slate-400">
                                        {{ $qetaaCounts->count() }} قطاع
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2.5">
                                    @foreach ($qetaaCounts as $qetaa)
                                        @php
                                            $full = $qetaa->booked_count >= 50;
                                        @endphp

                                        <div
                                            class="inline-flex items-center gap-2 rounded-2xl border px-3 py-2 text-xs font-bold shadow-sm
                                    {{ $full ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                                            <span class="truncate max-w-[180px]">{{ $qetaa->QetaaName ?? '-' }}</span>

                                            <span
                                                class="inline-flex items-center justify-center min-w-[32px] h-7 rounded-full px-2
                                        {{ $full ? 'bg-red-200 text-red-900' : 'bg-amber-200 text-amber-900' }}">
                                                {{ $qetaa->booked_count }}
                                            </span>

                                            @if ($full)
                                                <span class="hidden sm:inline text-[11px] text-red-700">
                                                    مكتمل
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0 xl:w-[280px] xl:pt-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                            <div class="text-sm font-extrabold text-slate-800 mb-3">
                                إجراءات سريعة
                            </div>

                            <div class="grid grid-cols-1 gap-2">
                                <a href="{{ route('eventBookingFinance.create', $event->SeasonEventID) }}"
                                    class="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold inline-flex items-center justify-center transition-colors duration-200 shadow-sm">
                                    إضافة حجز شخص
                                </a>

                                <a href="{{ route('eventBookingFinance.createGuestFamily', $event->SeasonEventID) }}"
                                    class="h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold inline-flex items-center justify-center transition-colors duration-200 shadow-sm">
                                    إضافة حجز ضيف / أهالي
                                </a>

                                <a href="{{ route('eventBookingFinance.selector') }}"
                                    class="h-11 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-bold inline-flex items-center justify-center transition-colors duration-200">
                                    رجوع
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <details class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden group">
            <summary
                class="cursor-pointer list-none px-4 py-3 bg-gradient-to-l from-slate-50 to-white border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold">
                        📊
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">الملخص السريع</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">إحصائيات اليوم المحدد + الإجمالي</p>
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

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 font-extrabold text-sm">
                                إجمالي يوم {{ \Carbon\Carbon::parse($selectedSummaryDate)->format('Y-m-d') }}
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-3 text-center">
                                <div class="text-[11px] text-slate-500 mb-1">عدد الحجوزات</div>
                                <div
                                    class="text-lg font-extrabold text-slate-800 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($selectedDaySummary['people_count']) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-emerald-700 mb-1">المحصّل</div>
                                <div
                                    class="text-lg font-extrabold text-emerald-700 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($selectedDaySummary['payments_amount'], 2) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-red-100 bg-red-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-red-700 mb-1">المرتجع</div>
                                <div
                                    class="text-lg font-extrabold text-red-700 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($selectedDaySummary['refund_amount'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 font-extrabold text-sm">
                                إجمالي الحجز
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-3 text-center">
                                <div class="text-[11px] text-slate-500 mb-1">عدد الحجوزات</div>
                                <div
                                    class="text-lg font-extrabold text-slate-800 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($totalSummary['people_count']) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-emerald-700 mb-1">المحصّل</div>
                                <div
                                    class="text-lg font-extrabold text-emerald-700 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($totalSummary['payments_amount'], 2) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-red-100 bg-red-50/70 px-2 py-3 text-center">
                                <div class="text-[11px] text-red-700 mb-1">المرتجع</div>
                                <div
                                    class="text-lg font-extrabold text-red-700 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($totalSummary['refund_amount'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </details>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <x-data-table title="قائمة الحجوزات" :data="$bookings" :columns="[
                [
                    'key' => 'BookingCode',
                    'label' => 'الكود',
                    'type' => 'text',
                    'cssClass' => 'text-sm font-bold text-slate-900',
                ],
                // [
                //     'key' => 'BookingEntityType',
                //     'label' => 'النوع',
                //     'type' => 'text',
                //     'cssClass' => 'text-sm text-slate-700',
                // ],
                [
                    'key' => 'PersonFullName',
                    'label' => 'الاسم',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-900 font-semibold',
                ],
                [
                    'key' => 'PersonPersonalMobileNumber',
                    'label' => 'الموبايل',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-700',
                ],
                [
                    'key' => 'QetaaNames',
                    'label' => 'القطاع',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-700',
                ],
                [
                    'key' => 'ShirtSize',
                    'label' => 'المقاس',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-700',
                ],
                [
                    'key' => 'BookingStatusText',
                    'label' => 'الحالة',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-amber-700 font-semibold',
                ],
                [
                    'key' => 'FinalRequiredAmountFormatted',
                    'label' => 'المطلوب',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-700 font-semibold',
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
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'show',
                    'label' => 'عرض',
                    'route' => route('eventBookingFinance.show', ':id'),
                    'idField' => 'SeasonEventParticipantFinanceID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200 ml-2',
                ],
            ]" :searchable="true"
                :sortable="true" :pagination="true" :per-page="10" />
        </div>
    </div>
@endsection
