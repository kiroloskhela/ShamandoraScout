@extends('layouts.app', ['pageTitle' => __('Record attendance')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">{{ __('Record attendance') }}</h1>
            <p class="text-gray-600 dark:text-slate-300">{{ __('Choose the season and authorized event, then record individual attendance') }}</p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('attendance.scan', ['season_id' => $seasonId, 'season_event_id' => $seasonEventId]) }}"
                    class="inline-flex items-center h-10 px-4 text-sm font-medium rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                    {{ __('Scan attendance') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-center font-semibold shadow dark:border dark:border-slate-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-center font-semibold shadow dark:border dark:border-slate-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Season / Event Selection --}}
        <form method="GET" action="{{ route('attendance.manage') }}"
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
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 dark:border-slate-700 dark:bg-slate-900 {{ !empty($seasonId) ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }} focus:border-blue-500 focus:outline-none"
                        {{ !empty($seasonId) ? '' : 'disabled' }} onchange="this.form.submit()">
                        <option value="">{{ __('-- Choose event --') }}</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->SeasonEventID }}"
                                {{ ($seasonEventId ?? null) == $e->SeasonEventID ? 'selected' : '' }}>
                                {{ $e->EventName }} - {{ $e->EventStartDate }}
                            </option>
                        @endforeach
                    </select>
                    @if (($seasonId ?? null) && $events->isEmpty())
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">{{ __('No events for your groups in this season.') }}</p>
                    @endif
                </div>
            </div>
        </form>

        @if (!empty($seasonEventId))
            <div class="mb-6 flex flex-wrap items-center justify-center gap-3">
                <form method="POST" action="{{ route('attendance.send-qr-bulk') }}"
                    onsubmit="return confirm(@json(__('Confirm send QR codes to all people on this event roster via WhatsApp?')))">
                    @csrf
                    <input type="hidden" name="season_id" value="{{ $seasonId }}">
                    <input type="hidden" name="season_event_id" value="{{ $seasonEventId }}">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-11 px-5 text-sm font-medium rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">
                        {{ __('Send QR codes via WhatsApp') }}
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 border-2 border-blue-300 dark:border-slate-700">
                <form method="POST" action="{{ route('attendance.save', $seasonEventId) }}" id="attendanceForm">
                    @csrf
                    <input type="hidden" name="season_id" value="{{ $seasonId }}">

                    {{-- Toolbar --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">

                        {{-- Current user --}}
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-700 dark:text-slate-200 font-semibold">{{ __('Attendance taken by') }}</span>
                            @php
                                $fullName = trim(
                                    (optional($me)->FirstName ?? '') .
                                        ' ' .
                                        (optional($me)->SecondName ?? '') .
                                        ' ' .
                                        (optional($me)->ThirdName ?? '') .
                                        ' ' .
                                        (optional($me)->FourthName ?? ''),
                                );
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-200 text-sm">
                                {{ $fullName ?: __('Me') }}
                            </span>
                        </div>

                        {{-- Search --}}
                        <div class="relative w-full md:w-80">
                            <input id="tableSearch" type="text" placeholder="{{ __('Search: name / phone / sector / stage') }}"
                                class="w-full h-11 pr-4 pl-10 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:outline-none text-sm">
                            <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24"
                                fill="none">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    d="m21 21-4.3-4.3m0-6.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </div>

                        {{-- Mark all buttons --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Set all:') }}</span>
                            <button type="button" id="markAllPresent"
                                class="px-3 py-1.5 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-xs font-semibold hover:bg-green-200 dark:hover:bg-green-900/60 transition">{{ __('✓ Present') }}</button>
                            <button type="button" id="markAllAbsent"
                                class="px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-xs font-semibold hover:bg-red-200 dark:hover:bg-red-900/60 transition">{{ __('✗ Absent') }}</button>
                            <button type="button" id="markAllExcused"
                                class="px-3 py-1.5 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 text-xs font-semibold hover:bg-amber-200 dark:hover:bg-amber-900/60 transition">{{ __('~ Absent with excuse') }}</button>
                        </div>

                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table id="attendanceTable" class="min-w-full border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                            <thead class="bg-slate-100 dark:bg-slate-800">
                                <tr>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-right">{{ __('Name') }}</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-right">{{ __('Phone') }}</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-right">{{ __('Sector') }}</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-right">{{ __('Stage') }}</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-center w-56">{{ __('Attendance section') }}</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-right" id="excuseHeader">{{ __('Excuse') }}</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-slate-200 text-center">{{ __('QR') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tableRows as $r)
                                    @php
                                        $pid = $r['PersonID'];
                                        $status = $r['Status']; // 'present' | 'absent' | 'excused'
                                        $excuse = $r['Excuse'];
                                        $searchHaystack = mb_strtolower(
                                            trim(
                                                ($r['PersonName'] ?? '') .
                                                    ' ' .
                                                    ($r['PhoneNumber'] ?? '') .
                                                    ' ' .
                                                    ($r['QetaaName'] ?? '') .
                                                    ' ' .
                                                    ($r['SanaMarhalaName'] ?? ''),
                                            ),
                                        );
                                    @endphp
                                    <tr class="border-t border-slate-200 dark:border-slate-700 attendance-row" data-search="{{ e($searchHaystack) }}">
                                        <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-100">{{ $r['PersonName'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $r['PhoneNumber'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $r['QetaaName'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $r['SanaMarhalaName'] }}</td>

                                        {{-- 3-way status selector --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">

                                                {{-- Present --}}
                                                <label class="status-label cursor-pointer">
                                                    <input type="radio" name="attendance[{{ $pid }}][status]"
                                                        value="present" class="sr-only status-radio"
                                                        data-person="{{ $pid }}"
                                                        {{ $status === 'present' ? 'checked' : '' }}>
                                                    <span
                                                        class="status-btn present-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all
                                                    {{ $status === 'present'
                                                        ? 'bg-green-600 text-white border-green-600'
                                                        : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-green-400' }}">{{ __('✓ Present') }}</span>
                                                </label>

                                                {{-- Absent --}}
                                                <label class="status-label cursor-pointer">
                                                    <input type="radio" name="attendance[{{ $pid }}][status]"
                                                        value="absent" class="sr-only status-radio"
                                                        data-person="{{ $pid }}"
                                                        {{ $status === 'absent' ? 'checked' : '' }}>
                                                    <span
                                                        class="status-btn absent-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all
                                                    {{ $status === 'absent'
                                                        ? 'bg-red-600 text-white border-red-600'
                                                        : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-red-400' }}">{{ __('✗ Absent') }}</span>
                                                </label>

                                                {{-- Excused --}}
                                                <label class="status-label cursor-pointer">
                                                    <input type="radio" name="attendance[{{ $pid }}][status]"
                                                        value="excused" class="sr-only status-radio"
                                                        data-person="{{ $pid }}"
                                                        {{ $status === 'excused' ? 'checked' : '' }}>
                                                    <span
                                                        class="status-btn excused-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all
                                                    {{ $status === 'excused'
                                                        ? 'bg-amber-500 text-white border-amber-500'
                                                        : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-amber-400' }}">{{ __('~ Excuse') }}</span>
                                                </label>

                                            </div>
                                        </td>

                                        {{-- Excuse text — visible only when status = excused --}}
                                        <td class="px-4 py-3 excuse-cell"
                                            style="{{ $status !== 'excused' ? 'display:none' : '' }}">
                                            <input type="text" name="attendance[{{ $pid }}][excuse]"
                                                value="{{ e($excuse) }}" placeholder="{{ __('Write the excuse...') }}" maxlength="1000"
                                                class="w-full h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-amber-400 focus:outline-none text-right">
                                        </td>
                                        {{-- Placeholder cell when not excused --}}
                                        <td class="px-4 py-3 no-excuse-cell"
                                            style="{{ $status === 'excused' ? 'display:none' : '' }}">
                                            <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <button type="submit" form="sendQrForm{{ $pid }}"
                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 text-xs font-semibold hover:bg-emerald-200 dark:hover:bg-emerald-900/60 transition"
                                                title="{{ __('Send QR code via WhatsApp') }}">
                                                {{ __('Send QR') }}
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-slate-600 dark:text-slate-300">{{ __('No people to display.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div id="pager" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                        <div id="pager-info" class="text-sm text-slate-600 dark:text-slate-300"></div>
                        <div class="flex items-center gap-2" id="pager-controls"></div>
                    </div>

                    {{-- Summary counts --}}
                    <div class="flex items-center gap-6 mt-4 px-2">
                        <span class="text-sm text-green-700 dark:text-green-300 font-semibold">{{ __('Present:') }} <span id="countPresent">0</span></span>
                        <span class="text-sm text-red-700 dark:text-red-300 font-semibold">{{ __('Absent:') }} <span id="countAbsent">0</span></span>
                        <span class="text-sm text-amber-700 dark:text-amber-300 font-semibold">{{ __('Absent with excuse:') }} <span
                                id="countExcused">0</span></span>
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('Total:') }} <span id="countTotal">0</span></span>
                    </div>

                    {{-- Save --}}
                    <div class="flex items-center justify-center gap-3 mt-6">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-600 text-white hover:bg-green-700 transition">{{ __('💾 Save attendance') }}</button>
                    </div>
                </form>

                @foreach ($tableRows as $r)
                    <form id="sendQrForm{{ $r['PersonID'] }}" method="POST" action="{{ route('attendance.send-qr', $r['PersonID']) }}" class="hidden">
                        @csrf
                        <input type="hidden" name="season_event_id" value="{{ $seasonEventId }}">
                    </form>
                @endforeach
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const pageSize = 10;
                    let currentPage = 1;

                    const searchInput = document.getElementById('tableSearch');
                    const table = document.getElementById('attendanceTable');
                    const allRows = Array.from(table.querySelectorAll('tbody tr.attendance-row'));
                    const pagerInfo = document.getElementById('pager-info');
                    const pagerControls = document.getElementById('pager-controls');

                    const inactiveBtnClasses = [
                        'bg-white', 'dark:bg-slate-900',
                        'text-slate-500', 'dark:text-slate-400',
                        'border-slate-200', 'dark:border-slate-700',
                    ];

                    // ── Status button visual update ──────────────────────────────
                    function updateStatusButtons(row, value) {
                        const btns = {
                            present: row.querySelector('.present-btn'),
                            absent: row.querySelector('.absent-btn'),
                            excused: row.querySelector('.excused-btn'),
                        };
                        const styles = {
                            present: ['bg-green-600', 'text-white', 'border-green-600'],
                            absent: ['bg-red-600', 'text-white', 'border-red-600'],
                            excused: ['bg-amber-500', 'text-white', 'border-amber-500'],
                        };
                        const hoverBorders = {
                            present: 'hover:border-green-400',
                            absent: 'hover:border-red-400',
                            excused: 'hover:border-amber-400',
                        };

                        Object.keys(btns).forEach(key => {
                            const btn = btns[key];
                            if (!btn) return;
                            // reset
                            btn.classList.remove(
                                'bg-green-600', 'bg-red-600', 'bg-amber-500',
                                'text-white', 'border-green-600', 'border-red-600', 'border-amber-500',
                                'hover:border-green-400', 'hover:border-red-400', 'hover:border-amber-400',
                                ...inactiveBtnClasses
                            );
                            btn.classList.add(...inactiveBtnClasses);

                            if (key === value) {
                                btn.classList.remove(...inactiveBtnClasses);
                                btn.classList.add(...styles[key]);
                            } else {
                                btn.classList.add(hoverBorders[key]);
                            }
                        });

                        // show/hide excuse column cells
                        const excuseCell = row.querySelector('.excuse-cell');
                        const noExcuseCell = row.querySelector('.no-excuse-cell');
                        if (excuseCell && noExcuseCell) {
                            if (value === 'excused') {
                                excuseCell.style.display = '';
                                noExcuseCell.style.display = 'none';
                            } else {
                                excuseCell.style.display = 'none';
                                noExcuseCell.style.display = '';
                                // clear excuse value when switching away
                                const input = excuseCell.querySelector('input');
                                if (input) input.value = '';
                            }
                        }

                        updateCounts();
                    }

                    // ── Listen for radio changes ─────────────────────────────────
                    table.addEventListener('change', function(e) {
                        if (!e.target.classList.contains('status-radio')) return;
                        const row = e.target.closest('tr');
                        updateStatusButtons(row, e.target.value);
                    });

                    // ── Mark all (visible filtered rows) ─────────────────────────
                    function markAll(status) {
                        getFilteredRows().forEach(row => {
                            const radio = row.querySelector(`input[type="radio"][value="${status}"]`);
                            if (radio) {
                                radio.checked = true;
                                updateStatusButtons(row, status);
                            }
                        });
                    }
                    document.getElementById('markAllPresent')?.addEventListener('click', () => markAll('present'));
                    document.getElementById('markAllAbsent')?.addEventListener('click', () => markAll('absent'));
                    document.getElementById('markAllExcused')?.addEventListener('click', () => markAll('excused'));

                    // ── Counts ───────────────────────────────────────────────────
                    function updateCounts() {
                        let p = 0,
                            a = 0,
                            ex = 0;
                        allRows.forEach(row => {
                            const checked = row.querySelector('input[type="radio"]:checked');
                            if (!checked) return;
                            if (checked.value === 'present') p++;
                            else if (checked.value === 'absent') a++;
                            else if (checked.value === 'excused') ex++;
                        });
                        document.getElementById('countPresent').textContent = p;
                        document.getElementById('countAbsent').textContent = a;
                        document.getElementById('countExcused').textContent = ex;
                        document.getElementById('countTotal').textContent = allRows.length;
                    }

                    // ── Search + Pagination ───────────────────────────────────────
                    function normalize(s) {
                        return (s || '').toString().toLowerCase();
                    }

                    function getFilteredRows() {
                        const q = normalize(searchInput?.value).trim();
                        if (!q) return allRows;
                        return allRows.filter(tr => (tr.getAttribute('data-search') || '').includes(q));
                    }

                    function renderPager(totalPages) {
                        pagerControls.innerHTML = '';
                        const btn = (label, disabled, onClick, extra = '') => {
                            const a = document.createElement('button');
                            a.type = 'button';
                            a.className = `px-3 py-1 rounded border border-slate-200 dark:border-slate-700 text-sm ${disabled
                        ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed'
                        : 'bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200'} ${extra}`;
                            a.textContent = label;
                            if (!disabled) a.addEventListener('click', onClick);
                            return a;
                        };

                        pagerControls.appendChild(btn(@json(__('Previous')), currentPage === 1, () => {
                            currentPage--;
                            renderPage();
                        }));

                        const windowSize = 5;
                        let start = Math.max(1, currentPage - Math.floor(windowSize / 2));
                        let end = Math.min(totalPages, start + windowSize - 1);
                        start = Math.max(1, end - windowSize + 1);

                        if (start > 1) pagerControls.appendChild(btn('1', false, () => {
                            currentPage = 1;
                            renderPage();
                        }));
                        if (start > 2) {
                            const d = document.createElement('span');
                            d.className = 'px-2 text-slate-500 dark:text-slate-400';
                            d.textContent = '…';
                            pagerControls.appendChild(d);
                        }

                        for (let p = start; p <= end; p++) {
                            pagerControls.appendChild(btn(String(p), false, () => {
                                    currentPage = p;
                                    renderPage();
                                },
                                p === currentPage ? 'bg-blue-600 text-white border-blue-600' : ''));
                        }

                        if (end < totalPages - 1) {
                            const d = document.createElement('span');
                            d.className = 'px-2 text-slate-500 dark:text-slate-400';
                            d.textContent = '…';
                            pagerControls.appendChild(d);
                        }
                        if (end < totalPages) pagerControls.appendChild(btn(String(totalPages), false, () => {
                            currentPage = totalPages;
                            renderPage();
                        }));

                        pagerControls.appendChild(btn(@json(__('Next')), currentPage === totalPages || totalPages === 0, () => {
                            currentPage++;
                            renderPage();
                        }));
                    }

                    function renderPage() {
                        const filtered = getFilteredRows();
                        const total = filtered.length;
                        const totalPages = Math.max(1, Math.ceil(total / pageSize));
                        if (currentPage > totalPages) currentPage = totalPages;
                        if (currentPage < 1) currentPage = 1;

                        allRows.forEach(tr => tr.style.display = 'none');

                        const start = (currentPage - 1) * pageSize;
                        filtered.slice(start, start + pageSize).forEach(tr => tr.style.display = '');

                        const from = total ? start + 1 : 0;
                        const to = Math.min(start + pageSize, total);
                        pagerInfo.textContent = @json(__('Showing :from–:to of :total')).replace(':from', from).replace(':to', to).replace(':total', total);
                        renderPager(totalPages);
                    }

                    searchInput?.addEventListener('input', () => {
                        currentPage = 1;
                        renderPage();
                    });

                    updateCounts();
                    renderPage();
                });
            </script>
        @endif
    </div>
@endsection
