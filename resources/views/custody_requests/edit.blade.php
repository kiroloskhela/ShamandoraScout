@extends('layouts.app', ['pageTitle' => __('Edit custody request')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">تعديل طلب عهدة</h1>
            <p class="text-gray-600">يمكن تعديل الطلب طالما أنه قيد المراجعة فقط</p>
        </div>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc pr-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="leading-5">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form id="requestForm" method="POST" action="{{ route('custody_requests.update', $requestRow->RequestID) }}">
            @csrf
            @method('PATCH')

            {{-- Step 1: Dates --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">١) التاريخ</h2>
                    <span class="text-xs text-gray-500">تحديث الفترة</span>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('From date') }}</label>
                        <input type="date" name="date_from" id="date_from"
                            value="{{ old('date_from', $requestRow->DateFrom) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('To date') }}</label>
                        <input type="date" name="date_to" id="date_to"
                            value="{{ old('date_to', $requestRow->DateTo) }}"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                            required>
                    </div>
                </div>
            </div>

            {{-- Optional dropdowns --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">{{ __('Additional info (optional)') }}</h2>
                    <span class="text-xs text-gray-500">القطاع / نوع الفعالية</span>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Sector') }}</label>
                        <select name="qetaa_id" id="qetaa_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="">-- بدون --</option>
                            @foreach ($qetaat as $q)
                                <option value="{{ $q->QetaaID }}"
                                    {{ (string) old('qetaa_id', $requestRow->QetaaID) === (string) $q->QetaaID ? 'selected' : '' }}>
                                    {{ $q->QetaaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">نوع الفعالية</label>
                        <select name="event_type_id" id="event_type_id"
                            class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="">-- بدون --</option>
                            @foreach ($eventTypes as $e)
                                <option value="{{ $e->EventTypeID }}"
                                    {{ (string) old('event_type_id', $requestRow->EventTypeID) === (string) $e->EventTypeID ? 'selected' : '' }}>
                                    {{ $e->EventTypeName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Search --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">٢) تعديل الأصناف</h2>
                    <span class="text-xs text-gray-500">ابحث ثم أضف</span>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">ابحث عن الصنف</label>
                    <input type="text" id="itemSearch" placeholder="اكتب اسم الصنف..."
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                        autocomplete="off">

                    <div id="searchResults"
                        class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden">
                    </div>

                    <p class="mt-2 text-xs text-gray-500">اكتب حرفين على الأقل لعرض النتائج</p>
                </div>
            </div>

            {{-- Step 3: Selected items --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-yellow-300">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">٣) الأصناف المختارة</h2>
                    <span id="itemsCount" class="text-xs text-gray-500">0 صنف</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden">
                        <thead class="bg-slate-50">
                            <tr class="text-sm text-slate-700">
                                <th class="p-3 border-b">م</th>
                                <th class="p-3 border-b">{{ __('Item') }}</th>
                                <th class="p-3 border-b">{{ __('Unit') }}</th>
                                <th class="p-3 border-b">الكمية المطلوبة</th>
                                <th class="p-3 border-b">{{ __('Delete') }}</th>
                            </tr>
                        </thead>
                        <tbody id="selectedItemsBody" class="text-sm text-slate-800"></tbody>
                    </table>
                </div>

                <p id="emptyHint" class="mt-4 text-sm text-gray-500 text-center">لم يتم اختيار أي صنف بعد.</p>
            </div>

            {{-- Note --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">{{ __('Note (optional)') }}</h2>
                    <span class="text-xs text-gray-500">{{ __('Message to admin') }}</span>
                </div>

                <textarea name="user_note" id="user_note" rows="3"
                    class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                    placeholder="{{ __('Write any note...') }}">{{ old('user_note', $requestRow->UserNote) }}</textarea>
            </div>

            {{-- Hidden items container --}}
            <div id="itemsHiddenContainer"></div>

            {{-- Actions --}}
            <div class="text-center">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    حفظ التعديلات
                </button>

                <a href="{{ route('custody_requests.show', $requestRow->RequestID) }}"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200 mr-2">{{ __('Back') }}</a>
            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inventory = @json($inventory);
            const existingItems = @json($existingItems); // pure array prepared in controller ✅

            const searchInput = document.getElementById('itemSearch');
            const searchResults = document.getElementById('searchResults');

            const selectedBody = document.getElementById('selectedItemsBody');
            const itemsCountEl = document.getElementById('itemsCount');
            const emptyHintEl = document.getElementById('emptyHint');
            const itemsHiddenContainer = document.getElementById('itemsHiddenContainer');
            const form = document.getElementById('requestForm');

            let selectedItems = Array.isArray(existingItems) ? existingItems.slice() : [];

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [s]));
            }

            function setResultsVisible(visible) {
                searchResults.classList.toggle('hidden', !visible);
            }

            function updateCounts() {
                itemsCountEl.textContent = `${selectedItems.length} صنف`;
                emptyHintEl.classList.toggle('hidden', selectedItems.length > 0);
            }

            function renderHiddenInputs() {
                itemsHiddenContainer.innerHTML = '';
                selectedItems.forEach((it, idx) => {
                    const inputInv = document.createElement('input');
                    inputInv.type = 'hidden';
                    inputInv.name = `items[${idx}][inventory_id]`;
                    inputInv.value = it.inventory_id;
                    itemsHiddenContainer.appendChild(inputInv);

                    const inputQty = document.createElement('input');
                    inputQty.type = 'hidden';
                    inputQty.name = `items[${idx}][qty]`;
                    inputQty.value = it.qty;
                    itemsHiddenContainer.appendChild(inputQty);
                });
            }

            function renderSelectedItems() {
                selectedBody.innerHTML = '';

                selectedItems.forEach((it, idx) => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b hover:bg-slate-50 transition';
                    tr.innerHTML = `
                <td class="p-3">${idx + 1}</td>
                <td class="p-3 text-right font-medium text-slate-900">${escapeHtml(it.name)}</td>
                <td class="p-3">${escapeHtml(it.unit || '')}</td>
                <td class="p-3">
                    <input type="number"
                        class="w-24 h-10 border rounded-lg text-center border-slate-200 focus:border-blue-500 focus:outline-none"
                        min="1"
                        value="${it.qty}"
                        data-idx="${idx}">
                </td>
                <td class="p-3">
                    <button type="button"
                        class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition border border-red-200"
                        data-remove="${idx}">{{ __('Delete') }}</button>
                </td>
            `;
                    selectedBody.appendChild(tr);
                });

                // qty handlers
                selectedBody.querySelectorAll('input[type="number"]').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const i = parseInt(this.dataset.idx, 10);
                        let v = parseInt(this.value, 10);
                        if (!v || v < 1) v = 1;
                        this.value = v;
                        selectedItems[i].qty = v;
                        renderHiddenInputs();
                    });
                });

                // remove handlers
                selectedBody.querySelectorAll('button[data-remove]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const i = parseInt(this.dataset.remove, 10);
                        selectedItems.splice(i, 1);
                        renderSelectedItems();
                        renderHiddenInputs();
                        updateCounts();
                    });
                });

                updateCounts();
                renderHiddenInputs();
            }

            renderSelectedItems();

            // Search inventory
            let timer = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const q = searchInput.value.toLowerCase().trim();
                    searchResults.innerHTML = '';

                    if (!q || q.length < 2) {
                        setResultsVisible(false);
                        return;
                    }

                    const matches = inventory
                        .filter(x => (x.ItemName || '').toLowerCase().includes(q))
                        .slice(0, 20);

                    if (!matches.length) {
                        searchResults.innerHTML =
                            `<div class="p-3 text-sm text-gray-500">{{ __('No results') }}</div>`;
                        setResultsVisible(true);
                        return;
                    }

                    matches.forEach(item => {
                        const row = document.createElement('button');
                        row.type = 'button';
                        row.className =
                            'w-full text-right p-3 hover:bg-slate-50 transition flex items-center justify-between';

                        const unit = item.ItemMeasuringUnit || '';
                        const available = (item.ItemQuantity === null || item
                                .ItemQuantity === undefined) ? '' :
                            ` • المتاح: ${item.ItemQuantity}`;

                        row.innerHTML = `
                    <div>
                        <div class="text-sm text-slate-800">${escapeHtml(item.ItemName)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(unit)}${available}</div>
                    </div>
                    <div class="text-xs text-gray-400">{{ __('Add') }}</div>
                `;

                        row.addEventListener('click', function() {
                            const exists = selectedItems.some(i => String(i
                                .inventory_id) === String(item.InventoryID));
                            if (exists) {
                                alert('هذا الصنف موجود بالفعل ضمن الطلب.');
                                return;
                            }

                            selectedItems.push({
                                inventory_id: item.InventoryID,
                                name: item.ItemName,
                                unit: unit,
                                qty: 1,
                            });

                            renderSelectedItems();
                            searchInput.value = '';
                            searchResults.innerHTML = '';
                            setResultsVisible(false);
                        });

                        searchResults.appendChild(row);
                    });

                    setResultsVisible(true);
                }, 120);
            });

            document.addEventListener('click', function(e) {
                const inside = searchResults.contains(e.target) || searchInput.contains(e.target);
                if (!inside) setResultsVisible(false);
            });

            // Final validation before submit
            form.addEventListener('submit', function(e) {
                if (selectedItems.length === 0) {
                    e.preventDefault();
                    alert('من فضلك اختر صنف واحد على الأقل.');
                    return;
                }
                // hidden inputs already in sync
            });
        });
    </script>
@endsection
