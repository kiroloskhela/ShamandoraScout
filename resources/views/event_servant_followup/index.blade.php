@extends('layouts.app', ['pageTitle' => 'متابعة حجوزات المخدومين'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">بيانات الفعالية</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">الموسم:</span>
                    <span class="text-gray-900">{{ $event->SeasonName }} ({{ $event->SeasonYear }})</span>
                </div>

                <div>
                    <span class="font-semibold text-gray-700">الفعالية:</span>
                    <span class="text-gray-900">{{ $event->EventTypeName }} - {{ $event->EventName }}</span>
                </div>

                <div>
                    <span class="font-semibold text-gray-700">تاريخ البداية:</span>
                    <span class="text-gray-900">{{ $event->EventStartDate }}</span>
                </div>

                <div>
                    <span class="font-semibold text-gray-700">تاريخ النهاية:</span>
                    <span class="text-gray-900">{{ $event->EventEndDate }}</span>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <x-data-table :data="$booked" title="المحجوزين" :columns="[
                [
                    'key' => 'ShamandoraCode',
                    'label' => 'الكود',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-700 font-medium',
                ],
                [
                    'key' => 'PersonFullName',
                    'label' => 'الاسم',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 font-medium',
                ],
                [
                    'key' => 'QetaaName',
                    'label' => 'القطاع',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'SanaMarhalaName',
                    'label' => 'سنة / مرحلة',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'FinalRequiredAmount',
                    'label' => 'المطلوب',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-blue-700 font-medium',
                ],
                [
                    'key' => 'AmountPaid',
                    'label' => 'المدفوع',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-green-700 font-medium',
                ],
                [
                    'key' => 'RemainingAmount',
                    'label' => 'المتبقي',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-red-700 font-medium',
                ],
            ]" :actions="[]" :searchable="true"
                :sortable="true" :pagination="true" :per-page="10" />
        </div>

        <div>
            <x-data-table :data="$waitingList" title="قائمة الانتظار" :columns="[
                [
                    'key' => 'ShamandoraCode',
                    'label' => 'الكود',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-700 font-medium',
                ],
                [
                    'key' => 'PersonFullName',
                    'label' => 'الاسم',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 font-medium',
                ],
                [
                    'key' => 'QetaaName',
                    'label' => 'القطاع',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'SanaMarhalaName',
                    'label' => 'سنة / مرحلة',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
            ]" :actions="[]" :searchable="true"
                :sortable="true" :pagination="true" :per-page="10" />
        </div>
    </div>
@endsection
