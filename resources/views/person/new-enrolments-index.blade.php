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

        <x-data-table :data="$bookings" title="حجوزات الفعالية" :columns="[
            [
                'key' => 'PersonFullName',
                'label' => 'الاسم',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'PersonID',
                'label' => 'PersonID',
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
                'key' => 'FirstPaymentDate',
                'label' => 'أول دفعة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'LastPaymentDate',
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
                'name' => 'refund',
                'label' => 'استرداد كامل',
                'route' => route('eventBookingFinance.refundPage', ':id'),
                'idField' => 'SeasonEventParticipantFinanceID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200',
            ],
        ]" :searchable="true"
            :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
