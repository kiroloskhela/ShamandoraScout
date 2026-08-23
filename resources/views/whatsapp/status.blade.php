@extends('layouts.app', ['pageTitle' => __('WhatsApp status')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-slate-100">{{ __('WhatsApp bridge status') }}</h1>
            <a href="{{ route('whatsapp.status') }}"
                class="bg-gray-800 dark:bg-slate-700 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-gray-700 dark:hover:bg-slate-600 transition">{{ __('Refresh') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 dark:border-slate-700 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-900 dark:text-emerald-200 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 dark:border-slate-700 bg-red-50 dark:bg-red-900/40 text-red-900 dark:text-red-200 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if ($error)
            <div class="mb-4 rounded-lg border border-amber-200 dark:border-slate-700 bg-amber-50 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200 px-4 py-3">
                {{ $error }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow dark:border dark:border-slate-700 border border-gray-100 dark:border-slate-700 p-6 space-y-4 max-w-xl">
            <div>
                <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">{{ __('Bridge URL') }}</div>
                <div class="font-mono text-sm break-all text-slate-800 dark:text-slate-100">{{ $base }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">{{ __('Connection') }}</div>
                @if (!$reachable)
                    <span class="inline-block px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm font-semibold">{{ __('Unavailable') }}</span>
                @elseif ($connected)
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 text-sm font-semibold">{{ __('Connected') }}</span>
                @elseif ($reconnecting)
                    <span class="inline-block px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-800 dark:text-sky-200 text-sm font-semibold">{{ __('Reconnecting') }}</span>
                @elseif ($pairingRequired)
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200 text-sm font-semibold">{{ __('Waiting for QR scan') }}</span>
                @elseif ($hasReusableSession)
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200 text-sm font-semibold">{{ __('Disconnected — saved session') }}</span>
                @else
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200 text-sm font-semibold">{{ __('Waiting for QR scan') }}</span>
                @endif
            </div>

            @if ($reachable && !$connected && $pairingRequired)
                <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('WhatsApp unlinked this device. Scan the new QR. The old session was cleared after two rejected reconnects.') }}</p>
            @endif

            @if ($reachable && !$connected)
                <form method="POST" action="{{ route('whatsapp.reconnect') }}" class="pt-2">
                    @csrf
                    <button type="submit"
                        @if ($reconnecting) disabled @endif
                        class="bg-emerald-700 hover:bg-emerald-800 disabled:opacity-60 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                        {{ __('Reconnect saved session') }}
                    </button>
                </form>
            @endif

            @if ($reachable && !$connected && $qr)
                <div>
                    <div class="text-sm text-gray-600 dark:text-slate-300 mb-2">{{ __('Scan the code from WhatsApp → Linked Devices') }}</div>
                    <img src="{{ $qr }}" alt="WhatsApp QR" class="w-64 h-64 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
                </div>
            @elseif ($reachable && !$connected && !$qr)
                <p class="text-sm text-gray-600 dark:text-slate-300">{{ __('No QR code available right now. Restart the bridge or wait a few seconds then refresh.') }}</p>
            @elseif ($connected)
                <p class="text-sm text-gray-600 dark:text-slate-300">{{ __('Session is saved on disk. Do not delete the folder :folder.', ['folder' => 'whatsapp-bridge/auth_session']) }}</p>
            @endif
        </div>
    </div>
@endsection
