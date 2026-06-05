{{-- resources/views/qetaa/tree.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="qetaa-page" dir="rtl">

        {{-- ── Top bar ── --}}
        <div class="top-bar">
            <div class="top-bar-right">
                <h1 class="page-title">شجرة القطاعات</h1>
            </div>
            <div class="top-bar-left">
                {{-- Season selector --}}
                <form method="GET" action="{{ url()->current() }}" id="season-form">
                    @if (request('id'))
                        <input type="hidden" name="id" value="{{ $userId }}">
                    @endif
                    <select name="season" class="form-select" onchange="document.getElementById('season-form').submit()">
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}"
                                {{ $season->SeasonID == $currentSeasonId ? 'selected' : '' }}>
                                {{ $season->SeasonName ?? $season->SeasonYear }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- ── Tree ── --}}
        <div class="tree-container">
            @foreach ($tree as $node)
                @php $q = $node['qetaa']; @endphp
                <div class="qetaa-block {{ $node['is_served'] ? 'is-served' : 'not-served' }}"
                    data-qetaa-id="{{ $q->QetaaID }}">

                    {{-- Qetaa header --}}
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <span class="chevron">›</span>
                        <span class="header-title">{{ $q->QetaaName }}</span>
                        <span class="person-count">{{ $node['total_people'] }} شخص</span>
                        @if ($node['is_served'])
                            <span class="served-badge">تخدمها</span>
                            <div class="header-actions" onclick="event.stopPropagation()">
                                <button class="add-btn" onclick="openGroupModal({{ $q->QetaaID }}, 2, 0)"
                                    title="إضافة طليعة">
                                    + طليعة
                                </button>
                                <button class="add-btn add-btn--alt" onclick="openGroupModal({{ $q->QetaaID }}, 3, 0)"
                                    title="إضافة فريق">
                                    + فريق
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Qetaa body --}}
                    <div class="accordion-body">
                        @if ($node['groups']->isEmpty())
                            <p class="empty-msg">لا توجد مجموعات في هذا القطاع</p>
                        @else
                            @foreach ($node['groups'] as $group)
                                @include('qetaa._group', [
                                    'group' => $group,
                                    'qetaaId' => $q->QetaaID,
                                    'isServed' => $node['is_served'],
                                    'seasonId' => $currentSeasonId,
                                ])
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Add Group Modal ── --}}
        <div id="group-modal" class="modal-overlay" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <span id="modal-title">إضافة مجموعة</span>
                    <button class="modal-close" onclick="closeModal('group-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="m-qetaa-id">
                    <input type="hidden" id="m-group-type">
                    <input type="hidden" id="m-parent-id">
                    <div class="form-group">
                        <label>الاسم</label>
                        <input type="text" id="m-group-name" class="form-input" placeholder="اسم المجموعة">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('group-modal')">إلغاء</button>
                    <button class="btn btn-primary" onclick="submitGroup()">حفظ</button>
                </div>
            </div>
        </div>

        {{-- ── Add Person Modal ── --}}
        <div id="group-person-modal" class="modal-overlay" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <span id="modal-person-title">إضافة شخص</span>
                    <button class="modal-close" onclick="closeModal('group-person-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="m-person-group-id">
                    <div class="form-group">
                        <label>رقم الشخص</label>
                        <input type="number" id="m-person-id" class="form-input" placeholder="أدخل رقم الشخص">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('group-person-modal')">إلغاء</button>
                    <button class="btn btn-primary" onclick="submitPerson()">حفظ</button>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Styles ── --}}
    <style>
        .qetaa-page {
            padding: 1.5rem;
            font-family: inherit;
            direction: rtl;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .page-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }

        .form-select {
            font-size: 13px;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }

        .tree-container {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        /* Qetaa block */
        .qetaa-block {
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            overflow: hidden;
        }

        .qetaa-block.not-served {
            opacity: .6;
        }

        .qetaa-block.is-served {
            border-color: #c8dff8;
        }

        .accordion-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #f7f8fa;
            cursor: pointer;
            user-select: none;
            transition: background .15s;
        }

        .accordion-header:hover {
            background: #eef1f6;
        }

        .accordion-header.open {
            background: #e8f0fc;
        }

        .chevron {
            font-size: 18px;
            color: #888;
            transition: transform .2s;
            line-height: 1;
        }

        .accordion-header.open .chevron {
            transform: rotate(90deg);
        }

        .header-title {
            font-size: 14px;
            font-weight: 500;
            flex: 1;
        }

        .person-count {
            font-size: 12px;
            color: #888;
            background: #eee;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .served-badge {
            font-size: 11px;
            color: #1a6fc4;
            background: #dbeafe;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .header-actions {
            display: flex;
            gap: 6px;
        }

        .accordion-body {
            display: none;
            padding: 10px 14px 14px;
            border-top: 1px solid #eee;
            background: #fff;
        }

        .accordion-body.open {
            display: block;
        }

        /* Add buttons */
        .add-btn {
            font-size: 12px;
            padding: 4px 10px;
            border: 1px dashed #93c5fd;
            border-radius: 6px;
            background: transparent;
            color: #1d6fb8;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #eff6ff;
        }

        .add-btn--alt {
            border-color: #86efac;
            color: #15803d;
        }

        .add-btn--alt:hover {
            background: #f0fdf4;
        }

        /* Groups */
        .group-block {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .group-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #fafafa;
            cursor: pointer;
            user-select: none;
        }

        .group-header:hover {
            background: #f3f4f6;
        }

        .group-header.open {
            background: #f0f9ff;
        }

        .group-chevron {
            font-size: 16px;
            color: #aaa;
            transition: transform .2s;
            line-height: 1;
        }

        .group-header.open .group-chevron {
            transform: rotate(90deg);
        }

        .group-title {
            font-size: 13px;
            font-weight: 500;
            flex: 1;
        }

        .group-body {
            display: none;
            padding: 8px 12px 12px;
            border-top: 1px solid #f0f0f0;
        }

        .group-body.open {
            display: block;
        }

        /* Subgroup (فريق under فريق or طليعة under فريق) */
        .subgroup-block {
            border: 1px solid #f0f0f0;
            border-radius: 6px;
            margin-bottom: 6px;
            overflow: hidden;
        }

        .subgroup-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            background: #fafafa;
            cursor: pointer;
            user-select: none;
        }

        .subgroup-header:hover {
            background: #f5f5f5;
        }

        .subgroup-header.open {
            background: #f0fdf4;
        }

        .subgroup-chevron {
            font-size: 14px;
            color: #bbb;
            transition: transform .2s;
            line-height: 1;
        }

        .subgroup-header.open .subgroup-chevron {
            transform: rotate(90deg);
        }

        .subgroup-title {
            font-size: 13px;
            flex: 1;
        }

        .subgroup-body {
            display: none;
            padding: 6px 10px 10px;
            border-top: 1px solid #f0f0f0;
        }

        .subgroup-body.open {
            display: block;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .badge-2 {
            background: #dbeafe;
            color: #1d4ed8;
        }

        /* طليعة = blue */
        .badge-3 {
            background: #dcfce7;
            color: #15803d;
        }

        /* فريق = green */

        /* People */
        .people-section {
            margin-top: 6px;
        }

        .people-section-title {
            font-size: 12px;
            color: #999;
            margin-bottom: 4px;
        }

        .person-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .person-row:last-child {
            border-bottom: none;
        }

        .person-name {
            font-size: 13px;
            flex: 1;
        }

        .person-rotba {
            font-size: 11px;
            color: #888;
            background: #f3f4f6;
            padding: 1px 6px;
            border-radius: 10px;
        }

        .delete-btn {
            font-size: 11px;
            color: #dc2626;
            background: none;
            border: none;
            cursor: pointer;
            opacity: .4;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .delete-btn:hover {
            opacity: 1;
        }

        /* Empty */
        .empty-msg {
            font-size: 12px;
            color: #bbb;
            font-style: italic;
            padding: 4px 0;
        }

        /* Group actions row */
        .group-actions {
            display: flex;
            gap: 6px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-box {
            background: #fff;
            border-radius: 12px;
            width: 320px;
            max-width: 90vw;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .15);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            font-size: 15px;
            font-weight: 500;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #888;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }

        .form-input {
            width: 100%;
            font-size: 13px;
            padding: 7px 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
        }

        .form-input:focus {
            border-color: #3b82f6;
        }

        .btn {
            font-size: 13px;
            padding: 7px 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-primary {
            background: #1d6fb8;
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background: #1558a0;
        }

        .btn-secondary {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #f5f5f5;
        }
    </style>

    {{-- ── Scripts ── --}}
    <script>
        const SEASON_ID = {{ $currentSeasonId ?? 'null' }};
        const CSRF = '{{ csrf_token() }}';

        // ── Accordion ────────────────────────────────────────────────────────────────
        function toggleAccordion(header) {
            const body = header.nextElementSibling;
            const isOpen = header.classList.contains('open');
            header.classList.toggle('open', !isOpen);
            body.classList.toggle('open', !isOpen);
        }

        function toggleGroup(header) {
            const body = header.nextElementSibling;
            const isOpen = header.classList.contains('open');
            header.classList.toggle('open', !isOpen);
            body.classList.toggle('open', !isOpen);
        }

        // ── Group modal ──────────────────────────────────────────────────────────────
        function openGroupModal(qetaaId, typeId, parentId) {
            document.getElementById('m-qetaa-id').value = qetaaId;
            document.getElementById('m-group-type').value = typeId;
            document.getElementById('m-parent-id').value = parentId;
            document.getElementById('m-group-name').value = '';
            document.getElementById('modal-title').textContent =
                typeId === 2 ? 'إضافة طليعة' : 'إضافة فريق';
            document.getElementById('group-modal').style.display = 'flex';
            setTimeout(() => document.getElementById('m-group-name').focus(), 50);
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
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        GroupName: name,
                        GroupTypeID: parseInt(typeId),
                        QetaaID: parseInt(qetaaId),
                        IncludedUnderGroupID: parseInt(parentId),
                        SeasonID: SEASON_ID,
                    }),
                });

                let data;
                const text = await res.text();
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    throw new Error(`Unexpected server response: ${text}`);
                }

                if (!res.ok) {
                    throw new Error(data.error || data.message || `HTTP ${res.status}`);
                }

                closeModal('group-modal');
                window.location.reload();
            } catch (error) {
                console.error('submitGroup error:', error);
                alert(error.message || 'حدث خطأ أثناء حفظ المجموعة.');
            }
        }

        async function deleteGroup(groupId) {
            if (!confirm('هل تريد حذف هذه المجموعة؟')) return;
            await fetch(`/qetaa/group/${groupId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
            });
            window.location.reload();
        }

        async function openPersonModal(groupId) {
            document.getElementById('m-person-group-id').value = groupId;
            document.getElementById('m-person-id').value = '';
            document.getElementById('modal-person-title').textContent = 'إضافة شخص';
            document.getElementById('group-person-modal').style.display = 'flex';
            setTimeout(() => document.getElementById('m-person-id').focus(), 50);
        }

        async function submitPerson() {
            const groupId = parseInt(document.getElementById('m-person-group-id').value, 10);
            const personId = parseInt(document.getElementById('m-person-id').value.trim(), 10);

            if (!personId || personId <= 0) {
                alert('أدخل رقم الشخص الصحيح');
                document.getElementById('m-person-id').focus();
                return;
            }

            try {
                const res = await fetch('{{ route('qetaa.storePerson') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        PersonID: personId,
                        GroupID: groupId
                    }),
                });

                let data;
                const text = await res.text();
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    throw new Error(`Unexpected server response: ${text}`);
                }

                if (!res.ok) {
                    throw new Error(data.error || data.message || `HTTP ${res.status}`);
                }

                closeModal('group-person-modal');
                window.location.reload();
            } catch (error) {
                console.error('submitPerson error:', error);
                alert(error.message || 'حدث خطأ أثناء إضافة الشخص.');
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

        // Close modal on overlay click
        document.getElementById('group-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('group-modal');
        });
        document.getElementById('group-person-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('group-person-modal');
        });
    </script>
@endsection
