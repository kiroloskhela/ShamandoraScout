@extends('layouts.app', ['pageTitle' => __('WhatsApp campaigns')])

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('WhatsApp campaigns') }}</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('whatsapp.campaigns.csv-template') }}"
                class="bg-white border border-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold text-sm">{{ __('Download CSV template') }}</a>
            <a href="{{ route('whatsapp.campaigns.create-csv') }}"
                class="bg-teal-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">{{ __('Campaign from CSV') }}</a>
            <a href="{{ route('whatsapp.campaigns.create') }}"
                class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold text-sm">{{ __('Campaign from directory') }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow border border-gray-100 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-right">#</th>
                    <th class="px-4 py-3 text-right">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Recipients') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Created') }}</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $c)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3">{{ $c->id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                @if($c->status === 'running') bg-emerald-100 text-emerald-800
                                @elseif($c->status === 'paused') bg-amber-100 text-amber-900
                                @elseif($c->status === 'completed') bg-blue-100 text-blue-800
                                @elseif($c->status === 'cancelled' || $c->status === 'failed') bg-red-100 text-red-800
                                @elseif($c->status === 'queued') bg-indigo-100 text-indigo-800
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $c->recipients_count }}</td>
                        <td class="px-4 py-3">{{ optional($c->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('whatsapp.campaigns.show', $c) }}" class="text-emerald-700 font-semibold">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No campaigns yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
</div>
@endsection
