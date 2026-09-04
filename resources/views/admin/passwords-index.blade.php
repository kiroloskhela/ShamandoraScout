@extends('layouts.app', ['pageTitle' => __('Password management')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('success'))
            <div role="status"
                class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/40 px-4 py-3 text-sm font-semibold text-emerald-800 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div role="alert"
                class="mb-4 rounded-lg border border-rose-200 bg-rose-50 dark:bg-rose-900/40 px-4 py-3 text-sm font-semibold text-rose-800 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif
        <x-data-table :data="$users" :title="__('Password management')" tableId="PasswordsTable" :columns="[
            [
                'key' => 'PersonID',
                'label' => __('User ID'),
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
                'label' => __('Edit password'),
                'route' => route('admin.passwords.edit', ':id'),
                'idField' => 'PersonID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="50" />
    </div>
@endsection
