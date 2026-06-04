@extends('layouts.app', ['pageTitle' => 'بيانات التحكم' ?? ''])


@section('content')
    <div class="container mx-auto px-4 py-8">

        <div class="flex justify-start mb-3">
            <a href="{{ route('export.scouts.excel', auth()->id()) }}"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                تحميل Excel
            </a>
        </div>

        <x-data-table :data="$persons" title="إدارة المستخدمين " :add-button="[
            'label' => 'إضافة  مستخدم',
            'route' => route('person.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'PersonID',
                'label' => 'رقم المستخدم',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'full_name',
                'label' => 'الاسم الكامل',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'PersonPersonalMobileNumber',
                'label' => 'رقم الهاتف',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'SanaMarhalaName',
                'label' => 'المرحله',
                'type' => 'label',
                'filter' => true,
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'QetaaName',
                'label' => 'القطاع',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'HasAnsweredQuestions',
                'label' => 'تم الاجابه عن الاسئله',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => 'تعديل',
                'route' => route('person.edit', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => 'مسح',
                'route' => route('person.delete', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
            [
                'name' => 'show',
                'label' => 'عرض',
                'route' => route('person.show', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
