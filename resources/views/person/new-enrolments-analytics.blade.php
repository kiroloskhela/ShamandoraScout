@extends('layouts.app', ['pageTitle' => 'تحليل الطلبات الجديدة'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$analytics" title="تحليل الطلبات الجديدة" :columns="[
            [
                'key' => 'QetaaName',
                'label' => __('Sector'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'CountOfRequests',
                'label' => 'عدد الطلبات',
                'type' => 'label',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'CountOfApprovedRequests',
                'label' => 'عدد الطلبات الموافق عليها',
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
        ]" :searchable="true" :sortable="true"
            :pagination="true" :per-page="10" />
    </div>
@endsection
