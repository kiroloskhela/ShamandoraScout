@extends('layouts.app', ['pageTitle' => 'سجل التدقيق'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">سجل التدقيق</h1>

        <form method="GET" action="{{ route('audit-logs.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-3 bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
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
                <label class="block text-sm text-gray-600 mb-1">{{ __('Search') }}</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="مسار / إجراء / اسم"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
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
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">تصفية</button>
                <a href="{{ route('audit-logs.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">إعادة تعيين</a>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow border border-gray-100 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-right">#</th>
                        <th class="px-3 py-2 text-right">الوقت</th>
                        <th class="px-3 py-2 text-right">الفاعل</th>
                        <th class="px-3 py-2 text-right">الإجراء</th>
                        <th class="px-3 py-2 text-right">{{ __('Status') }}</th>
                        <th class="px-3 py-2 text-right">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-gray-100 hover:bg-gray-50 align-top">
                            <td class="px-3 py-2 font-mono">{{ $log->id }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-2">
                                <div>{{ $log->actor_name ?: '—' }}</div>
                                <div class="text-xs text-gray-500">#{{ $log->person_id ?: '—' }}</div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="font-semibold">{{ $log->action }}</div>
                                <div class="text-xs text-gray-500 font-mono break-all">{{ $log->path }}</div>
                                @if (!empty($log->request_payload))
                                    <details class="mt-1">
                                        <summary class="cursor-pointer text-xs text-blue-700">{{ __('Data') }}</summary>
                                        <pre class="text-[11px] bg-gray-50 p-2 rounded mt-1 overflow-x-auto max-w-xl">{{ json_encode($log->request_payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $log->response_status ?? '—' }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $log->ip }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-gray-500">لا توجد سجلات بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
