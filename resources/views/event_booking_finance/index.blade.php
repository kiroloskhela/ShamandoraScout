@extends('layouts.app', ['pageTitle' => __('Event bookings')])

@section('content')
    <div class="container mx-auto px-4 py-6" dir="rtl">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 dark:bg-green-900/40 border border-green-300 dark:border-slate-700 text-green-800 dark:text-green-200 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="mb-4 rounded-lg bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-slate-700 text-red-800 dark:text-red-200 px-4 py-3">
                {{ $errors->first('general') }}
            </div>
        @endif

        <div class="mb-5 rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="bg-white dark:bg-slate-900 px-5 py-5">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-200 flex items-center justify-center text-xl shadow-sm">
                                🎟️
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">{{ __('Event bookings') }}</h2>

                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-200 px-3 py-1 text-[11px] font-bold">
                                        {{ $event->EventTypeName }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Follow up event bookings and manage people, guests, and families') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2.5 text-xs font-medium">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-700 dark:text-slate-200">
                                <span class="text-slate-400 dark:text-slate-500">{{ __('Season') }}</span>
                                <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ $event->SeasonName }}
                                    ({{ $event->SeasonYear }})</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-blue-200 dark:border-slate-700 bg-blue-50 dark:bg-blue-950/40 px-3 py-2 text-blue-700 dark:text-blue-200">
                                <span class="text-blue-500 dark:text-blue-400">{{ __('Event') }}</span>
                                <span class="font-extrabold">{{ $event->EventName }}</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-emerald-200 dark:border-slate-700 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-2 text-emerald-700 dark:text-emerald-200">
                                <span class="text-emerald-500 dark:text-emerald-400">{{ __('Start') }}</span>
                                <span
                                    class="font-extrabold">{{ \Carbon\Carbon::parse($event->EventStartDate)->format('Y-m-d') }}</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-rose-200 dark:border-slate-700 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-rose-700 dark:text-rose-200">
                                <span class="text-rose-500 dark:text-rose-400">{{ __('End') }}</span>
                                <span
                                    class="font-extrabold">{{ \Carbon\Carbon::parse($event->EventEndDate)->format('Y-m-d') }}</span>
                            </span>
                        </div>

                        @if (isset($qetaaCounts) && $qetaaCounts->count())
                            <div class="mt-5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Bookings count by sector') }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Quick view of each sector status') }}</div>
                                    </div>

                                    <div class="text-xs text-slate-400 dark:text-slate-500">
                                        {{ __(':count sector', ['count' => $qetaaCounts->count()]) }}
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2.5">
                                    {{-- Total chip --}}
                                    @php
                                        $totalBooked = $qetaaCounts->sum('booked_count');
                                    @endphp
                                    <div
                                        class="inline-flex items-center gap-2 rounded-2xl border px-3 py-2 text-xs font-bold shadow-sm border-blue-200 dark:border-slate-700 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-200">
                                        <span>{{ __('Total') }}</span>
                                        <span
                                            class="inline-flex items-center justify-center min-w-[32px] h-7 rounded-full px-2 bg-blue-200 dark:bg-blue-900/60 text-blue-900 dark:text-blue-100">
                                            {{ $totalBooked }}
                                        </span>
                                    </div>

                                    @foreach ($qetaaCounts as $qetaa)
                                        @php
                                            $full = $qetaa->booked_count >= 50;
                                        @endphp

                                        <div
                                            class="inline-flex items-center gap-2 rounded-2xl border px-3 py-2 text-xs font-bold shadow-sm
                                    {{ $full ? 'border-red-200 dark:border-slate-700 bg-red-50 dark:bg-red-950/40 text-red-800 dark:text-red-200' : 'border-amber-200 dark:border-slate-700 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200' }}">
                                            <span class="truncate max-w-[180px]">{{ $qetaa->QetaaName ?? '-' }}</span>

                                            <span
                                                class="inline-flex items-center justify-center min-w-[32px] h-7 rounded-full px-2
                                        {{ $full ? 'bg-red-200 dark:bg-red-900/60 text-red-900 dark:text-red-100' : 'bg-amber-200 dark:bg-amber-900/60 text-amber-900 dark:text-amber-100' }}">
                                                {{ $qetaa->booked_count }}
                                            </span>

                                            @if ($full)
                                                <span class="hidden sm:inline text-[11px] text-red-700 dark:text-red-300">{{ __('Full') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0 xl:w-[280px] xl:pt-6">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3 shadow-sm">
                            <div class="text-sm font-extrabold text-slate-800 dark:text-slate-100 mb-3">{{ __('Quick actions') }}</div>

                            <div class="grid grid-cols-1 gap-2">
                                <a href="{{ route('eventBookingFinance.create', $event->SeasonEventID) }}"
                                    class="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-extrabold inline-flex items-center justify-center transition-colors duration-200 shadow-md border-2 border-blue-300/80">
                                    {{ __('Add person booking') }}
                                </a>

                                <a href="{{ route('eventBookingFinance.createGuestFamily', $event->SeasonEventID) }}"
                                    class="h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-extrabold inline-flex items-center justify-center transition-colors duration-200 shadow-md border-2 border-emerald-300/80">
                                    {{ __('Add guest / family booking') }}
                                </a>

                                <a href="{{ route('eventBookingFinance.exportToday', $event->SeasonEventID) }}?summary_date={{ $selectedSummaryDate }}"
                                    class="h-11 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-extrabold inline-flex items-center justify-center transition-colors duration-200 shadow-md border-2 border-cyan-200/90">
                                    {{ __("Download today's CSV") }}
                                </a>

                                <a href="{{ route('eventBookingFinance.exportAll', $event->SeasonEventID) }}"
                                    class="h-11 rounded-xl bg-slate-600 hover:bg-slate-500 text-white text-sm font-extrabold inline-flex items-center justify-center transition-colors duration-200 shadow-md border-2 border-slate-300 dark:border-slate-600">
                                    {{ __('Download full CSV') }}
                                </a>

                                <a href="{{ route('eventBookingFinance.selector') }}"
                                    class="h-11 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 text-sm font-extrabold inline-flex items-center justify-center transition-colors duration-200 border-2 border-slate-400 dark:border-slate-600 shadow-sm">{{ __('Back') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <details class="mb-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden group">
            <summary
                class="cursor-pointer list-none px-4 py-3 bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-200 flex items-center justify-center text-base font-bold">
                        📊
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Quick summary') }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Selected day stats + totals') }}</p>
                    </div>
                </div>

                <span
                    class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs group-open:rotate-180 transition-transform duration-200">
                    ⌄
                </span>
            </summary>

            <div class="p-4 bg-slate-50/40 dark:bg-slate-800/40 space-y-4">
                <form method="GET" action="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-3">
                    <div class="flex flex-col md:flex-row md:items-end gap-3">
                        <div class="flex-1">
                            <label for="summary_date" class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-2">{{ __('Choose payment day') }}</label>

                            <select name="summary_date" id="summary_date"
                                class="w-full h-11 px-4 border rounded-xl text-right border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:outline-none">
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
                                class="h-11 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition-colors duration-200">{{ __('Apply') }}</button>

                            <a href="{{ route('eventBookingFinance.index', $event->SeasonEventID) }}"
                                class="h-11 px-4 rounded-xl bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-800 dark:text-slate-100 text-sm font-bold transition-colors duration-200 inline-flex items-center">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-200 font-extrabold text-sm">
                                {{ __('Day total') }} {{ \Carbon\Carbon::parse($selectedSummaryDate)->format('Y-m-d') }}
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 py-3 text-center">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-1">{{ __('Bookings count') }}</div>
                                <div
                                    class="text-lg font-extrabold text-slate-800 dark:text-slate-100 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($selectedDaySummary['people_count']) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 dark:border-slate-700 bg-emerald-50/70 dark:bg-emerald-950/40 px-2 py-3 text-center">
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-300 mb-1">{{ __('Collected') }}</div>
                                <div
                                    class="text-lg font-extrabold text-emerald-700 dark:text-emerald-300 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($selectedDaySummary['payments_amount'], 2) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-red-100 dark:border-slate-700 bg-red-50/70 dark:bg-red-950/40 px-2 py-3 text-center">
                                <div class="text-[11px] text-red-700 dark:text-red-300 mb-1">{{ __('Refunded') }}</div>
                                <div
                                    class="text-lg font-extrabold text-red-700 dark:text-red-300 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($selectedDaySummary['refund_amount'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
                        <div class="text-center mb-4">
                            <div
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-200 font-extrabold text-sm">{{ __('Booking total') }}</div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 py-3 text-center">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-1">{{ __('Bookings count') }}</div>
                                <div
                                    class="text-lg font-extrabold text-slate-800 dark:text-slate-100 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($totalSummary['people_count']) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 dark:border-slate-700 bg-emerald-50/70 dark:bg-emerald-950/40 px-2 py-3 text-center">
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-300 mb-1">{{ __('Collected') }}</div>
                                <div
                                    class="text-lg font-extrabold text-emerald-700 dark:text-emerald-300 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($totalSummary['payments_amount'], 2) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-red-100 dark:border-slate-700 bg-red-50/70 dark:bg-red-950/40 px-2 py-3 text-center">
                                <div class="text-[11px] text-red-700 dark:text-red-300 mb-1">{{ __('Refunded') }}</div>
                                <div
                                    class="text-lg font-extrabold text-red-700 dark:text-red-300 blur-sm hover:blur-none transition duration-200 select-none">
                                    {{ number_format($totalSummary['refund_amount'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </details>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm dark:border dark:border-slate-700 border border-slate-200 p-4">
            <x-data-table title="{{ __('Bookings list') }}" :data="$bookings" :columns="[
                [
                    'key' => 'BookingCode',
                    'label' => __('Code'),
                    'type' => 'text',
                    'cssClass' => 'text-sm font-bold text-slate-900 dark:text-slate-100',
                ],
                // [
                //     'key' => 'BookingEntityType',
                //     'label' => __('Gender'),
                //     'type' => 'text',
                //     'cssClass' => 'text-sm text-slate-700 dark:text-slate-200',
                // ],
                [
                    'key' => 'PersonFullName',
                    'label' => __('Name'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-900 dark:text-slate-100 font-semibold',
                ],
                [
                    'key' => 'PersonPersonalMobileNumber',
                    'label' => __('Mobile'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-700 dark:text-slate-200',
                ],
                [
                    'key' => 'QetaaNames',
                    'label' => __('Sector'),
                    'type' => 'text',
                    'filter' => true,
                    'cssClass' => 'text-sm text-slate-700 dark:text-slate-200',
                ],
                [
                    'key' => 'ShirtSize',
                    'label' => __('Size'),
                    'type' => 'text',
                    'filter' => true,
                    'cssClass' => 'text-sm text-slate-700 dark:text-slate-200',
                ],
                [
                    'key' => 'BookingStatusText',
                    'label' => __('Status'),
                    'type' => 'text',
                    'filter' => true,
                    'cssClass' => 'text-sm text-amber-700 dark:text-amber-300 font-semibold',
                ],
                [
                    'key' => 'FinalRequiredAmountFormatted',
                    'label' => __('Required amount'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-slate-700 dark:text-slate-200 font-semibold',
                ],
                [
                    'key' => 'AmountPaidFormatted',
                    'label' => __('Paid'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-green-700 dark:text-green-300 font-semibold',
                ],
                [
                    'key' => 'RemainingAmountFormatted',
                    'label' => __('Remaining'),
                    'type' => 'text',
                    'filter' => true,
                    'cssClass' => 'text-sm text-red-700 dark:text-red-300 font-semibold',
                ],
                [
                    'key' => 'PaymentsProgress',
                    'label' => __('Installments count'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100',
                ],
                [
                    'key' => 'FirstPaymentDateFormatted',
                    'label' => __('First payment'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100',
                ],
                [
                    'key' => 'LastPaymentDateFormatted',
                    'label' => __('Last payment'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100',
                ],
            ]" :actions="[
                [
                    'name' => 'add_installment',
                    'label' => __('Add payment'),
                    'route' => route('eventBookingFinance.createInstallment', ':id'),
                    'idField' => 'SeasonEventParticipantFinanceID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'edit_last_payment',
                    'label' => __('Edit last payment'),
                    'route' => route('eventBookingFinance.editLastPayment', ':id'),
                    'idField' => 'LastPaymentID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'print_receipt',
                    'label' => __('Print last receipt'),
                    'route' => route('eventBookingFinance.printReceipt', ':id'),
                    'idField' => 'LastPaymentID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'refund_full',
                    'label' => __('Full refund'),
                    'route' => route('eventBookingFinance.refundPage', ':id'),
                    'idField' => 'SeasonEventParticipantFinanceID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'refund_partial',
                    'label' => __('Partial refund with deduction'),
                    'route' => route('eventBookingFinance.partialRefundPage', ':id'),
                    'idField' => 'SeasonEventParticipantFinanceID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'show',
                    'label' => __('View'),
                    'route' => route('eventBookingFinance.show', ':id'),
                    'idField' => 'SeasonEventParticipantFinanceID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200 ml-2',
                ],
            ]" :searchable="true"
                :sortable="true" :pagination="true" :per-page="25"
                :server-filters="true"
                :filter-options="$filterOptions ?? []"
                :active-server-filters="$activeServerFilters ?? []" />
        </div>
    </div>
@endsection
