@extends('layouts.app', ['pageTitle' => __('Waiting list')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$persons" title="{{ __('Waiting list') }}" tableId="WaitingListTable" :columns="[
            [
                'key' => 'PersonID',
                'label' => __('Request'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'CreatedAt',
                'label' => __('Submitted at'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700 font-medium whitespace-nowrap',
            ],
            [
                'key' => 'FullName',
                'label' => __('Name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'SanaMarhalaName',
                'label' => __('Stage'),
                'type' => 'label',
                'filter' => true,
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'QetaaName',
                'label' => __('Sector'),
                'type' => 'label',
                'filter' => true,
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'RaqamQawmy',
                'label' => __('National ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'PersonPersonalMobileNumber',
                'label' => __('Mobile number'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'HasAnsweredQuestions',
                'label' => __('Completed questions?'),
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
        ]" :actions="[
            [
                'name' => 'migrate',
                'label' => __('Move to enrolment'),
                'route' => route('person.waiting-list-migrate', ':id'),
                'idField' => 'PersonID',
                'method' => 'POST',
                'confirm' => __('Are you sure you want to move this person to the enrolment list?'),
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'show',
                'label' => __('View'),
                'route' => route('person.waiting-list-show', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'decline',
                'label' => __('Reject'),
                'route' => route('person.waiting-list-decline', ':id'),
                'idField' => 'PersonID',
                'method' => 'DELETE',
                'confirm' => __('Are you sure you want to permanently reject and delete this request?'),
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 ml-2',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="25" />
    </div>
@endsection
