@extends('layouts.app', ['pageTitle' => 'الملتحقين الجدد - عام 2024'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$persons" title="إدارة المستخدمين " :columns="[
            [
                'key' => 'PersonID',
                'label' => 'الطلب',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'full_name',
                'label' => 'الاسم',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'SanaMarhalaName',
                'label' => 'المرحلة',
                'type' => 'label',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'QetaaName',
                'label' => 'القطاع',
                'type' => 'label',
                'cssClass' => 'text-sm text-gray-800 font-medium',
            ],
            [
                'key' => 'RaqamQawmy',
                'label' => 'الرقم القومي',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'PersonPersonalMobileNumber',
                'label' => 'رقم الموبايل',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'HasAnsweredQuestions',
                'label' => 'هل أكمل الأسئلة؟',
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
            [
                'key' => 'IsApproved',
                'label' => 'الحالة',
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold',
            ],
        ]" :actions="[
            [
                'name' => 'approve',
                'label' => 'موافقة',
                'route' => route('person.new-enrolments-approve', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'reject',
                'label' => 'رفض',
                'route' => route('person.new-enrolments-delete', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'show',
                'label' => 'عرض',
                'route' => route('person.new-enrolments-show', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'fill',
                'label' => 'إكمال الأسئلة',
                'route' => route('person.entry-questions-liveform', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200',
            ],
        ]" :searchable="true"
            :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
