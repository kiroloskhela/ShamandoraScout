@extends('layouts.app', ['pageTitle' => __('New enrolments analytics')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$analytics" :title="__('New enrolments analytics')" :columns="[
            [
                'key' => 'QetaaName',
                'label' => __('Sector'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'CountOfRequests',
                'label' => __('Requests count'),
                'type' => 'label',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'CountOfApprovedRequests',
                'label' => __('Approved requests count'),
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
        ]" :searchable="true" :sortable="true"
            :pagination="true" :per-page="10" />
    </div>
@endsection
