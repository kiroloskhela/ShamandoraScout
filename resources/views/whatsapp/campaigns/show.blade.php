@extends('layouts.app', ['pageTitle' => __('WhatsApp campaign')])

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $campaign->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('Status:') }}<strong>{{ $campaign->status }}</strong>
                · {{ __('Created :datetime', ['datetime' => optional($campaign->created_at)->format('Y-m-d H:i')]) }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('whatsapp.campaigns.index') }}" class="px-3 py-2 border rounded-lg text-sm">{{ __('Back') }}</a>
            @if ($campaign->isEditable() && !str_starts_with((string) $campaign->message_template, '[CSV]'))
                <a href="{{ route('whatsapp.campaigns.edit', $campaign) }}" class="px-3 py-2 border rounded-lg text-sm">{{ __('Edit') }}</a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="grid md:grid-cols-4 gap-4 mb-6">
        @foreach ([
            'total' => __('Total'),
            'pending' => __('Pending'),
            'sent' => __('Sent'),
            'failed' => __('Failed'),
            'skipped' => __('Skipped'),
            'cancelled' => __('Cancelled'),
        ] as $key => $label)
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-xs text-gray-500">{{ $label }}</div>
                <div class="text-2xl font-bold">{{ $counts[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white border rounded-lg p-4 mb-6 space-y-3">
        <h2 class="font-bold">{{ __('Template') }}</h2>
        <pre class="whitespace-pre-wrap text-sm bg-gray-50 p-3 rounded">{{ $campaign->message_template }}</pre>
        <p class="text-sm text-gray-600">{{ __('Delay :min–:max sec · limit :limit/hour', ['min' => $campaign->min_delay_seconds, 'max' => $campaign->max_delay_seconds, 'limit' => $campaign->max_messages_per_hour]) }}</p>

        <div class="flex flex-wrap gap-2 pt-2">
            @if ($campaign->canStart())
                <form method="POST" action="{{ route('whatsapp.campaigns.confirm', $campaign) }}" class="flex flex-wrap items-center gap-3"
                    onsubmit="return confirm(__('Confirm starting campaign send?'));">
                    @csrf
                    @if (($counts['pending'] ?? 0) > $highCountThreshold)
                        <label class="text-sm text-amber-800 flex items-center gap-2">
                            <input type="checkbox" name="acknowledge_high_count" value="1" required>
                            {{ __('I confirm the recipient count is large (:count)', ['count' => $counts['pending']]) }}
                        </label>
                    @endif
                    <button class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">{{ __('Confirm and send') }}</button>
                </form>
            @endif
            @if ($campaign->canPause())
                <form method="POST" action="{{ route('whatsapp.campaigns.pause', $campaign) }}">@csrf
                    <button class="bg-amber-500 text-white px-4 py-2 rounded-lg text-sm">{{ __('Pause') }}</button>
                </form>
            @endif
            @if ($campaign->canResume())
                <form method="POST" action="{{ route('whatsapp.campaigns.resume', $campaign) }}">@csrf
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">{{ __('Resume') }}</button>
                </form>
            @endif
            @if ($campaign->canCancel())
                <form method="POST" action="{{ route('whatsapp.campaigns.cancel', $campaign) }}"
                    onsubmit="return confirm(__('Cancel campaign?'));">@csrf
                    <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">{{ __('Cancel') }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-right">PersonID</th>
                    <th class="px-3 py-2 text-right">{{ __('Phone') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Message') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Status') }}</th>
                    <th class="px-3 py-2 text-right">Message ID</th>
                    <th class="px-3 py-2 text-right">{{ __('Error') }}</th>
                    <th class="px-3 py-2 text-right">{{ __('Sent') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($campaign->recipients as $r)
                    <tr class="border-t align-top">
                        <td class="px-3 py-2">{{ $r->person_id ?: '—' }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $r->phone }}</td>
                        <td class="px-3 py-2 max-w-xs"><div class="line-clamp-3 whitespace-pre-wrap">{{ $r->personalized_message }}</div></td>
                        <td class="px-3 py-2">{{ $r->status }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $r->whatsapp_message_id ?: '—' }}</td>
                        <td class="px-3 py-2 text-red-700 text-xs">{{ $r->error_message ?: '—' }}</td>
                        <td class="px-3 py-2 text-xs">{{ optional($r->sent_at)->format('Y-m-d H:i:s') ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
