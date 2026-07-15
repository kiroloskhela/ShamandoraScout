@extends('layouts.app' , ['pageTitle' => "إدارة روابط Drive"])

@section('content')
<div class="container mx-auto px-4 py-8">
    <x-data-table :data="$media" title="إدارة روابط Drive" :add-button="[
        'label' => 'إضافة رابط',
            'route' => route('media.create'),
            'cssClass' => 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200'
        ]" :columns="[
          
            [
                'key' => 'SeasonName',
                'label' => __('Season'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium'
            ],
             [
                'key' => 'SeasonYear',
                'label' => __('Year'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium'
            ],
              [
                'key' => 'EventName',
                'label' => 'الفعالية ',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium'
            ],
            [
                'key' => 'DriveLink',
                'label' => 'رابط Drive',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ]
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('media.edit', ':id'),
                'idField' => 'MediaID',
                'cssClass' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2'
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('media.delete', ':id'),
                'idField' => 'MediaID',
                'cssClass' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200'
            ]
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
</div>
@endsection