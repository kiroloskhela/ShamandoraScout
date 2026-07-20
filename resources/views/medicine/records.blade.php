@extends('layouts.app', ['pageTitle' => __('Medicine dispense log')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        <x-data-table :data="$records->toArray()" :title="__('Medicine dispense log')" :header-buttons="[
            [
                'label' => __('Dispense medicine'),
                'route' => route('medicine.dispense'),
                'cssClass' =>
                    'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
            [
                'label' => __('Medicine stock'),
                'route' => route('medicine.index'),
                'cssClass' =>
                    'bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
        ]" :columns="[
            [
                'key' => 'MedicineDispenseID',
                'label' => __('Number'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MedicineName',
                'label' => __('Medicine'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'MedicineTypeLabel',
                'label' => __('Gender'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
                'filter' => true,
            ],
            [
                'key' => 'PersonName',
                'label' => __('Person'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'LocationName',
                'label' => __('Dispense location'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
                'filter' => true,
            ],
            [
                'key' => 'GiverName',
                'label' => __('Dispensed by'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'QuantityText',
                'label' => __('Quantity'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'DispensedAtText',
                'label' => __('Dispense time'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'Notes',
                'label' => __('Notes'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
