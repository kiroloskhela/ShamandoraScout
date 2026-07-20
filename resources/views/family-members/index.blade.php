@extends('layouts.app', ['pageTitle' => 'إدارة أفراد الأسرة'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$familyMembers" title="إدارة أفراد الأسرة" :add-button="[
            'label' => 'إضافة فرد أسرة',
            'route' => route('family-members.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'FamilyID',
                'label' => '#',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700 font-medium',
            ],
            [
                'key' => 'FullName',
                'label' => __('Name'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MobileNumber',
                'label' => __('Mobile number'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'RaqamQawmy',
                'label' => __('National ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'LinkedPersonsCount',
                'label' => 'عدد الأشخاص المرتبطين',
                'type' => 'text',
                'cssClass' => 'text-sm text-blue-700 font-medium',
            ],
            [
                'key' => 'LinkedPersons',
                'label' => 'الأشخاص المرتبطون',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700',
            ],
        ]" :actions="[
            [
                'name' => 'show',
                'label' => __('View'),
                'route' => route('family-members.show', ':id'),
                'idField' => 'FamilyID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 ml-2',
            ],
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('family-members.edit', ':id'),
                'idField' => 'FamilyID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('family-members.delete', ':id'),
                'idField' => 'FamilyID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="25" />
    </div>
@endsection
