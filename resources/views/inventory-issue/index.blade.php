@extends('layouts.app', ['pageTitle' => __('Print custody')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 text-center">
            <h1 class="mb-2 text-3xl font-bold text-gray-800">{{ __('Print custody') }}</h1>
            <p class="text-gray-600">{{ __('Choose season and event, add items and quantities, then print professionally') }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('1) Season and event') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Start here') }}</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Choose season') }}</label>
                    <select id="season_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">{{ __('-- Choose season --') }}</option>
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}">
                                {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Choose event') }}</label>
                    <select id="event_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                        disabled>
                        <option value="">{{ __('-- Choose event --') }}</option>
                    </select>
                    <p id="eventHelp" class="mt-2 text-xs text-gray-500">{{ __('Choose season first to show events') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('2) Add items') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Search then select') }}</span>
            </div>

            <div class="relative">
                <label class="block mb-2 text-sm text-gray-700">{{ __('Search for item') }}</label>
                <input type="text" id="itemSearch" placeholder="{{ __('Type item name...') }}"
                    class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                    autocomplete="off">

                <div id="searchResults"
                    class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden">
                </div>

                <p class="mt-2 text-xs text-gray-500">{{ __('Tip: type two or more characters for faster results') }}</p>
            </div>
        </div>

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
                            <th class="p-3 border-b">{{ __('Quantity') }}</th>
                            <th class="p-3 border-b">{{ __('Delete') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800"></tbody>
                </table>
            </div>

            <p id="emptyHint" class="mt-4 text-sm text-gray-500 text-center">{{ __('No item selected yet.') }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ __('4) Signature details') }}</h2>
                <span class="text-xs text-gray-500">{{ __('Will appear at the bottom of each page') }}</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="qetaa" class="block mb-2 text-sm text-gray-700">{{ __('Choose sector') }}</label>
                    <select id="qetaa" name="qetaa"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">{{ __('-- Choose sector --') }}</option>
                        @foreach ($qetaat as $qetaa)
                            <option value="{{ $qetaa->QetaaID }}">{{ $qetaa->QetaaName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="muslim" class="block mb-2 text-sm text-gray-700">{{ __('Issuer') }}</label>
                    <input type="text" id="muslim" name="muslim"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                        placeholder="{{ __('Enter issuer name') }}">
                </div>

                <div>
                    <label for="mustalem" class="block mb-2 text-sm text-gray-700">{{ __('Recipient') }}</label>
                    <input type="text" id="mustalem" name="mustalem"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                        placeholder="{{ __('Enter recipient name') }}">
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="button" id="generatePdf"
                class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">{{ __('Download / print PDF') }}</button>

            <p id="actionHint" class="mt-3 text-xs text-gray-500">{{ __('Make sure to choose an event and add items before printing.') }}</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seasonSelect = document.getElementById('season_id');
            const eventSelect = document.getElementById('event_id');
            const eventHelp = document.getElementById('eventHelp');

            const searchInput = document.getElementById('itemSearch');
            const searchResults = document.getElementById('searchResults');

            const selectedTableBody = document.querySelector('#selectedItemsTable tbody');
            const itemsCountEl = document.getElementById('itemsCount');
            const emptyHintEl = document.getElementById('emptyHint');

            const qetaaSelect = document.getElementById('qetaa');
            const muslimInput = document.getElementById('muslim');
            const mustalemInput = document.getElementById('mustalem');

            const generatePdfBtn = document.getElementById('generatePdf');
            const actionHint = document.getElementById('actionHint');

            const inventory = @json($inventory);
            const logoUrl = @json(asset('img/shamandora.webp'));
            const eventsUrl = @json(route('inventory-issue.getEventsForSeason'));

            let selectedItems = [];
            let searchTimer = null;
            let requestCounter = 0;

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, function(char) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    } [char];
                });
            }

            function setActionHint(message, tone = 'gray') {
                actionHint.textContent = message;
                actionHint.className = 'mt-3 text-xs';

                if (tone === 'red') {
                    actionHint.classList.add('text-red-500');
                } else if (tone === 'green') {
                    actionHint.classList.add('text-green-600');
                } else {
                    actionHint.classList.add('text-gray-500');
                }
            }

            function setSearchResultsVisible(visible) {
                searchResults.classList.toggle('hidden', !visible);
            }

            function resetEventSelect(message = @json(__('Choose season first to show events'))) {
                eventSelect.innerHTML = '<option value="">{{ __('-- Choose event --') }}</option>';
                eventSelect.disabled = true;
                eventHelp.textContent = message;
            }

            function getSelectedText(selectElement, fallback = '') {
                if (!selectElement || selectElement.selectedIndex < 0) return fallback;
                return selectElement.options[selectElement.selectedIndex]?.text?.trim() || fallback;
            }

            function normalizeMaxQty(value) {
                const parsed = parseInt(value, 10);
                return Number.isFinite(parsed) && parsed > 0 ? parsed : 9999;
            }

            function updateCounts() {
                itemsCountEl.textContent = @json(__(':count item(s)')).replace(':count', selectedItems.length);
                emptyHintEl.classList.toggle('hidden', selectedItems.length > 0);
            }

            function setPrintLoading(loading) {
                generatePdfBtn.disabled = loading;
                generatePdfBtn.classList.toggle('opacity-60', loading);
                generatePdfBtn.classList.toggle('cursor-not-allowed', loading);
                generatePdfBtn.textContent = loading ? @json(__('Preparing print...')) : @json(__('Download / print PDF'));
            }

            function renderSelectedItems() {
                selectedTableBody.innerHTML = '';

                if (!selectedItems.length) {
                    updateCounts();
                    return;
                }

                selectedItems.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.className = 'border-b';

                    row.innerHTML = `
                        <td class="p-3">${index + 1}</td>
                        <td class="p-3 text-right">${escapeHtml(item.name)}</td>
                        <td class="p-3">${escapeHtml(item.unit || '')}</td>
                        <td class="p-3">
                            <input
                                type="number"
                                min="1"
                                max="${item.max}"
                                value="${item.qty}"
                                data-idx="${index}"
                                class="w-24 h-10 border rounded-lg text-center border-slate-200 focus:border-blue-500 focus:outline-none"
                            >
                            <div class="text-[11px] text-gray-500 mt-1">${@json(__('Max:'))} ${item.max}</div>
                        </td>
                        <td class="p-3">
                            <button
                                type="button"
                                data-remove="${index}"
                                class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                            >{{ __('Delete') }}</button>
                        </td>
                    `;

                    selectedTableBody.appendChild(row);
                });

                selectedTableBody.querySelectorAll('input[type="number"]').forEach(input => {
                    input.addEventListener('input', function() {
                        const index = parseInt(this.dataset.idx, 10);
                        let value = parseInt(this.value, 10);

                        if (!Number.isFinite(value) || value < 1) value = 1;
                        if (value > selectedItems[index].max) value = selectedItems[index].max;

                        this.value = value;
                        selectedItems[index].qty = value;
                    });
                });

                selectedTableBody.querySelectorAll('button[data-remove]').forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.remove, 10);
                        selectedItems.splice(index, 1);
                        renderSelectedItems();
                    });
                });

                updateCounts();
            }

            function addItem(item) {
                const exists = selectedItems.find(selected => String(selected.id) === String(item.InventoryID));
                if (exists) {
                    setActionHint(@json(__('This item is already added.')), 'red');
                    return;
                }

                selectedItems.push({
                    id: item.InventoryID,
                    name: item.ItemName,
                    unit: item.ItemMeasuringUnit || '',
                    qty: 1,
                    max: normalizeMaxQty(item.ItemQuantity)
                });

                renderSelectedItems();
                setActionHint(@json(__('Item added successfully.')), 'green');
            }

            function renderSearchResults(items) {
                searchResults.innerHTML = '';

                if (!items.length) {
                    searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">{{ __('No results') }}</div>';
                    setSearchResultsVisible(true);
                    return;
                }

                items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className =
                        'w-full text-right p-3 hover:bg-slate-50 transition flex items-center justify-between';

                    button.innerHTML = `
                        <div class="text-sm text-slate-800">${escapeHtml(item.ItemName)}</div>
                        <div class="text-xs text-gray-500">${@json(__('Maximum:'))} ${normalizeMaxQty(item.ItemQuantity)}</div>
                    `;

                    button.addEventListener('click', function() {
                        addItem(item);
                        searchInput.value = '';
                        searchResults.innerHTML = '';
                        setSearchResultsVisible(false);
                    });

                    searchResults.appendChild(button);
                });

                setSearchResultsVisible(true);
            }

            async function loadEventsForSeason() {
                const seasonId = seasonSelect.value;
                const currentRequest = ++requestCounter;

                if (!seasonId) {
                    resetEventSelect();
                    return;
                }

                eventSelect.innerHTML = '<option value="">{{ __('Loading...') }}</option>';
                eventSelect.disabled = true;
                eventHelp.textContent = @json(__('Loading events...'));

                try {
                    const response = await fetch(`${eventsUrl}?seasonID=${encodeURIComponent(seasonId)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load events');
                    }

                    const payload = await response.json();

                    if (currentRequest !== requestCounter) return;

                    const events = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload
                        .data : []);

                    eventSelect.innerHTML = '<option value="">{{ __('-- Choose event --') }}</option>';

                    if (!events.length) {
                        eventSelect.disabled = true;
                        eventHelp.textContent = @json(__('No events for this season'));
                        return;
                    }

                    events.forEach(event => {
                        const option = document.createElement('option');
                        option.value = event.EventID ?? event.id ?? '';
                        option.textContent = event.EventName ?? event.name ?? @json(__('Event'));
                        eventSelect.appendChild(option);
                    });

                    eventSelect.disabled = false;
                    eventHelp.textContent = @json(__('Events loaded successfully'));
                } catch (error) {
                    if (currentRequest !== requestCounter) return;
                    resetEventSelect(@json(__('Error loading events')));
                }
            }

            function chunkItems(items, chunkSize) {
                const pages = [];
                for (let i = 0; i < items.length; i += chunkSize) {
                    pages.push(items.slice(i, i + chunkSize));
                }
                return pages;
            }

            function generateTableRows(items, startIndex) {
                return items.map((item, index) => `
                    <tr>
                        <td>${startIndex + index + 1}</td>
                        <td class="item-name">${escapeHtml(item.name)}</td>
                        <td>${item.qty}</td>
                        <td>${escapeHtml(item.unit || '')}</td>
                    </tr>
                `).join('');
            }

            function buildPrintHtml({
                eventName,
                qetaaName,
                muslim,
                mustalem,
                pages
            }) {
                return `
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>${@json(__('Print custody'))} - ${escapeHtml(eventName)}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            font-family: Arial, Tahoma, sans-serif;
            color: #111827;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-page {
            position: relative;
            min-height: 272mm;
            display: flex;
            flex-direction: column;
            page-break-after: always;
            overflow: hidden;
            background: #fff;
        }

        .print-page:last-child {
            page-break-after: auto;
        }

        .page-watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 0;
        }

        .page-watermark::before {
            content: "";
            width: 15.5cm;
            height: 15.5cm;
            background: url("${logoUrl}") center center / contain no-repeat;
            opacity: 0.10;
            filter: grayscale(100%);
        }

        .page-inner {
            position: relative;
            z-index: 2;
            min-height: 272mm;
            display: flex;
            flex-direction: column;
        }

        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .header-logo img {
            height: 100px;
            object-fit: contain;
        }

        .header-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            align-items: center;
            font-size: 18px;
            font-weight: 700;
        }

        .header-team {
            text-align: right;
        }

        .header-title {
            text-align: center;
        }

        .header-date {
            text-align: left;
        }

        .content {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .table-wrap {
            position: relative;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 17px;
            background: transparent;
        }

        thead th {
            border: 1px solid #000;
            text-align: center;
            padding: 8px 6px;
            background: rgba(245, 245, 245, 0.92);
            font-weight: 700;
        }

        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 7px 6px;
            background-color: rgba(255, 255, 255, 0.22);
        }

        .item-name {
            font-weight: 600;
        }

                .footer {
                margin-top: auto;
                border-top: 2px solid #000;
                padding-top: 14px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                align-items: end;
                gap: 70px;
                position: relative;
                z-index: 2;
            }

.signature-box {
    width: 100%;
    max-width: 260px;
    line-height: 2.1;
    font-size: 16px;
}

.signature-box.right-box {
    justify-self: end;
    text-align: center;
}

.signature-box.left-box {
    justify-self: start;
    text-align: center;
}

.signature-title {
    font-weight: 700;
    margin-bottom: 4px;
}

.signature-name {
    display: inline-block;
    border-bottom: 1px solid #000;
    width: 220px;
    min-height: 24px;
    margin: 4px 0 6px;
    padding: 0 6px 2px;
    text-align: center;
}
        .page-counter {
            margin-top: 8px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    ${pages.map((pageItems, pageIndex) => `
                                                    <section class="print-page">
                                                        <div class="page-watermark" aria-hidden="true"></div>

                                                        <div class="page-inner">
                                                            <div class="header">
                                                                <div class="header-logo">
                                                                    <img src="${logoUrl}" alt="Logo" onerror="this.style.display='none'">
                                                                </div>

                                                                <div class="header-info">
                                                                    <div class="header-team">${@json(__('Team:'))} ${escapeHtml(qetaaName)}</div>
                                                                    <div class="header-title">${@json(__('Custody'))} ${escapeHtml(eventName)}</div>
                                                                    <div class="header-date">${@json(__('Date:'))} &nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;</div>
                                                                </div>
                                                            </div>

                                                            <div class="content">
                                                                <div class="table-wrap">
                                                                    <table>
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width: 10%;">${@json(__('#'))}</th>
                                                                                <th style="width: 50%;">{{ __('Item') }}</th>
                                                                                <th style="width: 20%;">{{ __('Quantity') }}</th>
                                                                                <th style="width: 20%;">{{ __('Unit') }}</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            ${generateTableRows(pageItems, pageIndex * 18)}
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>

                                                         <div class="footer">
                                        <div class="signature-box right-box">
                                            <div class="signature-title">{{ __('Issuer') }}</div>
                                            <div><span class="signature-name">${escapeHtml(muslim)}</span></div>
                                            <div>${@json(__('Signature: ....................................'))}</div>
                                        </div>

                                        <div class="signature-box left-box">
                                            <div class="signature-title">{{ __('Recipient') }}</div>
                                            <div><span class="signature-name">${escapeHtml(mustalem)}</span></div>
                                            <div>${@json(__('Signature: ....................................'))}</div>
                                        </div>
                                    </div>

                                                            <div class="page-counter">
                                                                ${@json(__('Page :current of :total')).replace(':current', pageIndex + 1).replace(':total', pages.length)}
                                                            </div>
                                                        </div>
                                                    </section>
                                                `).join('')}
</body>
</html>
                `;
            }

            async function waitForPrintAssets(frameWindow) {
                const doc = frameWindow.document;
                const images = Array.from(doc.images || []);

                const imagePromises = images.map(img => {
                    if (img.complete) return Promise.resolve();
                    return new Promise(resolve => {
                        img.onload = resolve;
                        img.onerror = resolve;
                    });
                });

                const fontsPromise = doc.fonts ? doc.fonts.ready.catch(() => {}) : Promise.resolve();
                await Promise.all([...imagePromises, fontsPromise]);
            }

            async function printHtml(html) {
                const iframe = document.createElement('iframe');
                iframe.style.position = 'fixed';
                iframe.style.left = '-9999px';
                iframe.style.top = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.opacity = '0';
                iframe.setAttribute('aria-hidden', 'true');

                document.body.appendChild(iframe);

                const frameWindow = iframe.contentWindow;
                const frameDoc = frameWindow.document;

                frameDoc.open();
                frameDoc.write(html);
                frameDoc.close();

                await new Promise(resolve => setTimeout(resolve, 300));
                await waitForPrintAssets(frameWindow);

                frameWindow.focus();
                frameWindow.print();

                setTimeout(() => {
                    iframe.remove();
                }, 1200);
            }

            seasonSelect.addEventListener('change', loadEventsForSeason);

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(() => {
                    const query = searchInput.value.trim().toLowerCase();

                    if (query.length < 2) {
                        searchResults.innerHTML = '';
                        setSearchResultsVisible(false);
                        return;
                    }

                    const matches = (Array.isArray(inventory) ? inventory : [])
                        .filter(item => String(item.ItemName || '').toLowerCase().includes(query))
                        .slice(0, 20);

                    renderSearchResults(matches);
                }, 120);
            });

            document.addEventListener('click', function(event) {
                const insideSearch = searchResults.contains(event.target) || searchInput.contains(event
                    .target);
                if (!insideSearch) {
                    setSearchResultsVisible(false);
                }
            });

            generatePdfBtn.addEventListener('click', async function() {
                if (!eventSelect.value) {
                    setActionHint(@json(__('Please choose the event first.')), 'red');
                    alert(@json(__('Please choose the event first.')));
                    return;
                }

                if (selectedItems.length === 0) {
                    setActionHint(@json(__('Please add at least one item before printing.')), 'red');
                    alert(@json(__('Please add at least one item before printing.')));
                    return;
                }

                const eventName = getSelectedText(eventSelect, @json(__('Event')));
                const qetaaName = getSelectedText(qetaaSelect, '...........');
                const muslim = muslimInput.value.trim();
                const mustalem = mustalemInput.value.trim();

                const pages = chunkItems(selectedItems, 18);
                const html = buildPrintHtml({
                    eventName,
                    qetaaName,
                    muslim,
                    mustalem,
                    pages
                });

                try {
                    setPrintLoading(true);
                    setActionHint(@json(__('Preparing print...')), 'green');
                    await printHtml(html);
                    setActionHint(@json(__('Print prepared successfully.')), 'green');
                } catch (error) {
                    console.error(error);
                    setActionHint(@json(__('An error occurred while preparing print.')), 'red');
                    alert(@json(__('An error occurred while preparing print.')));
                } finally {
                    setPrintLoading(false);
                }
            });

            updateCounts();
            resetEventSelect();
        });
    </script>
@endsection
