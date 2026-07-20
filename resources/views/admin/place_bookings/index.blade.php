@extends('layouts.app', ['pageTitle' => __('Place booking requests')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">{{ __('Place booking requests') }}</h1>
            <p class="text-gray-600 dark:text-slate-300">{{ __('Review and approve/reject user requests') }}</p>
        </div>

        {{-- Alerts --}}
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

        {{-- Summary Cards --}}
        @php
            $pendingCount = $bookings->where('Status', 'pending')->count();
            $approvedCount = $bookings->where('Status', 'approved')->count();
            $rejectedCount = $bookings->where('Status', 'rejected')->count();
            $allCount = $bookings->count();
        @endphp

        <div class="grid md:grid-cols-4 gap-4 mb-6 text-center">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-4 border-2 border-blue-200 dark:border-slate-700">
                <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">{{ __('Total') }}</div>
                <div class="text-2xl font-bold text-blue-800 dark:text-blue-200">{{ $allCount }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-4 border-2 border-yellow-200 dark:border-slate-700">
                <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">{{ __('Pending review') }}</div>
                <div class="text-2xl font-bold text-yellow-700 dark:text-amber-200">{{ $pendingCount }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-4 border-2 border-green-200 dark:border-slate-700">
                <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">{{ __('Approved') }}</div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-200">{{ $approvedCount }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-4 border-2 border-red-200 dark:border-slate-700">
                <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">{{ __('Rejected') }}</div>
                <div class="text-2xl font-bold text-red-700 dark:text-red-200">{{ $rejectedCount }}</div>
            </div>
        </div>

        {{-- Filters --}}
        @php
            $filter = request('status', 'all');
            $filtered = $bookings;
            if ($filter !== 'all') {
                $filtered = $bookings->where('Status', $filter);
            }
        @endphp

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 mb-6 border-2 border-blue-200 dark:border-slate-700">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="font-bold text-gray-800 dark:text-slate-100">{{ __('Filter') }}</div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'all']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'all' ? 'bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border-blue-200 dark:border-slate-700' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        {{ __('All') }}
                    </a>
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'pending']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'pending' ? 'bg-yellow-50 dark:bg-amber-900/40 text-yellow-700 dark:text-amber-200 border-yellow-200 dark:border-slate-700' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">{{ __('Pending review') }}</a>
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'approved']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'approved' ? 'bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-200 border-green-200 dark:border-slate-700' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">{{ __('Approved') }}</a>
                    <a href="{{ route('admin.place_bookings.index', ['status' => 'rejected']) }}"
                        class="px-4 py-2 text-xs rounded-full border transition
                               {{ $filter === 'rejected' ? 'bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-200 border-red-200 dark:border-slate-700' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">{{ __('Rejected') }}</a>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500 dark:text-slate-400">
                {{ __('Note: You can open any request to approve/reject or approve with place/time changes and notes.') }}
            </p>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 border-2 border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr class="text-sm text-slate-700 dark:text-slate-200">
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Request number') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('User') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Location') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Place') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Date') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('From') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('To') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Status') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Sent at') }}</th>
                            <th class="p-3 border-b dark:border-slate-700">{{ __('Details') }}</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-800 dark:text-slate-100">
                        @forelse ($filtered as $idx => $r)
                            @php
                                $rowClass = $idx % 2 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50/40 dark:bg-slate-800/40';
                            @endphp
                            <tr class="border-b dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition {{ $rowClass }}">
                                <td class="p-3 font-semibold text-slate-900 dark:text-slate-100">#{{ $r->BookingID }}</td>
                                <td class="p-3">{{ $r->UserName ?? '—' }}</td>
                                <td class="p-3">{{ $r->LocationName ?? '—' }}</td>
                                <td class="p-3">{{ $r->PlaceName ?? '—' }}</td>
                                <td class="p-3">{{ $r->BookingDate ?? '—' }}</td>
                                <td class="p-3">{{ $r->TimeFrom ?? '—' }}</td>
                                <td class="p-3">{{ $r->TimeTo ?? '—' }}</td>

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
                                    {{ !empty($r->created_at) ? \Carbon\Carbon::parse($r->created_at)->format('Y-m-d') : '—' }}
                                </td>

                                <td class="p-3">
                                    <a href="{{ route('admin.place_bookings.show', $r->BookingID) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 text-xs rounded-lg
                                               bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition border border-blue-200 dark:border-slate-700">
                                        {{ __('Open') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-8 text-gray-500 dark:text-slate-400" colspan="10">{{ __('No requests match the filter.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
