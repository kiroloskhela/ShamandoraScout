@extends('layouts.app', ['pageTitle' => __('New enrolments')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$persons->items()" title="{{ __('Manage users') }}" tableId="NewEnrolmentTable" :columns="[
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
            [
                'key' => 'IsApproved',
                'label' => __('Status'),
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
        ]"
            :actions="[
                [
                    'name' => 'approve',
                    'label' => __('Approve'),
                    'disabledLabel' => __('Approved'),
                    'disableWhen' => [
                        'field' => 'IsApproved',
                        'value' => 1,
                    ],
                    'route' => route('person.new-enrolments-approve', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                    'disabledClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-gray-400 cursor-not-allowed ml-2',
                ],
                [
                    'name' => 'reject',
                    'label' => __('Reject'),
                    'route' => route('person.new-enrolments-delete', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'show',
                    'label' => __('View'),
                    'route' => route('person.new-enrolments-show', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'edit',
                    'label' => __('Edit'),
                    'route' => route('person.new-enrolments-edit', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'fill',
                    'label' => __('Complete questions'),
                    'route' => route('person.new-enrolments-resume-questions', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200',
                ],
            ]" :searchable="true" :sortable="true" :pagination="false" :per-page="25" />
        <div class="mt-4">
            {{ $persons->links() }}
        </div>
    </div>
@endsection
