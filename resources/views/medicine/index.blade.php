@extends('layouts.app', ['pageTitle' => __('Medicine stock')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm text-center">
                {{ session('error') }}
            </div>
        @endif

        <x-data-table :data="$medicines->toArray()" title="{{ __('Medicine stock') }}" :add-button="[
            'label' => __('Add medicine'),
            'route' => route('medicine.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :header-buttons="[
            [
                'label' => __('Dispense medicine'),
                'route' => route('medicine.dispense'),
                'cssClass' =>
                    'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
            [
                'label' => __('Dispense log'),
                'route' => route('medicine.records'),
                'cssClass' =>
                    'bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
            [
                'label' => __('Reserve medicine'),
                'route' => route('medicine.locks'),
                'cssClass' =>
                    'bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
            [
                'label' => __('Medicine locations'),
                'route' => route('medicine.locations'),
                'cssClass' =>
                    'bg-cyan-700 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
        ]" :columns="[
            [
                'key' => 'MedicineID',
                'label' => __('Number'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MedicineName',
                'label' => __('Medicine name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'TypeLabel',
                'label' => __('Gender'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
                'filter' => true,
            ],
            [
                'key' => 'ExpirationDate',
                'label' => __('Expiry date'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'AmountText',
                'label' => __('Total stock'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'AvailableText',
                'label' => __('Available'),
                'type' => 'text',
                'cssClass' => 'text-sm text-emerald-700 font-bold',
            ],
            [
                'key' => 'LockedText',
                'label' => __('Locked'),
                'type' => 'text',
                'cssClass' => 'text-sm text-amber-700 font-bold',
            ],
            [
                'key' => 'LocationBreakdown',
                'label' => __('Distribution'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700',
            ],
            [
                'key' => 'LockedBreakdown',
                'label' => __('Locked distribution'),
                'type' => 'text',
                'cssClass' => 'text-sm text-amber-700',
            ],
            [
                'key' => 'StatusLabel',
                'label' => __('Status'),
                'type' => 'badge',
                'cssClass' => 'px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800',
                'filter' => true,
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('medicine.edit', ':id'),
                'idField' => 'MedicineID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'stock',
                'label' => __('Distribute'),
                'route' => route('medicine.stock', ':id'),
                'idField' => 'MedicineID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-cyan-700 hover:bg-cyan-800 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('medicine.delete', ':id'),
                'idField' => 'MedicineID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
