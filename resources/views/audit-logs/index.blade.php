@extends('layouts.app', ['pageTitle' => __('Audit log')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('Audit log') }}</h1>

        <form method="GET" action="{{ route('audit-logs.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-3 bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('Person ID') }}</label>
                <input type="number" name="person_id" value="{{ request('person_id') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الطريقة</label>
                <select name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                    <option value="">الكل</option>
                    @foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(request('method') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">تصفية</button>
                <a href="{{ route('audit-logs.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">إعادة تعيين</a>
            </div>
        </form>

        <x-data-table :data="$logs" :title="__('Audit log')" tableId="AuditLogsTable" :columns="[
            [
                'key' => 'id',
                'label' => '#',
                'type' => 'text',
                'cssClass' => 'text-sm font-mono text-gray-900',
            ],
            [
                'key' => 'created_at',
                'label' => __('Time'),
                'type' => 'text',
                'cssClass' => 'text-sm whitespace-nowrap text-gray-900',
            ],
            [
                'key' => 'actor_name',
                'label' => __('Actor'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'person_id',
                'label' => __('Person ID'),
                'type' => 'text',
                'cssClass' => 'text-sm font-mono text-gray-500',
            ],
            [
                'key' => 'action',
                'label' => __('Action'),
                'type' => 'text',
                'cssClass' => 'text-sm font-semibold text-gray-900',
            ],
            [
                'key' => 'path',
                'label' => __('Path'),
                'type' => 'text',
                'cssClass' => 'text-xs font-mono text-gray-500 break-all',
            ],
            [
                'key' => 'response_status',
                'label' => __('Status'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'ip',
                'label' => 'IP',
                'type' => 'text',
                'cssClass' => 'text-xs font-mono text-gray-500',
            ],
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="50" />
    </div>
@endsection
