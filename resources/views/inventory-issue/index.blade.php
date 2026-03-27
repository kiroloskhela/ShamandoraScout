@extends('layouts.app', ['pageTitle' => 'طباعة عهدة'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="mb-8 text-center">
            <h1 class="mb-2 text-3xl font-bold text-gray-800">طباعة عهدة</h1>
            <p class="text-gray-600">اختر الموسم والفعالية، أضف الأصناف والكميات، ثم اطبع بشكل احترافي</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-blue-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">١) الموسم والفعالية</h2>
                <span class="text-xs text-gray-500">ابدأ من هنا</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm text-gray-700">اختر الموسم</label>
                    <select id="season_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الموسم --</option>
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}">
                                {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-700">اختر الفعالية</label>
                    <select id="event_id"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                        disabled>
                        <option value="">-- اختر الفعالية --</option>
                    </select>
                    <p id="eventHelp" class="mt-2 text-xs text-gray-500">اختر الموسم أولاً لعرض الفعاليات</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-green-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">٢) إضافة الأصناف</h2>
                <span class="text-xs text-gray-500">ابحث ثم اختر</span>
            </div>

            <div class="relative">
                <label class="block mb-2 text-sm text-gray-700">ابحث عن الصنف</label>
                <input type="text" id="itemSearch" placeholder="اكتب اسم الصنف..."
                    class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none"
                    autocomplete="off">

                <div id="searchResults"
                    class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden">
                </div>

                <p class="mt-2 text-xs text-gray-500">نصيحة: اكتب حرفين أو أكثر لنتائج أسرع</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-yellow-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">٣) الأصناف المختارة</h2>
                <span id="itemsCount" class="text-xs text-gray-500">0 صنف</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-center border border-slate-200 rounded-lg overflow-hidden"
                    id="selectedItemsTable">
                    <thead class="bg-slate-50">
                        <tr class="text-sm text-slate-700">
                            <th class="p-3 border-b">م</th>
                            <th class="p-3 border-b">الصنف</th>
                            <th class="p-3 border-b">الوحدة</th>
                            <th class="p-3 border-b">الكمية</th>
                            <th class="p-3 border-b">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800"></tbody>
                </table>
            </div>

            <p id="emptyHint" class="mt-4 text-sm text-gray-500 text-center">
                لم يتم اختيار أي صنف بعد.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">٤) بيانات التوقيع</h2>
                <span class="text-xs text-gray-500">ستظهر في أسفل كل صفحة</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="qetaa" class="block mb-2 text-sm text-gray-700">اختر القطاع</label>
                    <select id="qetaa" name="qetaa"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر القطاع --</option>
                        @foreach ($qetaat as $qetaa)
                            <option value="{{ $qetaa->QetaaID }}">{{ $qetaa->QetaaName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="muslim" class="block mb-2 text-sm text-gray-700">المُسَلِّم</label>
                    <input type="text" id="muslim" name="muslim"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                        placeholder="اكتب اسم المُسَلِّم">
                </div>

                <div>
                    <label for="mustalem" class="block mb-2 text-sm text-gray-700">المُستَلِم</label>
                    <input type="text" id="mustalem" name="mustalem"
                        class="w-full h-12 border rounded-lg px-4 text-right border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none"
                        placeholder="اكتب اسم المُستَلِم">
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="button" id="generatePdf"
                class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                تحميل / طباعة PDF
            </button>

            <p id="actionHint" class="mt-3 text-xs text-gray-500">
                تأكد من اختيار فعالية وإضافة أصناف قبل الطباعة.
            </p>
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
            const logoUrl = @json(asset('img/shamandora.png'));
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

            function resetEventSelect(message = 'اختر الموسم أولاً لعرض الفعاليات') {
                eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
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
                itemsCountEl.textContent = `${selectedItems.length} صنف`;
                emptyHintEl.classList.toggle('hidden', selectedItems.length > 0);
            }

            function setPrintLoading(loading) {
                generatePdfBtn.disabled = loading;
                generatePdfBtn.classList.toggle('opacity-60', loading);
                generatePdfBtn.classList.toggle('cursor-not-allowed', loading);
                generatePdfBtn.textContent = loading ? 'جاري تجهيز الطباعة...' : 'تحميل / طباعة PDF';
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
                            <div class="text-[11px] text-gray-500 mt-1">حد أقصى: ${item.max}</div>
                        </td>
                        <td class="p-3">
                            <button
                                type="button"
                                data-remove="${index}"
                                class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                            >
                                حذف
                            </button>
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
                    setActionHint('هذا الصنف مضاف بالفعل.', 'red');
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
                setActionHint('تمت إضافة الصنف بنجاح.', 'green');
            }

            function renderSearchResults(items) {
                searchResults.innerHTML = '';

                if (!items.length) {
                    searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">لا توجد نتائج</div>';
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
                        <div class="text-xs text-gray-500">الحد الأقصى: ${normalizeMaxQty(item.ItemQuantity)}</div>
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

                eventSelect.innerHTML = '<option value="">جاري التحميل...</option>';
                eventSelect.disabled = true;
                eventHelp.textContent = 'جاري تحميل الفعاليات...';

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

                    eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';

                    if (!events.length) {
                        eventSelect.disabled = true;
                        eventHelp.textContent = 'لا توجد فعاليات لهذا الموسم';
                        return;
                    }

                    events.forEach(event => {
                        const option = document.createElement('option');
                        option.value = event.EventID ?? event.id ?? '';
                        option.textContent = event.EventName ?? event.name ?? 'فعالية';
                        eventSelect.appendChild(option);
                    });

                    eventSelect.disabled = false;
                    eventHelp.textContent = 'تم تحميل الفعاليات بنجاح';
                } catch (error) {
                    if (currentRequest !== requestCounter) return;
                    resetEventSelect('حدث خطأ أثناء تحميل الفعاليات');
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
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة عهدة - ${escapeHtml(eventName)}</title>
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
                                                                    <div class="header-team">الفريق: ${escapeHtml(qetaaName)}</div>
                                                                    <div class="header-title">عهدة ${escapeHtml(eventName)}</div>
                                                                    <div class="header-date">التاريخ: &nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;</div>
                                                                </div>
                                                            </div>

                                                            <div class="content">
                                                                <div class="table-wrap">
                                                                    <table>
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width: 10%;">م</th>
                                                                                <th style="width: 50%;">الصنف</th>
                                                                                <th style="width: 20%;">الكمية</th>
                                                                                <th style="width: 20%;">الوحدة</th>
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
                                            <div class="signature-title">المسلم</div>
                                            <div><span class="signature-name">${escapeHtml(muslim)}</span></div>
                                            <div>التوقيع: ....................................</div>
                                        </div>

                                        <div class="signature-box left-box">
                                            <div class="signature-title">المستلم</div>
                                            <div><span class="signature-name">${escapeHtml(mustalem)}</span></div>
                                            <div>التوقيع: ....................................</div>
                                        </div>
                                    </div>

                                                            <div class="page-counter">
                                                                صفحة ${pageIndex + 1} من ${pages.length}
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
                    setActionHint('من فضلك اختر الفعالية أولاً.', 'red');
                    alert('من فضلك اختر الفعالية أولاً.');
                    return;
                }

                if (selectedItems.length === 0) {
                    setActionHint('من فضلك أضف صنف واحد على الأقل قبل الطباعة.', 'red');
                    alert('من فضلك أضف صنف واحد على الأقل قبل الطباعة.');
                    return;
                }

                const eventName = getSelectedText(eventSelect, 'الفعالية');
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
                    setActionHint('جاري تجهيز الطباعة...', 'green');
                    await printHtml(html);
                    setActionHint('تم تجهيز الطباعة بنجاح.', 'green');
                } catch (error) {
                    console.error(error);
                    setActionHint('حدث خطأ أثناء تجهيز الطباعة.', 'red');
                    alert('حدث خطأ أثناء تجهيز الطباعة.');
                } finally {
                    setPrintLoading(false);
                }
            });

            updateCounts();
            resetEventSelect();
        });
    </script>
@endsection
