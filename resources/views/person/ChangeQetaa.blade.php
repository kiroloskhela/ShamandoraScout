{{-- resources/views/person/ChangeQetaa.blade.php --}}
@extends('layouts.app')

@section('title', 'تغيير القطاع')

@section('content')
    <div class="cq-wrapper" dir="rtl">

        {{-- ── Header ── --}}
        <div class="cq-header">
            <div class="cq-header-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <div>
                <h1 class="cq-title">تغيير القطاع</h1>
                <p class="cq-subtitle">ابحث عن الشخص ثم حدد القطاع الجديد</p>
            </div>
        </div>

        {{-- ── Status flash ── --}}
        @if (session('status'))
            <div class="cq-alert cq-alert--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="cq-alert cq-alert--error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="cq-card">

            {{-- ══ STEP 1 — Search ══ --}}
            <div class="cq-step">
                <span class="cq-step-badge">١</span>
                <div class="cq-step-body">
                    <label class="cq-label">البحث عن الشخص</label>
                    <p class="cq-hint">ابحث بالاسم أو رقم الهوية</p>

                    <div class="cq-search-row">
                        <input type="text" id="searchInput" class="cq-input" placeholder="اكتب الاسم أو رقم الهوية…"
                            autocomplete="off" />
                        <button type="button" class="cq-btn cq-btn--search" id="searchBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>{{ __('Search') }}</button>
                    </div>

                    {{-- Search results list --}}
                    <div id="searchResults" class="cq-results" style="display:none"></div>
                </div>
            </div>

            {{-- ══ STEP 2 — Person card + Qetaa change (revealed after selection) ══ --}}
            <div id="changeSection" class="cq-step cq-step--hidden">
                <span class="cq-step-badge">٢</span>
                <div class="cq-step-body">

                    {{-- Person info card --}}
                    <div class="cq-person-card" id="personCard">
                        <div class="cq-person-avatar" id="personInitials">—</div>
                        <div class="cq-person-info">
                            <div class="cq-person-name" id="personName">—</div>
                            <div class="cq-person-meta">
                                <span class="cq-meta-chip">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5">
                                        <rect x="2" y="5" width="20" height="14" rx="2" />
                                        <line x1="2" y1="10" x2="22" y2="10" />
                                    </svg>
                                    رقم الهوية: <strong id="personIdDisplay">—</strong>
                                </span>
                                <span class="cq-meta-chip cq-meta-chip--qetaa">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    </svg>
                                    القطاع الحالي: <strong id="currentQetaaName">—</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Qetaa selector --}}
                    <label class="cq-label" style="margin-top:1.5rem">القطاع الجديد</label>
                    <p class="cq-hint">اختر القطاع الذي تريد نقل الشخص إليه</p>
                    <select id="newQetaaSelect" class="cq-select">
                        <option value="">— اختر القطاع —</option>
                        @foreach ($qetaaList as $q)
                            <option value="{{ $q->QetaaID }}" data-name="{{ $q->QetaaName }}">
                                {{ $q->QetaaName }}
                            </option>
                        @endforeach
                    </select>

                    <button type="button" class="cq-btn cq-btn--save" id="saveBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        حفظ التغيير
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Confirmation Modal ══ --}}
    <div id="confirmOverlay" class="cq-overlay" aria-hidden="true">
        <div class="cq-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="cq-modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            </div>
            <h2 class="cq-modal-title" id="modalTitle">تأكيد تغيير القطاع</h2>
            <p class="cq-modal-body">
                هل أنت متأكد من نقل
                <strong id="modalPersonName">—</strong>
                من قطاع
                <span class="cq-badge cq-badge--from" id="modalFromQetaa">—</span>
                إلى قطاع
                <span class="cq-badge cq-badge--to" id="modalToQetaa">—</span>
                ؟
            </p>
            <div class="cq-modal-actions">
                <button type="button" class="cq-btn cq-btn--ghost" id="cancelBtn">{{ __('Cancel') }}</button>
                <form id="confirmForm" method="POST" action="">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="qetaa_id" id="hiddenQetaaId">
                    <button type="submit" class="cq-btn cq-btn--confirm">نعم، تأكيد النقل</button>
                </form>
            </div>
        </div>
    </div>


    {{-- ══ Styles ══ --}}
    <style>
        /* ── Tokens ── */
        :root {
            --cq-bg: #f5f6fa;
            --cq-surface: #ffffff;
            --cq-border: #e2e5ef;
            --cq-primary: #3b5bdb;
            --cq-primary-h: #2f4cc4;
            --cq-danger: #e03131;
            --cq-success: #2f9e44;
            --cq-warn: #e67700;
            --cq-text: #1a1d2e;
            --cq-muted: #6b7280;
            --cq-radius: 12px;
            --cq-shadow: 0 2px 16px rgba(30, 40, 90, .09);
        }

        /* ── Layout ── */
        .cq-wrapper {
            max-width: 680px;
            margin: 2rem auto;
            padding: 0 1rem;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        }

        /* ── Header ── */
        .cq-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .cq-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--cq-primary);
            color: #fff;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .cq-title {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--cq-text);
        }

        .cq-subtitle {
            margin: .2rem 0 0;
            font-size: .875rem;
            color: var(--cq-muted);
        }

        /* ── Alerts ── */
        .cq-alert {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem 1rem;
            border-radius: 8px;
            font-size: .875rem;
            margin-bottom: 1rem;
        }

        .cq-alert--success {
            background: #ebfbee;
            color: #2f9e44;
            border: 1px solid #b2f2bb;
        }

        .cq-alert--error {
            background: #fff5f5;
            color: #c92a2a;
            border: 1px solid #ffc9c9;
        }

        /* ── Card ── */
        .cq-card {
            background: var(--cq-surface);
            border-radius: var(--cq-radius);
            border: 1px solid var(--cq-border);
            box-shadow: var(--cq-shadow);
            overflow: hidden;
        }

        /* ── Steps ── */
        .cq-step {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--cq-border);
        }

        .cq-step:last-child {
            border-bottom: none;
        }

        .cq-step--hidden {
            display: none;
        }

        .cq-step-badge {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--cq-primary);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: .8rem;
            font-weight: 700;
            margin-top: .15rem;
        }

        .cq-step-body {
            flex: 1;
            min-width: 0;
        }

        /* ── Form elements ── */
        .cq-label {
            display: block;
            font-size: .875rem;
            font-weight: 600;
            color: var(--cq-text);
            margin-bottom: .25rem;
        }

        .cq-hint {
            font-size: .8rem;
            color: var(--cq-muted);
            margin: 0 0 .75rem;
        }

        .cq-input,
        .cq-select {
            width: 100%;
            padding: .6rem .85rem;
            border: 1.5px solid var(--cq-border);
            border-radius: 8px;
            font-size: .9rem;
            color: var(--cq-text);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            box-sizing: border-box;
        }

        .cq-input:focus,
        .cq-select:focus {
            outline: none;
            border-color: var(--cq-primary);
            box-shadow: 0 0 0 3px rgba(59, 91, 219, .12);
        }

        /* ── Search row ── */
        .cq-search-row {
            display: flex;
            gap: .5rem;
        }

        .cq-search-row .cq-input {
            flex: 1;
        }

        /* ── Results dropdown ── */
        .cq-results {
            margin-top: .5rem;
            border: 1.5px solid var(--cq-border);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        }

        .cq-result-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .7rem 1rem;
            cursor: pointer;
            transition: background .12s;
            border-bottom: 1px solid var(--cq-border);
        }

        .cq-result-item:last-child {
            border-bottom: none;
        }

        .cq-result-item:hover {
            background: #f0f4ff;
        }

        .cq-result-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--cq-primary);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .cq-result-name {
            font-size: .875rem;
            font-weight: 600;
            color: var(--cq-text);
        }

        .cq-result-meta {
            font-size: .78rem;
            color: var(--cq-muted);
        }

        .cq-results-empty {
            padding: 1rem;
            text-align: center;
            color: var(--cq-muted);
            font-size: .875rem;
        }

        /* ── Person card ── */
        .cq-person-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 10px;
            background: #f0f4ff;
            border: 1.5px solid #c5d0fa;
        }

        .cq-person-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--cq-primary);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .cq-person-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--cq-text);
        }

        .cq-person-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .35rem;
        }

        .cq-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .78rem;
            color: var(--cq-muted);
            background: #fff;
            border: 1px solid var(--cq-border);
            padding: .2rem .6rem;
            border-radius: 20px;
        }

        .cq-meta-chip--qetaa {
            color: var(--cq-primary);
            border-color: #c5d0fa;
            background: #eef2ff;
        }

        /* ── Buttons ── */
        .cq-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem 1.1rem;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, transform .1s;
            border: none;
            white-space: nowrap;
        }

        .cq-btn:active {
            transform: scale(.97);
        }

        .cq-btn--search {
            background: var(--cq-primary);
            color: #fff;
        }

        .cq-btn--search:hover {
            background: var(--cq-primary-h);
        }

        .cq-btn--save {
            background: var(--cq-primary);
            color: #fff;
            margin-top: 1.1rem;
            width: 100%;
            justify-content: center;
            padding: .7rem;
        }

        .cq-btn--save:hover:not(:disabled) {
            background: var(--cq-primary-h);
        }

        .cq-btn--save:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .cq-btn--ghost {
            background: transparent;
            color: var(--cq-muted);
            border: 1.5px solid var(--cq-border);
        }

        .cq-btn--ghost:hover {
            background: #f3f4f8;
        }

        .cq-btn--confirm {
            background: var(--cq-danger);
            color: #fff;
        }

        .cq-btn--confirm:hover {
            background: #c92a2a;
        }

        /* ── Modal overlay ── */
        .cq-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 15, 40, .45);
            display: grid;
            place-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
        }

        .cq-overlay.is-open {
            opacity: 1;
            pointer-events: all;
        }

        .cq-modal {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            max-width: 420px;
            width: calc(100% - 2rem);
            box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
            transform: translateY(12px);
            transition: transform .2s;
            text-align: center;
        }

        .cq-overlay.is-open .cq-modal {
            transform: translateY(0);
        }

        .cq-modal-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fff8e1;
            color: var(--cq-warn);
            display: grid;
            place-items: center;
            margin: 0 auto 1rem;
            border: 2px solid #ffe69c;
        }

        .cq-modal-title {
            margin: 0 0 .75rem;
            font-size: 1.1rem;
            color: var(--cq-text);
            font-weight: 700;
        }

        .cq-modal-body {
            font-size: .9rem;
            color: #374151;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .cq-modal-actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
        }

        .cq-modal-actions form {
            margin: 0;
        }

        /* ── Badges (modal) ── */
        .cq-badge {
            display: inline-block;
            padding: .15rem .55rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: .85rem;
        }

        .cq-badge--from {
            background: #fff0f0;
            color: var(--cq-danger);
        }

        .cq-badge--to {
            background: #ebfbee;
            color: var(--cq-success);
        }

        /* ── Loading spinner inside results ── */
        .cq-spinner {
            display: flex;
            justify-content: center;
            padding: 1rem;
        }

        .cq-spinner::after {
            content: '';
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 3px solid #e2e5ef;
            border-top-color: var(--cq-primary);
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>


    {{-- ══ Script ══ --}}
    <script>
        (function() {
            /* ── State ── */
            let selectedPerson = null; // { id, name, currentQetaaId, currentQetaaName }

            /* ── DOM refs ── */
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const searchResults = document.getElementById('searchResults');
            const changeSection = document.getElementById('changeSection');
            const personInitials = document.getElementById('personInitials');
            const personName = document.getElementById('personName');
            const personIdDisplay = document.getElementById('personIdDisplay');
            const currentQetaaName = document.getElementById('currentQetaaName');
            const newQetaaSelect = document.getElementById('newQetaaSelect');
            const saveBtn = document.getElementById('saveBtn');
            const confirmOverlay = document.getElementById('confirmOverlay');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmForm = document.getElementById('confirmForm');
            const hiddenQetaaId = document.getElementById('hiddenQetaaId');
            const modalPersonName = document.getElementById('modalPersonName');
            const modalFromQetaa = document.getElementById('modalFromQetaa');
            const modalToQetaa = document.getElementById('modalToQetaa');

            /* ── Helpers ── */
            function initials(name) {
                return (name || '؟').split(' ').slice(0, 2).map(w => w[0]).join('');
            }

            /* ── Search ── */
            async function doSearch() {
                const q = searchInput.value.trim();
                if (!q) return;

                searchResults.innerHTML = '<div class="cq-spinner"></div>';
                searchResults.style.display = 'block';

                try {
                    const res = await fetch(`{{ route('person.search') }}?q=${encodeURIComponent(q)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();

                    if (!data.length) {
                        searchResults.innerHTML = '<div class="cq-results-empty">{{ __('No results') }}</div>';
                        return;
                    }

                    searchResults.innerHTML = data.map(p => `
                <div class="cq-result-item" data-id="${p.PersonID}"
                     data-name="${p.FullName}"
                     data-qetaa-id="${p.QetaaID ?? ''}"
                     data-qetaa-name="${p.QetaaName ?? 'غير محدد'}">
                    <div class="cq-result-avatar">${initials(p.FullName)}</div>
                    <div>
                        <div class="cq-result-name">${p.FullName}</div>
                        <div class="cq-result-meta">هوية: ${p.NationalID ?? p.PersonID} &nbsp;|&nbsp; قطاع: ${p.QetaaName ?? 'غير محدد'}</div>
                    </div>
                </div>
            `).join('');

                    searchResults.querySelectorAll('.cq-result-item').forEach(item => {
                        item.addEventListener('click', () => selectPerson(item));
                    });

                } catch (e) {
                    searchResults.innerHTML = '<div class="cq-results-empty">حدث خطأ أثناء البحث</div>';
                }
            }

            searchBtn.addEventListener('click', doSearch);
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') doSearch();
            });

            /* ── Select person from results ── */
            function selectPerson(item) {
                selectedPerson = {
                    id: item.dataset.id,
                    name: item.dataset.name,
                    currentQetaaId: item.dataset.qetaaId,
                    currentQetaaName: item.dataset.qetaaName,
                };

                // Update person card
                personInitials.textContent = initials(selectedPerson.name);
                personName.textContent = selectedPerson.name;
                personIdDisplay.textContent = selectedPerson.id;
                currentQetaaName.textContent = selectedPerson.currentQetaaName;

                // Pre-select current Qetaa (so user sees where they are)
                for (let opt of newQetaaSelect.options) {
                    opt.selected = (opt.value == selectedPerson.currentQetaaId);
                }

                // Reveal step 2
                changeSection.classList.remove('cq-step--hidden');
                changeSection.style.display = 'flex';

                // Hide results
                searchResults.style.display = 'none';
                searchInput.value = selectedPerson.name;

                checkSaveReady();
            }

            /* ── Enable save only when a different Qetaa is chosen ── */
            newQetaaSelect.addEventListener('change', checkSaveReady);

            function checkSaveReady() {
                if (!selectedPerson) {
                    saveBtn.disabled = true;
                    return;
                }
                const v = newQetaaSelect.value;
                saveBtn.disabled = (!v || v == selectedPerson.currentQetaaId);
            }

            /* ── Open confirmation modal ── */
            saveBtn.addEventListener('click', () => {
                if (!selectedPerson) return;
                const opt = newQetaaSelect.options[newQetaaSelect.selectedIndex];
                const newName = opt.dataset.name || opt.text;
                const newId = opt.value;

                modalPersonName.textContent = selectedPerson.name;
                modalFromQetaa.textContent = selectedPerson.currentQetaaName;
                modalToQetaa.textContent = newName;

                // Set form action dynamically
                confirmForm.action = `{{ url('/person') }}/${selectedPerson.id}/change-qetaa`;
                hiddenQetaaId.value = newId;

                confirmOverlay.classList.add('is-open');
                confirmOverlay.setAttribute('aria-hidden', 'false');
            });

            /* ── Close modal ── */
            cancelBtn.addEventListener('click', closeModal);
            confirmOverlay.addEventListener('click', e => {
                if (e.target === confirmOverlay) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeModal();
            });

            function closeModal() {
                confirmOverlay.classList.remove('is-open');
                confirmOverlay.setAttribute('aria-hidden', 'true');
            }
        })();
    </script>
@endsection
