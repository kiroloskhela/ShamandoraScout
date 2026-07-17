@extends('layouts.app', ['pageTitle' => __('Manage event finance plans')])

@section('content')
    <div class="container mx-auto px-4 py-8">
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

        <x-data-table :data="$finance" tableId="AddingFinance" title="{{ __('Manage event finance plans') }}" :add-button="[
            'label' => __('Add finance plan'),
            'route' => route('finance.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]"
            :columns="[
                [
                    'key' => 'SeasonName',
                    'label' => __('Season'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                ],
                [
                    'key' => 'SeasonYear',
                    'label' => __('Year'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                ],
                [
                    'key' => 'EventDisplayName',
                    'label' => __('Event'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                ],
                [
                    'key' => 'EventStartDate',
                    'label' => __('Event start'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'EventEndDate',
                    'label' => __('Event end'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'MaxInstallmentsNumber',
                    'label' => __('Max installments'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'MinimumDeposit',
                    'label' => __('Minimum deposit'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'AllowBelowMinimumDepositText',
                    'label' => __('Allow below minimum deposit'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                ],
                [
                    'key' => 'IntervalsCount',
                    'label' => __('Intervals count'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'CanEditDeleteText',
                    'label' => __('Editable/deletable'),
                    'type' => 'text',
                    'cssClass' => 'text-sm font-medium',
                ],
            ]" :actions="[
                [
                    'name' => 'edit',
                    'label' => __('Edit'),
                    'route' => route('finance.edit', ':id'),
                    'idField' => 'SeasonEventID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => __('Delete'),
                    'route' => route('finance.delete', ':id'),
                    'idField' => 'SeasonEventID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200',
                ],
            ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
