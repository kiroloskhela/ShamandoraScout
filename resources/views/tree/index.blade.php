{{-- resources/views/tree/index.blade.php --}}
@extends('layouts.app')

@section('content')
    @php
        $typeLabel = [2 => 'فريق', 3 => 'طليعة'];
        $typeBadgeClass = [2 => 'badge--fareeq', 3 => 'badge--taleia'];
        $pageTitle = $pageTitle ?? 'هيكل الفريق';
        $servedQetaas = $servedQetaas ?? collect();
        $selectedQetaaId = $selectedQetaaId ?? request('qetaa');
    @endphp

    <div class="qt-root" dir="rtl">

        {{-- ══ TOPBAR ══════════════════════════════════════════════════════════════ --}}
        <header class="qt-topbar">
            <div class="qt-topbar__brand">
                <svg class="qt-topbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M12 3 L21 9 L21 21 L3 21 L3 9 Z" />
                    <path d="M9 21 L9 14 L15 14 L15 21" />
                    <path d="M12 3 L12 8" />
                    <circle cx="12" cy="8" r="1.5" fill="currentColor" />
                </svg>
                <div>
                    <h1 class="qt-topbar__title">{{ $pageTitle }}</h1>
                    <p class="qt-topbar__subtitle">القطاعات والمجموعات المرتبطة بالفريق الذي تخدم فيه</p>
                </div>
            </div>

            <form method="GET" action="{{ url()->current() }}" id="season-form" class="qt-topbar__form">
                @if (request('id'))
                    <input type="hidden" name="id" value="{{ $userId }}">
                @endif
                @if ($servedQetaas->count() > 1)
                    <label class="qt-select-wrap qt-select-wrap--strong">
                        <span class="qt-select-wrap__label">القطاع</span>
                        <select name="qetaa" class="qt-select" onchange="document.getElementById('season-form').submit()">
                            <option value="">كل القطاعات</option>
                            @foreach ($servedQetaas as $qetaaOption)
                                <option value="{{ $qetaaOption->QetaaID }}"
                                    {{ (string) $qetaaOption->QetaaID === (string) $selectedQetaaId ? 'selected' : '' }}>
                                    {{ $qetaaOption->QetaaName }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="qt-select-wrap__arrow" viewBox="0 0 10 6">
                            <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" />
                        </svg>
                    </label>
                @endif
                <label class="qt-select-wrap">
                    <span class="qt-select-wrap__label">الموسم</span>
                    <select name="season" class="qt-select" onchange="document.getElementById('season-form').submit()">
                        @foreach ($seasons as $s)
                            <option value="{{ $s->SeasonID }}" {{ $s->SeasonID == $currentSeasonId ? 'selected' : '' }}>
                                {{ $s->SeasonName ?? $s->SeasonYear }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="qt-select-wrap__arrow" viewBox="0 0 10 6">
                        <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" />
                    </svg>
                </label>
            </form>
        </header>

        {{-- ══ STATS STRIP ═════════════════════════════════════════════════════════ --}}
        @php
            $servedCount = $tree->where('is_served', true)->count();
            $totalPeople = $tree->sum('total_people');
            $totalGroups = $tree->sum('total_groups');
        @endphp
        <div class="qt-stats">
            <div class="qt-stat">
                <span class="qt-stat__num">{{ $tree->count() }}</span>
                <span class="qt-stat__lbl">قطاع</span>
            </div>
            <div class="qt-stat qt-stat--accent">
                <span class="qt-stat__num">{{ $servedCount }}</span>
                <span class="qt-stat__lbl">تخدمها</span>
            </div>
            <div class="qt-stat">
                <span class="qt-stat__num">{{ $totalGroups }}</span>
                <span class="qt-stat__lbl">مجموعة</span>
            </div>
            <div class="qt-stat">
                <span class="qt-stat__num">{{ $totalPeople }}</span>
                <span class="qt-stat__lbl">شخص</span>
            </div>
        </div>

        <div class="qt-tools">
            <label class="qt-tree-search">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="6.5" cy="6.5" r="5" />
                    <path d="M10.5 10.5l3.5 3.5" />
                </svg>
                <input type="search" id="qt-tree-search" placeholder="ابحث باسم القطاع أو المجموعة أو الشخص"
                    oninput="qtFilterTree(this.value)">
            </label>
            <div class="qt-tool-actions">
                <button class="qt-mini-btn" type="button" onclick="qtSetAll(true)">فتح الكل</button>
                <button class="qt-mini-btn" type="button" onclick="qtSetAll(false)">غلق الكل</button>
            </div>
        </div>

        {{-- ══ TREE ════════════════════════════════════════════════════════════════ --}}
        <div class="qt-tree">
            @forelse ($tree as $node)
                @php
                    $q = $node['qetaa'];
                    $served = $node['is_served'];
                    $editable = $node['is_served'];
                @endphp

                <div class="qt-qetaa {{ $served ? 'qt-qetaa--served' : 'qt-qetaa--dim' }}"
                    data-qetaa-id="{{ $q->QetaaID }}">

                    {{-- Qetaa header --}}
                    <div class="qt-qetaa__head" onclick="qtToggle(this)">
                        <span class="qt-chevron">
                            <svg viewBox="0 0 8 13" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 2l4 4.5-4 4.5" />
                            </svg>
                        </span>

                        <div class="qt-qetaa__info">
                            <span class="qt-qetaa__name">{{ $q->QetaaName }}</span>
                            @if ($node['is_served'])
                                <span class="qt-pill qt-pill--blue">تخدمها</span>
                            @endif
                        </div>

                        <div class="qt-qetaa__meta">
                            <span class="qt-count">
                                <svg viewBox="0 0 16 16" fill="currentColor">
                                    <circle cx="8" cy="5" r="3" />
                                    <path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                                </svg>
                                {{ $node['total_people'] }}
                            </span>

                            @if ($editable)
                                <div class="qt-head-actions" onclick="event.stopPropagation()">
                                    <button class="qt-action-btn qt-action-btn--fareeq"
                                        onclick="openGroupModal({{ $q->QetaaID }}, 2, 0)" title="إضافة فريق">
                                        <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 2v8M2 6h8" />
                                        </svg>
                                        فريق
                                    </button>
                                    <button class="qt-action-btn qt-action-btn--taleia"
                                        onclick="openGroupModal({{ $q->QetaaID }}, 3, 0)" title="إضافة طليعة مباشرة">
                                        <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 2v8M2 6h8" />
                                        </svg>
                                        طليعة
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Qetaa body --}}
                    <div class="qt-qetaa__body">
                        @if ($node['groups']->isEmpty())
                            <p class="qt-empty">لا توجد مجموعات في هذا القطاع</p>
                        @else
                            <div class="qt-groups">
                                @foreach ($node['groups'] as $group)
                                    @include('tree._group', [
                                        'group' => $group,
                                        'qetaaId' => $q->QetaaID,
                                        'isServed' => $editable,
                                        'seasonId' => $currentSeasonId,
                                    ])
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="qt-empty-panel">
                    لا يوجد هيكل فريق مرتبط بحسابك حالياً
                </div>
            @endforelse
        </div>

        {{-- ══ MODALS ══════════════════════════════════════════════════════════════ --}}

        {{-- Add Group Modal --}}
        <div id="modal-group" class="qt-overlay" onclick="if(event.target===this)closeModal('modal-group')">
            <div class="qt-modal">
                <div class="qt-modal__header">
                    <span id="modal-group-title">إضافة مجموعة</span>
                    <button class="qt-modal__close" onclick="closeModal('modal-group')">
                        <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 1l10 10M11 1L1 11" />
                        </svg>
                    </button>
                </div>
                <div class="qt-modal__body">
                    <input type="hidden" id="m-qetaa-id">
                    <input type="hidden" id="m-group-type">
                    <input type="hidden" id="m-parent-id">
                    <label class="qt-field">
                        <span class="qt-field__label">اسم المجموعة</span>
                        <input type="text" id="m-group-name" class="qt-input" placeholder="أدخل الاسم…">
                    </label>
                </div>
                <div class="qt-modal__footer">
                    <button class="qt-btn qt-btn--ghost" onclick="closeModal('modal-group')">إلغاء</button>
                    <button class="qt-btn qt-btn--primary" onclick="submitGroup()">حفظ</button>
                </div>
            </div>
        </div>

        {{-- Add Person Modal --}}
        <div id="modal-person" class="qt-overlay" onclick="if(event.target===this)closeModal('modal-person')">
            <div class="qt-modal qt-modal--wide">
                <div class="qt-modal__header">
                    <span>إضافة أشخاص</span>
                    <button class="qt-modal__close" onclick="closeModal('modal-person')">
                        <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 1l10 10M11 1L1 11" />
                        </svg>
                    </button>
                </div>
                <div class="qt-modal__body">
                    <input type="hidden" id="m-person-group-id">

                    <label class="qt-field">
                        <span class="qt-field__label">ابحث عن شخص</span>
                        <div class="qt-search-wrap">
                            <svg class="qt-search-wrap__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <circle cx="6.5" cy="6.5" r="5" />
                                <path d="M10.5 10.5l3.5 3.5" />
                            </svg>
                            <input type="text" id="m-person-search" class="qt-input qt-input--search"
                                placeholder="اسم أو كود شمندورة…" autocomplete="off" oninput="searchPersons(this.value)">
                        </div>
                        <div id="person-suggestions" class="qt-suggestions" style="display:none"></div>
                    </label>

                    <div id="person-selected-list" class="qt-selected-list" style="display:none"></div>

                    <input type="hidden" id="m-person-id">

                    <label class="qt-field" style="margin-top:10px">
                        <span class="qt-field__label">رتبة المضافين <span
                                style="color:#aaa;font-size:11px">(اختياري)</span></span>
                        <select id="m-rotba-id" class="qt-select qt-select--field">
                            <option value="">— لا تغيير —</option>
                        </select>
                    </label>
                </div>
                <div class="qt-modal__footer">
                    <button class="qt-btn qt-btn--ghost" onclick="closeModal('modal-person')">إلغاء</button>
                    <button class="qt-btn qt-btn--primary" onclick="submitPerson()">حفظ</button>
                </div>
            </div>
        </div>

        {{-- Edit Person Rotba Modal --}}
        <div id="modal-rotba" class="qt-overlay" onclick="if(event.target===this)closeModal('modal-rotba')">
            <div class="qt-modal">
                <div class="qt-modal__header">
                    <span>تعديل الرتبة</span>
                    <button class="qt-modal__close" onclick="closeModal('modal-rotba')">
                        <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 1l10 10M11 1L1 11" />
                        </svg>
                    </button>
                </div>
                <div class="qt-modal__body">
                    <input type="hidden" id="edit-rotba-person-id">
                    <input type="hidden" id="edit-rotba-group-id">
                    <label class="qt-field">
                        <span class="qt-field__label">الرتبة</span>
                        <select id="edit-rotba-id" class="qt-select qt-select--field">
                            <option value="">بدون رتبة</option>
                        </select>
                    </label>
                </div>
                <div class="qt-modal__footer">
                    <button class="qt-btn qt-btn--ghost" onclick="closeModal('modal-rotba')">إلغاء</button>
                    <button class="qt-btn qt-btn--primary" onclick="submitRotbaEdit()">حفظ</button>
                </div>
            </div>
        </div>

    </div>{{-- /qt-root --}}

    {{-- ══════════════════════════════════════════════ STYLES ══════════════════ --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600&display=swap');

        :root {
            --qt-bg: #f4f5f7;
            --qt-surface: #ffffff;
            --qt-border: #e8eaed;
            --qt-border-soft: #f0f1f3;
            --qt-text: #1a1d23;
            --qt-text-muted: #6b7280;
            --qt-text-faint: #b0b8c4;

            --qt-blue: #2563eb;
            --qt-blue-soft: #eff6ff;
            --qt-blue-mid: #bfdbfe;
            --qt-green: #16a34a;
            --qt-green-soft: #f0fdf4;
            --qt-green-mid: #bbf7d0;
            --qt-amber: #d97706;
            --qt-amber-soft: #fffbeb;

            --qt-radius-sm: 6px;
            --qt-radius: 10px;
            --qt-radius-lg: 14px;
            --qt-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --qt-shadow-md: 0 4px 16px rgba(0, 0, 0, .09);
            --qt-shadow-lg: 0 12px 40px rgba(0, 0, 0, .14);

            --qt-transition: .18s ease;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .qt-root {
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            direction: rtl;
            background: var(--qt-bg);
            min-height: 100vh;
            padding: 24px 20px 48px;
            color: var(--qt-text);
        }

        /* ── Topbar ──────────────────────────────────────────────────────────────── */
        .qt-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .qt-topbar__brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qt-topbar__icon {
            width: 26px;
            height: 26px;
            color: var(--qt-blue);
        }

        .qt-topbar__title {
            font-size: 17px;
            font-weight: 600;
            letter-spacing: 0;
            margin-bottom: 2px;
        }

        .qt-topbar__subtitle {
            font-size: 12px;
            color: var(--qt-text-muted);
            line-height: 1.4;
        }

        .qt-topbar__form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qt-select-wrap {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--qt-surface);
            border: 1px solid var(--qt-border);
            border-radius: var(--qt-radius);
            padding: 0 10px;
            height: 36px;
            cursor: pointer;
            box-shadow: var(--qt-shadow);
        }

        .qt-select-wrap--strong {
            border-color: var(--qt-blue-mid);
            background: linear-gradient(180deg, #ffffff 0%, var(--qt-blue-soft) 100%);
        }

        .qt-select-wrap__label {
            font-size: 11px;
            color: var(--qt-text-muted);
            white-space: nowrap;
        }

        .qt-select-wrap__arrow {
            width: 10px;
            height: 6px;
            color: var(--qt-text-muted);
            flex-shrink: 0;
        }

        .qt-select {
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: 13px;
            color: var(--qt-text);
            outline: none;
            cursor: pointer;
            padding: 0;
            direction: rtl;
        }

        .qt-link-btn {
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 14px;
            border-radius: var(--qt-radius);
            border: 1px solid var(--qt-border);
            background: var(--qt-surface);
            color: var(--qt-text-muted);
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            box-shadow: var(--qt-shadow);
            white-space: nowrap;
            transition: background var(--qt-transition), color var(--qt-transition), border-color var(--qt-transition);
        }

        .qt-link-btn:hover,
        .qt-link-btn.is-active {
            background: var(--qt-blue);
            color: #fff;
            border-color: var(--qt-blue);
        }

        /* ── Stats strip ─────────────────────────────────────────────────────────── */
        .qt-stats {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .qt-stat {
            background: var(--qt-surface);
            border: 1px solid var(--qt-border);
            border-radius: var(--qt-radius);
            padding: 10px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            box-shadow: var(--qt-shadow);
            min-width: 80px;
        }

        .qt-stat--accent {
            border-color: var(--qt-blue-mid);
            background: var(--qt-blue-soft);
        }

        .qt-stat__num {
            font-size: 22px;
            font-weight: 600;
            line-height: 1;
            color: var(--qt-text);
        }

        .qt-stat--accent .qt-stat__num {
            color: var(--qt-blue);
        }

        .qt-stat__lbl {
            font-size: 11px;
            color: var(--qt-text-muted);
        }

        /* ── Tools ──────────────────────────────────────────────────────────────── */
        .qt-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .qt-tree-search {
            position: relative;
            flex: 1;
            min-width: min(100%, 280px);
        }

        .qt-tree-search svg {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: var(--qt-text-faint);
            pointer-events: none;
        }

        .qt-tree-search input {
            width: 100%;
            height: 40px;
            padding: 0 38px 0 12px;
            border: 1px solid var(--qt-border);
            border-radius: var(--qt-radius);
            background: var(--qt-surface);
            box-shadow: var(--qt-shadow);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            color: var(--qt-text);
        }

        .qt-tree-search input:focus {
            border-color: var(--qt-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .qt-tool-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .qt-mini-btn {
            height: 36px;
            padding: 0 14px;
            border-radius: var(--qt-radius);
            border: 1px solid var(--qt-border);
            background: var(--qt-surface);
            color: var(--qt-text);
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: var(--qt-shadow);
            transition: background var(--qt-transition), border-color var(--qt-transition), color var(--qt-transition);
        }

        .qt-mini-btn:hover {
            background: var(--qt-blue-soft);
            border-color: var(--qt-blue-mid);
            color: var(--qt-blue);
        }

        /* ── Tree container ──────────────────────────────────────────────────────── */
        .qt-tree {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* ── Qetaa block ─────────────────────────────────────────────────────────── */
        .qt-qetaa {
            background: var(--qt-surface);
            border: 1px solid var(--qt-border);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
            overflow: hidden;
            transition: box-shadow var(--qt-transition);
        }

        .qt-qetaa:hover {
            box-shadow: var(--qt-shadow-md);
        }

        .qt-qetaa--dim {
            opacity: .55;
        }

        .qt-qetaa--served {
            border-color: #d6e4ff;
        }

        .qt-qetaa--served:hover {
            box-shadow: 0 4px 16px rgba(37, 99, 235, .1);
        }

        .qt-qetaa__head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            cursor: pointer;
            user-select: none;
            transition: background var(--qt-transition), border-color var(--qt-transition);
            border-right: 4px solid var(--qt-blue);
        }

        .qt-qetaa__head:hover {
            background: #fafbfc;
        }

        .qt-qetaa__head.is-open {
            background: linear-gradient(90deg, var(--qt-blue-soft), #fff);
        }

        .qt-chevron {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--qt-text-faint);
            transition: transform var(--qt-transition);
        }

        .qt-chevron svg {
            width: 8px;
            height: 13px;
        }

        .is-open .qt-chevron {
            transform: rotate(90deg);
            color: var(--qt-blue);
        }

        .qt-qetaa__info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .qt-qetaa__name {
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .qt-qetaa__meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .qt-count {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--qt-text-muted);
            background: var(--qt-bg);
            border: 1px solid var(--qt-border);
            padding: 2px 8px;
            border-radius: 20px;
        }

        .qt-count svg {
            width: 12px;
            height: 12px;
        }

        .qt-pill {
            font-size: 10px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 20px;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .qt-pill--blue {
            background: var(--qt-blue-soft);
            color: var(--qt-blue);
            border: 1px solid var(--qt-blue-mid);
        }

        .qt-pill--green {
            background: var(--qt-green-soft);
            color: var(--qt-green);
            border: 1px solid var(--qt-green-mid);
        }

        .qt-pill--amber {
            background: var(--qt-amber-soft);
            color: var(--qt-amber);
        }

        .qt-head-actions {
            display: flex;
            gap: 6px;
        }

        .qt-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: inherit;
            font-size: 11px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: var(--qt-radius-sm);
            cursor: pointer;
            border: 1px dashed;
            transition: background var(--qt-transition), color var(--qt-transition);
            white-space: nowrap;
        }

        .qt-action-btn svg {
            width: 10px;
            height: 10px;
            flex-shrink: 0;
        }

        .qt-action-btn--taleia {
            border-color: var(--qt-blue-mid);
            color: var(--qt-blue);
            background: transparent;
        }

        .qt-action-btn--taleia:hover {
            background: var(--qt-blue-soft);
        }

        .qt-action-btn--fareeq {
            border-color: var(--qt-green-mid);
            color: var(--qt-green);
            background: transparent;
        }

        .qt-action-btn--fareeq:hover {
            background: var(--qt-green-soft);
        }

        .qt-qetaa__body {
            display: none;
            border-top: 1px solid var(--qt-border-soft);
            background: linear-gradient(180deg, #fbfdff 0%, #ffffff 72%);
        }

        .qt-qetaa__body.is-open {
            display: block;
        }

        /* ── Groups ──────────────────────────────────────────────────────────────── */
        .qt-groups {
            position: relative;
            padding: 14px 22px 16px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .qt-groups::before {
            content: "";
            position: absolute;
            top: 14px;
            bottom: 18px;
            right: 10px;
            width: 2px;
            background: #dbe7ff;
        }

        .qt-group {
            position: relative;
            border: 1px solid var(--qt-border);
            border-radius: 8px;
            overflow: hidden;
            transition: border-color var(--qt-transition);
            background: var(--qt-surface);
            box-shadow: 0 3px 12px rgba(15, 23, 42, .04);
        }

        .qt-group::before {
            content: "";
            position: absolute;
            top: 22px;
            right: -12px;
            width: 12px;
            height: 2px;
            background: #dbe7ff;
        }

        .qt-group:hover {
            border-color: #d1d5db;
        }

        .qt-group__head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #fbfcfe;
            cursor: pointer;
            user-select: none;
            transition: background var(--qt-transition);
        }

        .qt-group--fareeq .qt-group__head {
            border-right: 3px solid var(--qt-green);
        }

        .qt-group--taleia .qt-group__head {
            border-right: 3px solid var(--qt-blue);
        }

        .qt-group__head:hover {
            background: #f3f4f6;
        }

        .qt-group__head.is-open {
            background: #f8fbff;
        }

        .qt-group-chevron {
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--qt-text-faint);
            transition: transform var(--qt-transition);
        }

        .qt-group-chevron svg {
            width: 7px;
            height: 11px;
        }

        .is-open .qt-group-chevron {
            transform: rotate(90deg);
        }

        .qt-group__name {
            flex: 1;
            font-size: 13px;
            font-weight: 500;
        }

        .qt-group__actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .qt-icon-btn {
            width: 26px;
            height: 26px;
            border-radius: var(--qt-radius-sm);
            border: 1px solid var(--qt-border);
            background: var(--qt-surface);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--qt-text-muted);
            transition: background var(--qt-transition), color var(--qt-transition), border-color var(--qt-transition);
        }

        .qt-icon-btn svg {
            width: 11px;
            height: 11px;
        }

        .qt-icon-btn:hover {
            background: var(--qt-blue-soft);
            color: var(--qt-blue);
            border-color: var(--qt-blue-mid);
        }

        .qt-icon-btn--danger:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .qt-group__body {
            display: none;
            padding: 8px 12px 12px;
            border-top: 1px solid var(--qt-border-soft);
        }

        .qt-group__body.is-open {
            display: block;
        }

        /* ── Subgroups ───────────────────────────────────────────────────────────── */
        .qt-subgroups {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin: 2px 16px 10px 0;
            padding-right: 14px;
        }

        .qt-subgroups::before {
            content: "";
            position: absolute;
            top: 4px;
            bottom: 8px;
            right: 0;
            width: 2px;
            background: #d9f7e3;
        }

        .qt-subgroup {
            position: relative;
            border: 1px solid var(--qt-border-soft);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .qt-subgroup::before {
            content: "";
            position: absolute;
            top: 18px;
            right: -14px;
            width: 14px;
            height: 2px;
            background: #d9f7e3;
        }

        .qt-subgroup__head {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            background: var(--qt-surface);
            cursor: pointer;
            user-select: none;
            transition: background var(--qt-transition);
        }

        .qt-subgroup__head:hover {
            background: #f9fafb;
        }

        .qt-subgroup__head.is-open {
            background: var(--qt-green-soft);
        }

        .qt-subgroup__name {
            flex: 1;
            font-size: 13px;
        }

        .qt-subgroup__body {
            display: none;
            padding: 6px 10px 10px;
            border-top: 1px solid var(--qt-border-soft);
        }

        .qt-subgroup__body.is-open {
            display: block;
        }

        /* ── People ──────────────────────────────────────────────────────────────── */
        .qt-people {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 8px;
        }

        .qt-person {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 8px;
            border: 1px solid transparent;
            border-radius: 8px;
            transition: background var(--qt-transition);
        }

        .qt-person:hover {
            background: var(--qt-bg);
            border-color: var(--qt-border-soft);
        }

        .qt-person__avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--qt-blue-soft), var(--qt-blue-mid));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: var(--qt-blue);
            flex-shrink: 0;
            overflow: hidden;
        }

        .qt-person__avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .qt-person__info {
            flex: 1;
            min-width: 0;
        }

        .qt-person__name {
            font-size: 13px;
            line-height: 1.3;
        }

        .qt-person__code {
            font-size: 11px;
            color: var(--qt-text-muted);
            direction: ltr;
            display: inline-block;
        }

        .qt-person__rotba {
            font-size: 10px;
            background: var(--qt-bg);
            border: 1px solid var(--qt-border);
            color: var(--qt-text-muted);
            padding: 1px 7px;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* ── Group bottom actions ─────────────────────────────────────────────────── */
        .qt-group-footer {
            display: flex;
            gap: 6px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--qt-border-soft);
            flex-wrap: wrap;
        }

        /* ── Empty state ─────────────────────────────────────────────────────────── */
        .qt-empty {
            font-size: 12px;
            color: var(--qt-text-faint);
            font-style: italic;
            padding: 6px 4px;
        }

        .qt-empty-panel {
            background: var(--qt-surface);
            border: 1px dashed var(--qt-border);
            border-radius: var(--qt-radius-lg);
            color: var(--qt-text-muted);
            font-size: 13px;
            padding: 22px;
            text-align: center;
            box-shadow: var(--qt-shadow);
        }

        /* ── Modal overlay ───────────────────────────────────────────────────────── */
        .qt-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 20, 30, .45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(3px);
            animation: qt-fade-in .15s ease;
        }

        @keyframes qt-fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .qt-modal {
            background: var(--qt-surface);
            border-radius: var(--qt-radius-lg);
            width: 360px;
            max-width: calc(100vw - 32px);
            box-shadow: var(--qt-shadow-lg);
            animation: qt-slide-up .18s ease;
            overflow: visible;
        }

        .qt-modal--wide {
            width: 460px;
        }

        @keyframes qt-slide-up {
            from {
                transform: translateY(10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .qt-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px 14px;
            border-bottom: 1px solid var(--qt-border);
            font-size: 14px;
            font-weight: 600;
        }

        .qt-modal__close {
            width: 28px;
            height: 28px;
            border: none;
            background: var(--qt-bg);
            border-radius: var(--qt-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--qt-text-muted);
            transition: background var(--qt-transition);
        }

        .qt-modal__close:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .qt-modal__close svg {
            width: 10px;
            height: 10px;
        }

        .qt-modal__body {
            position: relative;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow: visible;
        }

        .qt-modal__footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px 18px;
            border-top: 1px solid var(--qt-border);
            background: var(--qt-bg);
        }

        /* ── Form elements ───────────────────────────────────────────────────────── */
        .qt-field {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .qt-field__label {
            font-size: 12px;
            color: var(--qt-text-muted);
            font-weight: 500;
        }

        .qt-input {
            font-family: inherit;
            font-size: 13px;
            padding: 8px 11px;
            border: 1px solid var(--qt-border);
            border-radius: var(--qt-radius);
            outline: none;
            width: 100%;
            background: var(--qt-surface);
            color: var(--qt-text);
            transition: border-color var(--qt-transition), box-shadow var(--qt-transition);
        }

        .qt-input:focus {
            border-color: var(--qt-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .qt-input::placeholder {
            color: var(--qt-text-faint);
        }

        .qt-select--field {
            font-family: inherit;
            font-size: 13px;
            padding: 8px 11px;
            border: 1px solid var(--qt-border);
            border-radius: var(--qt-radius);
            outline: none;
            width: 100%;
            background: var(--qt-surface);
            color: var(--qt-text);
            direction: rtl;
        }

        .qt-select--field:focus {
            border-color: var(--qt-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .qt-search-wrap {
            position: relative;
        }

        .qt-search-wrap__icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            color: var(--qt-text-faint);
            pointer-events: none;
        }

        .qt-input--search {
            padding-right: 32px;
        }

        /* Autocomplete suggestions */
        .qt-suggestions {
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            left: 0;
            background: var(--qt-surface);
            border: 1px solid var(--qt-border);
            border-radius: var(--qt-radius);
            box-shadow: var(--qt-shadow-md);
            z-index: 10020;
            max-height: 200px;
            overflow-y: auto;
        }

        .qt-suggestion-item {
            width: 100%;
            border: 0;
            background: transparent;
            color: inherit;
            font-family: inherit;
            text-align: right;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background var(--qt-transition);
            font-size: 13px;
        }

        .qt-suggestion-item:hover {
            background: var(--qt-blue-soft);
        }

        .qt-suggestion-avatar,
        .qt-selected-card__avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--qt-blue-soft), var(--qt-blue-mid));
            color: var(--qt-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 12px;
            font-weight: 600;
            overflow: hidden;
        }

        .qt-suggestion-avatar img,
        .qt-selected-card__avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .qt-suggestion-item__main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .qt-suggestion-item__name {
            font-weight: 500;
            line-height: 1.3;
        }

        .qt-suggestion-item__meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .qt-suggestion-item__code {
            font-size: 11px;
            color: var(--qt-text-muted);
            direction: ltr;
        }

        .qt-suggestion-item__id {
            font-size: 11px;
            color: var(--qt-text-muted);
            direction: ltr;
        }

        .qt-suggestion-item__rotba {
            font-size: 11px;
            color: var(--qt-text-faint);
            margin-right: auto;
        }

        /* Selected person card */
        .qt-selected-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 180px;
            overflow-y: auto;
        }

        .qt-selected-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: var(--qt-blue-soft);
            border: 1px solid var(--qt-blue-mid);
            border-radius: var(--qt-radius);
        }

        .qt-selected-card__info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .qt-selected-card__name {
            font-size: 13px;
            font-weight: 500;
            color: var(--qt-text);
        }

        .qt-selected-card__code {
            font-size: 11px;
            color: var(--qt-text-muted);
        }

        .qt-selected-card__rotba {
            font-size: 11px;
            color: var(--qt-blue);
        }

        .qt-selected-card__clear {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: none;
            background: var(--qt-blue-mid);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--qt-blue);
            transition: background var(--qt-transition);
        }

        .qt-selected-card__clear:hover {
            background: #93c5fd;
        }

        .qt-selected-card__clear svg {
            width: 9px;
            height: 9px;
        }

        /* ── Buttons ─────────────────────────────────────────────────────────────── */
        .qt-btn {
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: var(--qt-radius);
            cursor: pointer;
            border: none;
            transition: background var(--qt-transition), transform .1s;
        }

        .qt-btn:active {
            transform: scale(.97);
        }

        .qt-btn--primary {
            background: var(--qt-blue);
            color: #fff;
        }

        .qt-btn--primary:hover {
            background: #1d4ed8;
        }

        .qt-btn--ghost {
            background: var(--qt-surface);
            color: var(--qt-text-muted);
            border: 1px solid var(--qt-border);
        }

        .qt-btn--ghost:hover {
            background: var(--qt-bg);
        }

        /* ── Scrollbar ───────────────────────────────────────────────────────────── */
        .qt-suggestions::-webkit-scrollbar {
            width: 4px;
        }

        .qt-suggestions::-webkit-scrollbar-track {
            background: transparent;
        }

        .qt-suggestions::-webkit-scrollbar-thumb {
            background: var(--qt-border);
            border-radius: 4px;
        }

        @media (max-width: 720px) {
            .qt-root {
                padding: 16px 12px 36px;
            }

            .qt-topbar,
            .qt-topbar__form,
            .qt-tools {
                align-items: stretch;
                flex-direction: column;
            }

            .qt-select-wrap,
            .qt-link-btn,
            .qt-tool-actions,
            .qt-mini-btn {
                width: 100%;
            }

            .qt-tool-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .qt-qetaa__head,
            .qt-group__head,
            .qt-subgroup__head {
                align-items: flex-start;
            }

            .qt-qetaa__meta,
            .qt-group__actions {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .qt-head-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>

    {{-- ══════════════════════════════════════════════ SCRIPTS ══════════════════ --}}
    <script>
        const SEASON_ID = {{ $currentSeasonId ?? 'null' }};
        const CSRF = '{{ csrf_token() }}';

        // ── Toggle ─────────────────────────────────────────────────────────────────
        function qtToggle(header) {
            const body = header.nextElementSibling;
            const open = header.classList.contains('is-open');
            header.classList.toggle('is-open', !open);
            body.classList.toggle('is-open', !open);
        }

        function qtSetOpen(header, open) {
            const body = header.nextElementSibling;
            if (!body) return;
            header.classList.toggle('is-open', open);
            body.classList.toggle('is-open', open);
        }

        function qtSetAll(open) {
            document.querySelectorAll('.qt-qetaa__head, .qt-group__head, .qt-subgroup__head')
                .forEach(header => qtSetOpen(header, open));
        }

        function qtFilterTree(value) {
            const query = value.trim().toLowerCase();

            document.querySelectorAll('.qt-qetaa').forEach(qetaa => {
                const matches = !query || qetaa.textContent.toLowerCase().includes(query);
                qetaa.style.display = matches ? '' : 'none';

                if (query && matches) {
                    const head = qetaa.querySelector('.qt-qetaa__head');
                    if (head) qtSetOpen(head, true);
                }
            });
        }

        function qtEscapeHtml(value) {
            const entities = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return String(value ?? '').replace(/[&<>"']/g, char => entities[char]);
        }

        // ── Group modal ────────────────────────────────────────────────────────────
        function openGroupModal(qetaaId, typeId, parentId) {
            document.getElementById('m-qetaa-id').value = qetaaId;
            document.getElementById('m-group-type').value = typeId;
            document.getElementById('m-parent-id').value = parentId;
            document.getElementById('m-group-name').value = '';
            document.getElementById('modal-group-title').textContent = typeId == 2 ? 'إضافة فريق' : 'إضافة طليعة';
            showModal('modal-group');
            setTimeout(() => document.getElementById('m-group-name').focus(), 80);
        }

        function showModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        async function submitGroup() {
            const name = document.getElementById('m-group-name').value.trim();
            const qetaaId = document.getElementById('m-qetaa-id').value;
            const typeId = document.getElementById('m-group-type').value;
            const parentId = document.getElementById('m-parent-id').value;

            if (!name) {
                document.getElementById('m-group-name').focus();
                return;
            }

            try {
                const res = await fetch('{{ route('qetaa.storeGroup') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        GroupName: name,
                        GroupTypeID: parseInt(typeId),
                        QetaaID: parseInt(qetaaId),
                        IncludedUnderGroupID: parseInt(parentId),
                        SeasonID: SEASON_ID
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || data.message || `HTTP ${res.status}`);
                closeModal('modal-group');
                window.location.reload();
            } catch (e) {
                alert(e.message || 'حدث خطأ');
            }
        }

        async function deleteGroup(groupId) {
            if (!confirm('هل تريد حذف هذه المجموعة؟')) return;
            const url = '{{ route('qetaa.deleteGroup', ['groupId' => '__GROUP_ID__']) }}'.replace('__GROUP_ID__', groupId);
            await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                }
            });
            window.location.reload();
        }

        // ── Person modal ──────────────────────────────────────────────────────────
        let _searchTimer = null;
        let _selectedPersons = [];
        let _rotbaList = null;

        function personInitials(name) {
            const cleanName = String(name || '').trim();
            return cleanName ? cleanName.slice(0, 1) : '؟';
        }

        function createPersonAvatar(person, className) {
            const avatar = document.createElement('span');
            avatar.className = className;

            if (person.AvatarUrl) {
                const img = document.createElement('img');
                img.src = person.AvatarUrl;
                img.alt = person.FullName || '';
                avatar.appendChild(img);
            } else {
                avatar.textContent = personInitials(person.FullName);
            }

            return avatar;
        }

        async function loadRotbaOptions(selectId, emptyText, selectedValue = '') {
            if (!_rotbaList) {
                const res = await fetch('{{ route('qetaa.getRotbaList') }}');
                _rotbaList = await res.json();
            }

            const sel = document.getElementById(selectId);
            sel.innerHTML = '';

            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = emptyText;
            sel.appendChild(emptyOpt);

            _rotbaList.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.RotbaID;
                opt.textContent = r.RotbaName;
                sel.appendChild(opt);
            });

            sel.value = selectedValue ? String(selectedValue) : '';
        }

        async function openPersonModal(groupId) {
            document.getElementById('m-person-group-id').value = groupId;
            document.getElementById('m-person-id').value = '';
            document.getElementById('m-person-search').value = '';
            document.getElementById('person-suggestions').style.display = 'none';
            document.getElementById('m-person-search').style.display = '';
            _selectedPersons = [];
            renderSelectedPersons();

            try {
                await loadRotbaOptions('m-rotba-id', '— لا تغيير —');
            } catch {}

            showModal('modal-person');
            setTimeout(() => document.getElementById('m-person-search').focus(), 80);
        }

        function searchPersons(val) {
            clearTimeout(_searchTimer);
            const sug = document.getElementById('person-suggestions');
            if (val.length < 2) {
                sug.style.display = 'none';
                return;
            }
            _searchTimer = setTimeout(async () => {
                const groupId = document.getElementById('m-person-group-id').value;
                const res = await fetch(`{{ route('qetaa.searchPersons') }}?q=${encodeURIComponent(val)}&group_id=${encodeURIComponent(groupId)}`);
                const list = await res.json();
                if (!list.length) {
                    sug.style.display = 'none';
                    return;
                }

                sug.innerHTML = '';
                list.forEach(p => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'qt-suggestion-item';

                    const person = {
                        PersonID: p.PersonID,
                        FullName: p.FullName || `${p.FirstName ?? ''} ${p.SecondName ?? ''} ${p.ThirdName ?? ''} ${p.FourthName ?? ''}`.trim(),
                        ShamandoraCode: p.ShamandoraCode || '',
                        RotbaName: p.RotbaName || '',
                        AvatarUrl: p.AvatarUrl || ''
                    };

                    const mainEl = document.createElement('span');
                    mainEl.className = 'qt-suggestion-item__main';

                    const nameEl = document.createElement('span');
                    nameEl.className = 'qt-suggestion-item__name';
                    nameEl.textContent = person.FullName;

                    const metaEl = document.createElement('span');
                    metaEl.className = 'qt-suggestion-item__meta';

                    const idEl = document.createElement('span');
                    idEl.className = 'qt-suggestion-item__id';
                    idEl.textContent = `ID: ${person.PersonID}`;

                    const codeEl = document.createElement('span');
                    codeEl.className = 'qt-suggestion-item__code';
                    codeEl.textContent = person.ShamandoraCode;

                    const rotbaEl = document.createElement('span');
                    rotbaEl.className = 'qt-suggestion-item__rotba';
                    rotbaEl.textContent = person.RotbaName;

                    metaEl.append(idEl);
                    if (person.ShamandoraCode) metaEl.append(codeEl);
                    if (person.RotbaName) metaEl.append(rotbaEl);
                    mainEl.append(nameEl, metaEl);
                    item.append(createPersonAvatar(person, 'qt-suggestion-avatar'), mainEl);
                    item.addEventListener('mousedown', event => {
                        event.preventDefault();
                        selectPerson(person);
                    });

                    sug.appendChild(item);
                });
                sug.style.display = 'block';
            }, 300);
        }

        function selectPerson(person) {
            const id = parseInt(person.PersonID, 10);
            if (!_selectedPersons.some(selected => parseInt(selected.PersonID, 10) === id)) {
                _selectedPersons.push(person);
            }
            document.getElementById('m-person-search').value = '';
            document.getElementById('person-suggestions').style.display = 'none';
            renderSelectedPersons();
            document.getElementById('m-person-search').focus();
        }

        function renderSelectedPersons() {
            const list = document.getElementById('person-selected-list');
            list.innerHTML = '';
            list.style.display = _selectedPersons.length ? 'flex' : 'none';

            _selectedPersons.forEach(person => {
                const card = document.createElement('div');
                card.className = 'qt-selected-card';

                const info = document.createElement('div');
                info.className = 'qt-selected-card__info';

                const nameEl = document.createElement('span');
                nameEl.className = 'qt-selected-card__name';
                nameEl.textContent = person.FullName;

                const codeEl = document.createElement('span');
                codeEl.className = 'qt-selected-card__code';
                codeEl.textContent = `ID: ${person.PersonID}${person.ShamandoraCode ? ` | ${person.ShamandoraCode}` : ''}`;

                const rotbaEl = document.createElement('span');
                rotbaEl.className = 'qt-selected-card__rotba';
                rotbaEl.textContent = person.RotbaName || '';

                info.append(nameEl, codeEl);
                if (person.RotbaName) info.append(rotbaEl);

                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'qt-selected-card__clear';
                clearBtn.innerHTML = '<svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1l10 10M11 1L1 11" /></svg>';
                clearBtn.addEventListener('click', () => clearPersonSelection(person.PersonID));

                card.append(createPersonAvatar(person, 'qt-selected-card__avatar'), info, clearBtn);
                list.appendChild(card);
            });
        }

        function clearPersonSelection(personId) {
            _selectedPersons = _selectedPersons.filter(person => parseInt(person.PersonID, 10) !== parseInt(personId, 10));
            renderSelectedPersons();
        }

        async function submitPerson() {
            const groupId = parseInt(document.getElementById('m-person-group-id').value, 10);
            const personIds = _selectedPersons.map(person => parseInt(person.PersonID, 10)).filter(Boolean);
            const rotbaId = document.getElementById('m-rotba-id').value;

            if (!personIds.length) {
                alert('اختر شخصاً واحداً على الأقل');
                return;
            }

            try {
                const res = await fetch('{{ route('qetaa.storePerson') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        PersonIDs: personIds,
                        GroupID: groupId,
                        RotbaID: rotbaId || null
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || data.message || `HTTP ${res.status}`);
                closeModal('modal-person');
                window.location.reload();
            } catch (e) {
                alert(e.message || 'حدث خطأ');
            }
        }

        async function openRotbaModal(personId, groupId, currentRotbaId) {
            document.getElementById('edit-rotba-person-id').value = personId;
            document.getElementById('edit-rotba-group-id').value = groupId;

            try {
                await loadRotbaOptions('edit-rotba-id', 'بدون رتبة', currentRotbaId || '');
            } catch {}

            showModal('modal-rotba');
        }

        async function submitRotbaEdit() {
            const personId = parseInt(document.getElementById('edit-rotba-person-id').value, 10);
            const groupId = parseInt(document.getElementById('edit-rotba-group-id').value, 10);
            const rotbaId = document.getElementById('edit-rotba-id').value;

            try {
                const res = await fetch('{{ route('qetaa.updatePersonRotba') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        PersonID: personId,
                        GroupID: groupId,
                        RotbaID: rotbaId || null
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || data.message || `HTTP ${res.status}`);
                closeModal('modal-rotba');
                window.location.reload();
            } catch (e) {
                alert(e.message || 'حدث خطأ');
            }
        }

        async function removePerson(personId, groupId) {
            if (!confirm('إزالة هذا الشخص من المجموعة؟')) return;
            await fetch('{{ route('qetaa.removePerson') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    PersonID: personId,
                    GroupID: groupId
                }),
            });
            window.location.reload();
        }

        // Close suggestions when clicking outside
        document.addEventListener('click', e => {
            const sug = document.getElementById('person-suggestions');
            if (sug && !sug.contains(e.target) && e.target.id !== 'm-person-search') {
                sug.style.display = 'none';
            }
        });
    </script>
@endsection
