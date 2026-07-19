@extends('layouts.app', ['pageTitle' => __('Live attendance')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">{{ __('Live attendance') }}</h1>
            <p class="text-gray-600 dark:text-slate-300">{{ __('Watch reservation event attendance update in real time') }}</p>
        </div>

        <form method="GET" action="{{ route('attendance.live') }}"
            class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 mb-8 border-2 border-blue-300 dark:border-slate-700">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="season_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose season') }}</label>
                    <select id="season_id" name="season_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:border-blue-500 focus:outline-none"
                        onchange="this.form.submit()">
                        <option value="">{{ __('-- Choose season --') }}</option>
                        @foreach ($seasons as $s)
                            <option value="{{ $s->SeasonID }}" {{ ($seasonId ?? null) == $s->SeasonID ? 'selected' : '' }}>
                                {{ $s->SeasonName }} ({{ $s->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="season_event_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose event') }}</label>
                    <select id="season_event_id" name="season_event_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 dark:border-slate-700 dark:bg-slate-900 focus:border-blue-500 focus:outline-none"
                        {{ !empty($seasonId) ? '' : 'disabled' }} onchange="this.form.submit()">
                        <option value="">{{ __('-- Choose event --') }}</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->SeasonEventID }}" {{ ($seasonEventId ?? null) == $e->SeasonEventID ? 'selected' : '' }}>
                                {{ $e->EventName }} - {{ $e->EventStartDate }}
                            </option>
                        @endforeach
                    </select>
                    @if (($seasonId ?? null) && $events->isEmpty())
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">{{ __('No reservation events in this season.') }}</p>
                    @endif
                </div>
            </div>
        </form>

        @if (!empty($seasonEventId) && $snapshot)
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ $snapshot['event_name'] }}</h2>
                <span id="liveConnection" class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ __('Polling') }}
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
                @foreach ([
                    ['key' => 'present', 'label' => __('Present'), 'class' => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200'],
                    ['key' => 'absent', 'label' => __('Absent'), 'class' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200'],
                    ['key' => 'outside', 'label' => __('Outside'), 'class' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200'],
                    ['key' => 'unmarked', 'label' => __('Not scanned'), 'class' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200'],
                    ['key' => 'total', 'label' => __('Total booked'), 'class' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200'],
                ] as $card)
                    <div class="rounded-xl p-4 {{ $card['class'] }}">
                        <div class="text-xs font-semibold mb-1">{{ $card['label'] }}</div>
                        <div class="text-2xl font-bold" id="count-{{ $card['key'] }}">{{ $snapshot['counts'][$card['key']] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 me-2">{{ __('Filter by status') }}:</span>
                <button type="button" data-filter="all" class="live-filter h-9 px-3 rounded-full text-xs font-bold border border-slate-300 dark:border-slate-600 bg-slate-800 text-white">{{ __('All') }}</button>
                <button type="button" data-filter="present" class="live-filter h-9 px-3 rounded-full text-xs font-bold border border-green-600 text-green-700 dark:text-green-300 bg-white dark:bg-slate-900">{{ __('Present') }}</button>
                <button type="button" data-filter="absent" class="live-filter h-9 px-3 rounded-full text-xs font-bold border border-red-600 text-red-700 dark:text-red-300 bg-white dark:bg-slate-900">{{ __('Absent') }}</button>
                <button type="button" data-filter="outside" class="live-filter h-9 px-3 rounded-full text-xs font-bold border border-amber-500 text-amber-700 dark:text-amber-300 bg-white dark:bg-slate-900">{{ __('Outside') }}</button>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-100">
                    {{ __('Recent activity') }}
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-2 text-right text-sm">{{ __('Name') }}</th>
                                <th class="px-4 py-2 text-right text-sm">{{ __('Type') }}</th>
                                <th class="px-4 py-2 text-right text-sm">{{ __('Status') }}</th>
                                <th class="px-4 py-2 text-right text-sm">{{ __('Updated') }}</th>
                            </tr>
                        </thead>
                        <tbody id="liveFeed"></tbody>
                    </table>
                </div>
            </div>

            <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const seasonEventId = @json((int) $seasonEventId);
                    const snapshotUrl = @json(route('attendance.live.snapshot'));
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const statusLabels = {
                        present: @json(__('Present')),
                        absent: @json(__('Absent')),
                        outside: @json(__('Outside')),
                    };
                    const rowClasses = {
                        present: 'bg-green-100 dark:bg-green-900/40 border-t border-green-200 dark:border-green-800',
                        absent: 'bg-red-100 dark:bg-red-900/40 border-t border-red-200 dark:border-red-800',
                        outside: 'bg-amber-100 dark:bg-amber-900/40 border-t border-amber-200 dark:border-amber-800',
                    };
                    const connectionEl = document.getElementById('liveConnection');
                    let pollTimer = null;
                    let activeFilter = 'all';
                    let latestFeed = @json($snapshot['feed'] ?? []);

                    function applyCounts(counts) {
                        ['present', 'absent', 'outside', 'unmarked', 'total'].forEach((key) => {
                            const el = document.getElementById('count-' + key);
                            if (el) el.textContent = counts[key] ?? 0;
                        });
                    }

                    function escapeHtml(str) {
                        return String(str)
                            .replaceAll('&', '&amp;')
                            .replaceAll('<', '&lt;')
                            .replaceAll('>', '&gt;')
                            .replaceAll('"', '&quot;');
                    }

                    function renderFeed(feed) {
                        latestFeed = feed || [];
                        const tbody = document.getElementById('liveFeed');
                        if (!tbody) return;

                        const filtered = activeFilter === 'all'
                            ? latestFeed
                            : latestFeed.filter((row) => row.status === activeFilter);

                        if (!filtered.length) {
                            tbody.innerHTML = `<tr id="emptyFeedRow"><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No attendance marks yet.') }}</td></tr>`;
                            return;
                        }

                        tbody.innerHTML = filtered.map((row) => `
                            <tr class="${rowClasses[row.status] || 'border-t border-slate-200 dark:border-slate-700'}" data-status="${escapeHtml(row.status || '')}">
                                <td class="px-4 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100">${escapeHtml(row.name || '')}</td>
                                <td class="px-4 py-2 text-sm text-slate-700 dark:text-slate-200">${escapeHtml(row.booking_type_label || '')}</td>
                                <td class="px-4 py-2 text-sm font-bold">${escapeHtml(statusLabels[row.status] || row.status)}</td>
                                <td class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300">${escapeHtml(row.updated_at || '')}</td>
                            </tr>
                        `).join('');
                    }

                    function setFilter(filter) {
                        activeFilter = filter;
                        document.querySelectorAll('.live-filter').forEach((btn) => {
                            const on = btn.getAttribute('data-filter') === filter;
                            btn.classList.toggle('bg-slate-800', on && filter === 'all');
                            btn.classList.toggle('text-white', on && filter === 'all');
                            btn.classList.toggle('bg-green-600', on && filter === 'present');
                            btn.classList.toggle('bg-red-600', on && filter === 'absent');
                            btn.classList.toggle('bg-amber-500', on && filter === 'outside');
                            if (on && filter !== 'all') {
                                btn.classList.add('text-white');
                                btn.classList.remove('text-green-700', 'text-red-700', 'text-amber-700', 'dark:text-green-300', 'dark:text-red-300', 'dark:text-amber-300');
                            } else if (!on) {
                                btn.classList.remove('bg-slate-800', 'bg-green-600', 'bg-red-600', 'bg-amber-500', 'text-white');
                            }
                        });
                        renderFeed(latestFeed);
                    }

                    async function refreshSnapshot() {
                        try {
                            const res = await fetch(`${snapshotUrl}?season_event_id=${seasonEventId}`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                cache: 'no-store',
                            });
                            const data = await res.json();
                            if (data.ok && data.snapshot) {
                                applyCounts(data.snapshot.counts || {});
                                renderFeed(data.snapshot.feed || []);
                            }
                        } catch (e) {}
                    }

                    function startPolling(ms) {
                        if (pollTimer) clearInterval(pollTimer);
                        connectionEl.textContent = @json(__('Polling'));
                        connectionEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300';
                        pollTimer = setInterval(refreshSnapshot, ms);
                    }

                    document.querySelectorAll('.live-filter').forEach((btn) => {
                        btn.addEventListener('click', () => setFilter(btn.getAttribute('data-filter')));
                    });

                    applyCounts(@json($snapshot['counts'] ?? []));
                    setFilter('all');

                    const pusherKey = @json($pusherKey);
                    const broadcastDriver = @json($broadcastDriver);
                    if (pusherKey && window.Echo && (broadcastDriver === 'pusher' || broadcastDriver === 'reverb')) {
                        try {
                            window.Pusher = Pusher;
                            window.Echo = new Echo({
                                broadcaster: 'pusher',
                                key: pusherKey,
                                cluster: @json($pusherCluster ?: 'mt1'),
                                wsHost: @json($pusherHost),
                                wsPort: @json((int) ($pusherPort ?: 80)),
                                wssPort: @json((int) ($pusherPort ?: 443)),
                                forceTLS: @json(($pusherScheme ?? 'https') === 'https'),
                                enabledTransports: ['ws', 'wss'],
                                authEndpoint: '/broadcasting/auth',
                                auth: {
                                    headers: { 'X-CSRF-TOKEN': csrf },
                                },
                            });

                            window.Echo.private(`attendance.live.${seasonEventId}`)
                                .listen('.AttendanceMarked', (payload) => {
                                    if (payload.counts) applyCounts(payload.counts);
                                    refreshSnapshot();
                                });

                            connectionEl.textContent = @json(__('Live'));
                            connectionEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200';
                            startPolling(5000);
                        } catch (e) {
                            startPolling(2000);
                        }
                    } else {
                        // Fast polling (~2s) when websockets are not configured
                        startPolling(2000);
                    }

                    // Immediate first refresh
                    refreshSnapshot();
                });
            </script>
        @endif
    </div>
@endsection
