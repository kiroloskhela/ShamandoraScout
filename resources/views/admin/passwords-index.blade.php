@extends('layouts.app', ['pageTitle' => 'إدارة كلمات المرور'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$users" title="إدارة كلمات المرور" tableId="PasswordsTable" :columns="[
            [
                'key' => 'PersonID',
                'label' => 'رقم المستخدم',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 text-center',
            ],
            [
                'key' => 'FullName',
                'label' => __('Full name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm text-center',
            ],
            [
                'key' => 'ShamandoraCode',
                'label' => __('Shamandora code'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 text-center',
            ],
            [
                'key' => 'PersonPersonalMobileNumber',
                'label' => __('Phone number'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 text-center',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => 'تعديل كلمة السر',
                'route' => route('admin.passwords.edit', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="50" />
    </div>
@endsection
