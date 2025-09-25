@extends('layouts.app', ['pageTitle' => 'تسجيل الحضور'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">تسجيل الحضور</h1>
            <p class="text-gray-600">اختر الموسم والفعالية المصرح لك بها ثم فعّل الحضور للأفراد</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 text-center font-semibold shadow">
                {{ session('success') }}
            </div>
        @endif

        <!-- Selection Card -->
        <form method="GET" action="{{ route('attendance.manage') }}"
            class="bg-white rounded-lg shadow-lg p-6 mb-8 border-2 border-blue-300">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Season -->
                <div class="relative">
                    <label for="season_id" class="block mb-2 text-sm font-semibold text-gray-700">اختر الموسم</label>
                    <select id="season_id" name="season_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                        onchange="this.form.submit()">
                        <option value="">-- اختر الموسم --</option>
                        @foreach ($seasons as $s)
                            <option value="{{ $s->SeasonID }}" {{ ($seasonId ?? null) == $s->SeasonID ? 'selected' : '' }}>
                                {{ $s->SeasonName }} ({{ $s->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Event (only overlapping with my groups) -->
                <div class="relative">
                    <label for="season_event_id" class="block mb-2 text-sm font-semibold text-gray-700">اختر
                        الفعالية</label>
                    <select id="season_event_id" name="season_event_id"
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 {{ !empty($seasonId) ? 'text-slate-600' : 'text-slate-400' }} focus:border-blue-500 focus:outline-none"
                        {{ !empty($seasonId) ? '' : 'disabled' }} onchange="this.form.submit()">
                        <option value="">-- اختر الفعالية --</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->SeasonEventID }}"
                                {{ ($seasonEventId ?? null) == $e->SeasonEventID ? 'selected' : '' }}>
                                {{ $e->EventName }} - {{ $e->EventStartDate }}
                            </option>
                        @endforeach
                    </select>
                    @if (($seasonId ?? null) && $events->isEmpty())
                        <p class="mt-2 text-xs text-amber-600">لا توجد فعاليات تخص مجموعاتك في هذا الموسم.</p>
                    @endif
                </div>
            </div>
        </form>


        @if (!empty($seasonEventId))
            @php
                $rows = $tableRows;
                $presentSet = array_flip($attendanceIds ?? []);
            @endphp

            <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-blue-300">
                <form method="POST" action="{{ route('attendance.save', $seasonEventId) }}" id="attendanceForm">
                    @csrf
                    <input type="hidden" name="season_id" value="{{ $seasonId }}">

                    <!-- Header: current user + search + toggle all -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                        <!-- Servent (auto) -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-700 font-semibold">أخذ الحضور بواسطة</span>
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
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700">
                                {{ $fullName ?: 'أنا' }}
                            </span>
                        </div>

                        <!-- Search -->
                        <div class="relative w-full md:w-80">
                            <input id="tableSearch" type="text" placeholder="بحث: الاسم / الهاتف / القطاع / المرحلة"
                                class="w-full h-11 pr-4 pl-10 rounded-lg border border-slate-200 focus:border-blue-500 focus:outline-none text-sm">
                            <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24"
                                fill="none">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    d="m21 21-4.3-4.3m0-6.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </div>

                        <!-- Toggle All -->
                        <div class="flex items-center gap-3">
                            <label for="toggleAll" class="text-sm font-semibold text-slate-700">تبديل الكل (كل
                                الجدول)</label>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input id="toggleAll" type="checkbox" class="sr-only peer">
                                <!-- OFF = red, ON = green -->
                                <span class="w-14 h-8 bg-red-500 rounded-full transition peer-checked:bg-green-600"></span>
                                <span
                                    class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full transition peer-checked:translate-x-6"></span>
                            </label>
                        </div>

                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table id="attendanceTable" class="min-w-full border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">الاسم</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">الهاتف</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">القطاع</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">المرحلة</th>
                                    <th class="px-4 py-2 text-sm font-semibold text-gray-700 text-center">الحضور</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $r)
                                    @php
                                        $checked = isset($presentSet[$r['PersonID']]);
                                        $searchHaystack = trim(
                                            ($r['PersonName'] ?? '') .
                                                ' ' .
                                                ($r['PhoneNumber'] ?? '') .
                                                ' ' .
                                                ($r['QetaaName'] ?? '') .
                                                ' ' .
                                                ($r['SanaMarhalaName'] ?? ''),
                                        );
                                    @endphp
                                    <tr class="border-t" data-search="{{ e(mb_strtolower($searchHaystack)) }}">
                                        <td class="px-4 py-2 text-right">{{ $r['PersonName'] }}</td>
                                        <td class="px-4 py-2 text-right">{{ $r['PhoneNumber'] }}</td>
                                        <td class="px-4 py-2 text-right">{{ $r['QetaaName'] }}</td>
                                        <td class="px-4 py-2 text-right">{{ $r['SanaMarhalaName'] }}</td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center justify-center">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox" name="ServedIDs[]" value="{{ $r['PersonID'] }}"
                                                        class="sr-only peer row-toggle" {{ $checked ? 'checked' : '' }}>
                                                    <!-- OFF = red-500, ON = green-700 (darker) -->
                                                    <span
                                                        class="w-14 h-8 bg-red-500 rounded-full transition peer-checked:bg-green-700"></span>
                                                    <span
                                                        class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full transition peer-checked:translate-x-6"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-600">لا يوجد أفراد
                                            لعرضهم.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (10 per page) -->
                    <div id="pager" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                        <div id="pager-info" class="text-sm text-slate-600"></div>
                        <div class="flex items-center gap-2" id="pager-controls">
                            <!-- Controls will be injected by JS -->
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="flex items-center justify-center gap-3 mt-6">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-600 text-white hover:bg-green-700 transition">
                            💾 حفظ الحضور
                        </button>
                    </div>
                </form>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const pageSize = 10;
                    let currentPage = 1;

                    const toggleAll = document.getElementById('toggleAll');
                    const searchInput = document.getElementById('tableSearch');
                    const table = document.getElementById('attendanceTable');
                    const allRows = Array.from(table.querySelectorAll('tbody tr'));
                    const pagerInfo = document.getElementById('pager-info');
                    const pagerControls = document.getElementById('pager-controls');

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
                            a.className =
                                `px-3 py-1 rounded border text-sm ${disabled ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-white hover:bg-slate-50 text-slate-700'} ${extra}`;
                            a.textContent = label;
                            if (!disabled) a.addEventListener('click', onClick);
                            return a;
                        };

                        // Prev
                        pagerControls.appendChild(btn('السابق', currentPage === 1, () => {
                            currentPage--;
                            renderPage();
                        }));

                        // Page numbers
                        const total = totalPages;
                        const windowSize = 5;
                        let start = Math.max(1, currentPage - Math.floor(windowSize / 2));
                        let end = Math.min(total, start + windowSize - 1);
                        start = Math.max(1, end - windowSize + 1);

                        if (start > 1) pagerControls.appendChild(btn('1', false, () => {
                            currentPage = 1;
                            renderPage();
                        }));
                        if (start > 2) {
                            const dots = document.createElement('span');
                            dots.className = 'px-2 text-slate-500';
                            dots.textContent = '…';
                            pagerControls.appendChild(dots);
                        }

                        for (let p = start; p <= end; p++) {
                            pagerControls.appendChild(btn(String(p), false, () => {
                                    currentPage = p;
                                    renderPage();
                                },
                                p === currentPage ? 'bg-blue-600 text-white border-blue-600' : ''));
                        }

                        if (end < total - 1) {
                            const dots2 = document.createElement('span');
                            dots2.className = 'px-2 text-slate-500';
                            dots2.textContent = '…';
                            pagerControls.appendChild(dots2);
                        }
                        if (end < total) pagerControls.appendChild(btn(String(total), false, () => {
                            currentPage = total;
                            renderPage();
                        }));

                        // Next
                        pagerControls.appendChild(btn('التالي', currentPage === totalPages || totalPages === 0, () => {
                            currentPage++;
                            renderPage();
                        }));
                    }

                    let currentSlice = [];

                    function renderPage() {
                        const filtered = getFilteredRows();
                        const total = filtered.length;
                        const totalPages = Math.max(1, Math.ceil(total / pageSize));
                        if (currentPage > totalPages) currentPage = totalPages;
                        if (currentPage < 1) currentPage = 1;

                        // hide all
                        allRows.forEach(tr => tr.style.display = 'none');

                        // show current page slice
                        const start = (currentPage - 1) * pageSize;
                        const end = start + pageSize;
                        currentSlice = filtered.slice(start, end);
                        currentSlice.forEach(tr => tr.style.display = '');

                        // info
                        const from = total ? start + 1 : 0;
                        const to = Math.min(end, total);
                        pagerInfo.textContent = `عرض ${from}–${to} من ${total}`;

                        renderPager(totalPages);
                    }

                    // ✅ Toggle ALL rows in the table (not just current page, not just filtered)
                    function setAllTable(checked) {
                        allRows.forEach(r => {
                            const cb = r.querySelector('.row-toggle');
                            if (cb) cb.checked = checked;
                        });
                    }

                    // Events
                    toggleAll?.addEventListener('change', (e) => setAllTable(e.target.checked));
                    searchInput?.addEventListener('input', function() {
                        currentPage = 1;
                        renderPage();
                    });

                    // Initial
                    renderPage();
                });
            </script>
        @endif
    @endsection
