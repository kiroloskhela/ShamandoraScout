@extends('layouts.app', ['pageTitle' => __('Request status')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">{{ __('Request status') }}</h1>
            <p class="text-gray-600 dark:text-slate-300">{{ __('Track your custody request status') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-3 rounded-lg bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-slate-700 text-green-700 dark:text-green-200 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-3 rounded-lg bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-slate-700 text-red-700 dark:text-red-200 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 border-2 border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <h2 class="text-lg font-bold text-gray-800 dark:text-slate-100">{{ __('Request list') }}</h2>
                <a href="{{ route('custody_requests.create') }}"
                    class="inline-flex items-center justify-center h-10 px-6 text-sm font-medium rounded-full
                       bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-200 hover:bg-green-100 dark:hover:bg-green-900/60 transition border border-green-200 dark:border-slate-700">
                    {{ __('New custody request') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr class="text-sm text-slate-700 dark:text-slate-200">
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Request number') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('From') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('To') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Data') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Status') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Reviewer') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Details') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800 dark:text-slate-100">
                        @forelse ($requests as $idx => $r)
                            <tr
                                class="border-b dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition {{ $idx % 2 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50/40 dark:bg-slate-800/40' }}">
                                <td class="p-3 font-semibold text-slate-900 dark:text-slate-100">#{{ $r->RequestID }}</td>
                                <td class="p-3">{{ $r->DateFrom }}</td>
                                <td class="p-3">{{ $r->DateTo }}</td>

                                <td class="p-3">
                                    <div class="flex flex-col gap-1 items-center">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-200 dark:border-slate-700">
                                            {{ __('Sector:') }} {{ $r->QetaaName ?? '—' }}
                                        </span>
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                            {{ __('Event:') }} {{ $r->EventTypeName ?? '—' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="p-3">
                                    @if ($r->Status === 'pending')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-yellow-50 dark:bg-amber-900/40 text-yellow-700 dark:text-amber-200 border border-yellow-200 dark:border-slate-700">{{ __('Pending review') }}</span>
                                    @elseif ($r->Status === 'approved')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-200 border border-green-200 dark:border-slate-700">{{ __('Approved') }}</span>
                                    @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-200 border border-red-200 dark:border-slate-700">{{ __('Rejected') }}</span>
                                    @endif
                                </td>

                                <td class="p-3 text-gray-600 dark:text-slate-300">
                                    {{ $r->ReviewerName ?? '—' }}
                                </td>

                                <td class="p-3">
                                    <a href="{{ route('custody_requests.show', $r->RequestID) }}"
                                        class="px-3 py-2 text-xs rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition border border-blue-200 dark:border-slate-700">{{ __('View') }}</a>
                                </td>

                                <td class="p-3">
                                    @if ($r->Status === 'pending')
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('custody_requests.edit', $r->RequestID) }}"
                                                class="px-3 py-2 text-xs rounded-lg bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-200 hover:bg-green-100 dark:hover:bg-green-900/60 transition border border-green-200 dark:border-slate-700">{{ __('Edit') }}</a>

                                            <form method="POST"
                                                action="{{ route('custody_requests.destroy', $r->RequestID) }}"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this request?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-2 text-xs rounded-lg bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-200 hover:bg-red-100 dark:hover:bg-red-900/60 transition border border-red-200 dark:border-slate-700">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-gray-500 dark:text-slate-400" colspan="8">{{ __('No requests yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
