@extends('layouts.app', ['pageTitle' => 'إدارة الإعدادات المالية'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$data" title="الإعدادات المالية للفعاليات" :add-button="[
            'label' => 'إضافة إعداد مالي',
            'route' => route('seasonEventFinance.create'),
            'cssClass' => 'bg-blue-600 text-white px-4 py-2 rounded-lg',
        ]" :columns="[
            ['key' => 'SeasonName', 'label' => 'الموسم'],
            ['key' => 'SeasonYear', 'label' => 'السنة'],
            ['key' => 'EventName', 'label' => 'الفعالية'],
            ['key' => 'SupportedPrice', 'label' => 'السعر المدعوم'],
            ['key' => 'ActualMaxPrice', 'label' => 'السعر الفعلي'],
            ['key' => 'InstallmentsNumber', 'label' => 'عدد الأقساط'],
        ]"
            :actions="[
                [
                    'name' => 'edit',
                    'label' => 'تعديل',
                    'route' => route('seasonEventFinance.edit', ':id'),
                    'idField' => 'SeasonEventID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => 'حذف',
                    'route' => route('seasonEventFinance.delete', ':id'),
                    'idField' => 'SeasonEventID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
                ],
            ]" />
    </div>
@endsection
