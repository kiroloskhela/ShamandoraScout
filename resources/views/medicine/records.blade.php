@extends('layouts.app', ['pageTitle' => 'سجل صرف الأدوية'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center" dir="rtl">
                {{ session('status') }}
            </div>
        @endif

        <x-data-table :data="$records->toArray()" title="سجل صرف الأدوية" :header-buttons="[
            [
                'label' => 'صرف دواء',
                'route' => route('medicine.dispense'),
                'cssClass' =>
                    'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
            [
                'label' => 'مخزون الأدوية',
                'route' => route('medicine.index'),
                'cssClass' =>
                    'bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
        ]" :columns="[
            [
                'key' => 'MedicineDispenseID',
                'label' => 'رقم',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MedicineName',
                'label' => 'الدواء',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'MedicineTypeLabel',
                'label' => 'النوع',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
                'filter' => true,
            ],
            [
                'key' => 'PersonName',
                'label' => 'الشخص',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'LocationName',
                'label' => 'مكان الصرف',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
                'filter' => true,
            ],
            [
                'key' => 'GiverName',
                'label' => 'تم الصرف بواسطة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'QuantityText',
                'label' => 'الكمية',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'DispensedAtText',
                'label' => 'وقت الصرف',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'Notes',
                'label' => 'ملاحظات',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
