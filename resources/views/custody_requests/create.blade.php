@extends('layouts.app', ['pageTitle' => __('Custody request')])

@section('content')
    <div class="container mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ __('Custody request') }}</h1>
            <p class="text-gray-600">{{ __('Select dates then choose items and quantities and submit') }}</p>
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

        {{-- Step 1: Dates --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('1) Date') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Choose from / to') }}</span>
            </div>

            <div class="grid md:grid-cols-3 gap-6 items-end">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('From date') }}</label>
                    <input type="date" id="date_from" value="{{ old('date_from') }}"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('To date') }}</label>
                    <input type="date" id="date_to" value="{{ old('date_to') }}"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="same_day" class="w-5 h-5">
                    <label for="same_day" class="text-sm text-gray-700">{{ __('Same day') }}</label>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500">{{ __('When "Same day" is selected, the to date will be disabled automatically.') }}</p>
        </div>

        {{-- Optional Info --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('Additional info (optional)') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Sector / event type') }}</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Sector') }}</label>
                    <select id="qetaa_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">-- {{ __('None') }} --</option>
                        @foreach ($qetaat as $q)
                            <option value="{{ $q->QetaaID }}" {{ old('qetaa_id') == $q->QetaaID ? 'selected' : '' }}>
                                {{ $q->QetaaName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Event type') }}</label>
                    <select id="event_type_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">-- {{ __('None') }} --</option>
                        @foreach ($eventTypes as $e)
                            <option value="{{ $e->EventTypeID }}"
                                {{ old('event_type_id') == $e->EventTypeID ? 'selected' : '' }}>
                                {{ $e->EventTypeName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Step 2: Search Inventory --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('2) Select items') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Search then add') }}</span>
            </div>

            <div class="relative">
                <label class="block mb-2 text-sm text-gray-700">{{ __('Search for item') }}</label>
                <input type="text" id="itemSearch" placeholder="{{ __('Type item name...') }}"
                    class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                    autocomplete="off">

                <div id="searchResults"
                    class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden">
                </div>

                <p class="mt-2 text-xs text-gray-500">{{ __('Type at least two characters to show results') }}</p>
            </div>
        </div>

        {{-- Step 3: Selected Items --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-yellow-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('3) Selected items') }}</h2>
                <span id="itemsCount" class="text-xs text-gray-500">{{ __('0 items') }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden"
                    id="selectedItemsTable">
                    <thead class="bg-slate-50">
                        <tr class="text-sm text-slate-700">
                            <th class="p-3 border-b">{{ __('#') }}</th>
                            <th class="p-3 border-b">{{ __('Item') }}</th>
                            <th class="p-3 border-b">{{ __('Unit') }}</th>
                            <th class="p-3 border-b">{{ __('Requested quantity') }}</th>
                            <th class="p-3 border-b">{{ __('Delete') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800"></tbody>
                </table>
            </div>

            <p id="emptyHint" class="mt-4 text-sm text-gray-500 text-center">{{ __('No item selected yet.') }}</p>
        </div>

        {{-- Note --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('Note (optional)') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Message to admin') }}</span>
            </div>

            <textarea id="user_note" rows="3"
                class="w-full border rounded-lg p-3 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                placeholder="{{ __('Write any note...') }}">{{ old('user_note') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="text-center">
            <form id="requestForm" method="POST" action="{{ route('custody_requests.store') }}">
                @csrf
                <input type="hidden" name="date_from" id="hidden_date_from">
                <input type="hidden" name="date_to" id="hidden_date_to">
                <input type="hidden" name="user_note" id="hidden_user_note">
                <input type="hidden" name="qetaa_id" id="hidden_qetaa_id">
                <input type="hidden" name="event_type_id" id="hidden_event_type_id">

                <div id="itemsHiddenContainer"></div>

                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                       bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                    {{ __('Send request') }}
                </button>

                <p class="mt-3 text-xs text-gray-500">{{ __('The request will be submitted with status') }} <span class="font-bold">{{ __('Pending review') }}</span>.
                </p>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inventory = @json($inventory);

            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            const sameDay = document.getElementById('same_day');

            const qetaaSelect = document.getElementById('qetaa_id');
            const eventTypeSelect = document.getElementById('event_type_id');

            const searchInput = document.getElementById('itemSearch');
            const searchResults = document.getElementById('searchResults');

            const selectedTableBody = document.querySelector('#selectedItemsTable tbody');
            const itemsCountEl = document.getElementById('itemsCount');
            const emptyHintEl = document.getElementById('emptyHint');

            const requestForm = document.getElementById('requestForm');
            const itemsHiddenContainer = document.getElementById('itemsHiddenContainer');

            const hiddenDateFrom = document.getElementById('hidden_date_from');
            const hiddenDateTo = document.getElementById('hidden_date_to');
            const userNote = document.getElementById('user_note');
            const hiddenUserNote = document.getElementById('hidden_user_note');

            const hiddenQetaa = document.getElementById('hidden_qetaa_id');
            const hiddenEventType = document.getElementById('hidden_event_type_id');

            let selectedItems = [];

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
                itemsCountEl.textContent = @json(__(':count item(s)')).replace(':count', selectedItems.length);
                emptyHintEl.classList.toggle('hidden', selectedItems.length > 0);
            }

            // Same day logic
            sameDay.addEventListener('change', function() {
                if (sameDay.checked) {
                    dateTo.value = dateFrom.value;
                    dateTo.setAttribute('disabled', 'disabled');
                } else {
                    dateTo.removeAttribute('disabled');
                }
            });

            dateFrom.addEventListener('change', function() {
                if (sameDay.checked) dateTo.value = dateFrom.value;
            });

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
                        const qtyHint = (item.ItemQuantity === null || item.ItemQuantity ===
                            undefined) ? '' : ` • {{ __('Available:') }} ${item.ItemQuantity}`;

                        const row = document.createElement('button');
                        row.type = 'button';
                        row.className =
                            'w-full text-right p-3 hover:bg-slate-50 transition flex items-center justify-between';

                        row.innerHTML = `
                    <div>
                        <div class="text-sm text-slate-800">${escapeHtml(item.ItemName)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(item.ItemMeasuringUnit || '')}${qtyHint}</div>
                    </div>
                    <div class="text-xs text-gray-400">{{ __('Add') }}</div>
                `;

                        row.addEventListener('click', function() {
                            const exists = selectedItems.find(i => i.inventory_id ==
                                item.InventoryID);
                            if (!exists) {
                                selectedItems.push({
                                    inventory_id: item.InventoryID,
                                    name: item.ItemName,
                                    unit: item.ItemMeasuringUnit,
                                    qty: 1
                                });
                                renderSelectedItems();
                            }
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

            function renderSelectedItems() {
                selectedTableBody.innerHTML = '';

                selectedItems.forEach((it, idx) => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b';
                    tr.innerHTML = `
                <td class="p-3">${idx + 1}</td>
                <td class="p-3 text-right">${escapeHtml(it.name)}</td>
                <td class="p-3">${escapeHtml(it.unit || '')}</td>
                <td class="p-3">
                    <input type="number"
                        class="w-24 h-10 border rounded-lg text-center border-slate-200 focus:border-blue-500 focus:outline-none"
                        min="1" value="${it.qty}" data-idx="${idx}">
                </td>
                <td class="p-3">
                    <button type="button"
                        class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition border border-red-200"
                        data-remove="${idx}">{{ __('Delete') }}</button>
                </td>
            `;
                    selectedTableBody.appendChild(tr);
                });

                selectedTableBody.querySelectorAll('input[type="number"]').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const i = parseInt(this.dataset.idx, 10);
                        let v = parseInt(this.value, 10);
                        if (!v || v < 1) v = 1;
                        this.value = v;
                        selectedItems[i].qty = v;
                    });
                });

                selectedTableBody.querySelectorAll('button[data-remove]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const i = parseInt(this.dataset.remove, 10);
                        selectedItems.splice(i, 1);
                        renderSelectedItems();
                    });
                });

                updateCounts();
            }

            updateCounts();

            requestForm.addEventListener('submit', function(e) {
                if (!dateFrom.value || !dateTo.value) {
                    e.preventDefault();
                    alert(@json(__('Please select dates (from / to).')));
                    return;
                }
                if (selectedItems.length === 0) {
                    e.preventDefault();
                    alert(@json(__('Please select at least one item.')));
                    return;
                }

                hiddenDateFrom.value = dateFrom.value;
                hiddenDateTo.value = dateTo.value;
                hiddenUserNote.value = userNote.value || '';

                hiddenQetaa.value = qetaaSelect.value || '';
                hiddenEventType.value = eventTypeSelect.value || '';

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
            });
        });
    </script>
@endsection
