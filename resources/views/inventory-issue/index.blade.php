@extends('layouts.app', ['pageTitle' => 'طباعة عهدة'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">طباعة عهدة</h1>
            <p class="text-gray-600">اختر الموسم والفعالية ثم أضف الأصناف والكميات، وبعدها اطبع</p>
        </div>

        {{-- Step 1: Season & Event --}}
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

        {{-- Step 2: Search Inventory --}}
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

                {{-- Dropdown Results --}}
                <div id="searchResults"
                    class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto hidden">
                </div>

                <p class="mt-2 text-xs text-gray-500">نصيحة: اكتب 2-3 حروف لنتائج أسرع</p>
            </div>
        </div>

        {{-- Step 3: Selected Items --}}
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

        {{-- Step 4: Signatures --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">٤) بيانات التوقيع</h2>
                <span class="text-xs text-gray-500">ستظهر في أسفل الورقة</span>
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

        {{-- Actions --}}
        <div class="text-center">
            <button type="button" id="generatePdf"
                class="inline-flex items-center justify-center h-12 px-10 text-sm font-medium rounded-full
                   bg-green-50 text-green-700 hover:bg-green-100 transition border border-green-200">
                تحميل / طباعة PDF
            </button>

            <p id="actionHint" class="mt-3 text-xs text-gray-500">
                تأكد من اختيار فعالية وإضافة أصناف قبل الطباعة.
            </p>
        </div>

        {{-- Hidden HTML for Print --}}
        <div id="htmlOutput" style="display:none;"></div>
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

            const htmlOutput = document.getElementById('htmlOutput');

            const inventory = @json($inventory);

            let selectedItems = [];

            // ---------- Helpers ----------
            function setResultsVisible(visible) {
                searchResults.classList.toggle('hidden', !visible);
            }

            function updateCounts() {
                itemsCountEl.textContent = `${selectedItems.length} صنف`;
                emptyHintEl.classList.toggle('hidden', selectedItems.length > 0);
            }

            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [s]));
            }

            // ---------- Load events when season changes ----------
            seasonSelect.addEventListener('change', function() {
                const seasonId = seasonSelect.value;

                eventSelect.innerHTML = '<option value="">جاري التحميل...</option>';
                eventSelect.disabled = true;

                if (!seasonId) {
                    eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                    eventHelp.textContent = 'اختر الموسم أولاً لعرض الفعاليات';
                    return;
                }

                fetch('{{ route('inventory-issue.getEventsForSeason') }}?seasonID=' + seasonId)
                    .then(res => res.json())
                    .then(events => {
                        eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                        events.forEach(ev => {
                            const opt = document.createElement('option');
                            opt.value = ev.EventID;
                            opt.textContent = ev.EventName;
                            eventSelect.appendChild(opt);
                        });
                        eventSelect.disabled = false;
                        eventHelp.textContent = events.length ? 'تم تحميل الفعاليات' :
                            'لا توجد فعاليات لهذا الموسم';
                    })
                    .catch(() => {
                        eventSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
                        eventHelp.textContent = 'حدث خطأ أثناء تحميل الفعاليات';
                    });
            });

            // ---------- Search items ----------
            let searchTimer = null;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(() => {
                    const query = searchInput.value.toLowerCase().trim();
                    searchResults.innerHTML = '';

                    if (!query || query.length < 2) {
                        setResultsVisible(false);
                        return;
                    }

                    const matches = inventory
                        .filter(item => (item.ItemName || '').toLowerCase().includes(query))
                        .slice(0, 20); // limit results

                    if (!matches.length) {
                        searchResults.innerHTML =
                            `<div class="p-3 text-sm text-gray-500">لا توجد نتائج</div>`;
                        setResultsVisible(true);
                        return;
                    }

                    matches.forEach(item => {
                        const maxQty = item.ItemQuantity || 10;

                        const row = document.createElement('button');
                        row.type = 'button';
                        row.className =
                            'w-full text-right p-3 hover:bg-slate-50 transition flex items-center justify-between';
                        row.innerHTML = `
                    <div class="text-sm text-slate-800">${escapeHtml(item.ItemName)}</div>
                    <div class="text-xs text-gray-500">الحد الأقصى: ${maxQty}</div>
                `;

                        row.addEventListener('click', function() {
                            const exists = selectedItems.find(i => i.id == item
                                .InventoryID);
                            if (!exists) {
                                selectedItems.push({
                                    id: item.InventoryID,
                                    name: item.ItemName,
                                    unit: item.ItemMeasuringUnit,
                                    qty: 1,
                                    max: maxQty
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

            // Close results on outside click
            document.addEventListener('click', function(e) {
                const inside = searchResults.contains(e.target) || searchInput.contains(e.target);
                if (!inside) setResultsVisible(false);
            });

            // ---------- Render selected items ----------
            function renderSelectedItems() {
                selectedTableBody.innerHTML = '';

                selectedItems.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b';

                    tr.innerHTML = `
                <td class="p-3">${idx + 1}</td>
                <td class="p-3 text-right">${escapeHtml(item.name)}</td>
                <td class="p-3">${escapeHtml(item.unit || '')}</td>
                <td class="p-3">
                    <input type="number"
                           class="w-24 h-10 border rounded-lg text-center border-slate-200 focus:border-blue-500 focus:outline-none"
                           min="1" max="${item.max}" value="${item.qty}" data-idx="${idx}">
                    <div class="text-[11px] text-gray-500 mt-1">حد أقصى: ${item.max}</div>
                </td>
                <td class="p-3">
                    <button type="button"
                            class="px-3 py-2 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                            data-remove="${idx}">
                        حذف
                    </button>
                </td>
            `;

                    selectedTableBody.appendChild(tr);
                });

                // Quantity handlers
                selectedTableBody.querySelectorAll('input[type="number"]').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const i = parseInt(this.dataset.idx, 10);
                        let val = parseInt(this.value, 10);
                        if (!val || val < 1) val = 1;
                        if (val > selectedItems[i].max) val = selectedItems[i].max;
                        this.value = val;
                        selectedItems[i].qty = val;
                    });
                });

                // Remove handlers
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

            // ---------- Print / Generate ----------
            document.getElementById('generatePdf').addEventListener('click', function() {
                // Basic validations
                if (!eventSelect.value) {
                    alert('من فضلك اختر الفعالية أولاً.');
                    return;
                }
                if (selectedItems.length === 0) {
                    alert('من فضلك أضف صنف واحد على الأقل قبل الطباعة.');
                    return;
                }

                const selectedEvent = eventSelect.options[eventSelect.selectedIndex];
                const eventName = selectedEvent ? selectedEvent.text : 'الفعالية';

                const qetaaSelect = document.getElementById('qetaa');
                const qetaaName = qetaaSelect.options[qetaaSelect.selectedIndex]?.text || '...........';

                const muslim = document.getElementById('muslim').value || '';
                const mustalem = document.getElementById('mustalem').value || '';

                const maxItemsPerPage = 15;
                const totalItems = selectedItems.length;
                const totalPages = Math.ceil(totalItems / maxItemsPerPage);

                // Build HTML
                let html = `
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        @page { 
            margin: 0; 
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .page {
            width: 21cm;
            height: 29.7cm;
            padding: 1.5cm;
            position: relative;
            page-break-after: always;
            background: white;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
        
        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header-logo {
            margin-bottom: 25px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        
        .header-logo img {
            height: 140px;
            max-width: 100%;
        }
        
        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            font-size: 22px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .header-team {
            flex: 1;
            text-align: left;
        }
        
        .header-title {
            flex: 1;
            text-align: center;
            margin: 0 90px;
        }
        
        .header-date {
            flex: 1;
            text-align: right;
        }
        
        .bg-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            opacity: 0.08;
            filter: grayscale(100%);
            z-index: 1;
            pointer-events: none;
        }
        
        .content {
            position: relative;
            z-index: 5;
            min-height: 16cm;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 20px;
            background: white;
        }
        
        table th {
            border: 1px solid #000;
            text-align: center;
            padding: 8px;
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 8px;
        }
        
        .footer {
            position: absolute;
            bottom: 1.5cm;
            left: 1.5cm;
            right: 1.5cm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 2px solid #000;
            padding-top: 10px;
            z-index: 10;
        }
        
        .signature-box {
            text-align: right;
            line-height: 2;
        }
        
        .signature-name {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 200px;
            height: 25px;
            margin: 5px 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .page {
                margin: 0;
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
`;

                function generateTable(items, startIndex) {
                    let table = `
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">م</th>
                <th style="width: 50%;">الصنف</th>
                <th style="width: 20%;">الكمية</th>
                <th style="width: 20%;">الوحدة</th>
            </tr>
        </thead>
        <tbody>`;

                    items.forEach((item, index) => {
                        const counter = startIndex + index + 1;
                        table += `
            <tr>
                <td>${counter}</td>
                <td>${escapeHtml(item.name)}</td>
                <td>${item.qty}</td>
                <td>${escapeHtml(item.unit || '')}</td>
            </tr>`;
                    });

                    table += `
        </tbody>
    </table>`;
                    return table;
                }

                // Generate pages
                for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
                    const startIdx = pageIndex * maxItemsPerPage;
                    const endIdx = Math.min(startIdx + maxItemsPerPage, totalItems);
                    const pageItems = selectedItems.slice(startIdx, endIdx);

                    html += `<div class="page">`;

                    // Header
                    html += `
    <div class="header">
        <div class="header-logo">
            <img src="{{ asset('img/shamandora.png') }}" alt="Logo" onerror="this.style.display='none'">
        </div>
        <div class="header-info">
            <div class="header-team">الفريق: ${escapeHtml(qetaaName)}</div>
            <div class="header-title">عهدة ${escapeHtml(eventName)}</div>
            <div class="header-date">التاريخ: &nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;</div>
        </div>
    </div>`;

                    // Background overlay
                    html += `<img src="{{ asset('img/shamandora.png') }}" class="bg-overlay" alt="">`;

                    // Content
                    html += `<div class="content">`;
                    html += generateTable(pageItems, startIdx);
                    html += `</div>`;

                    // Footer with signatures
                    html += `
    <div class="footer">
        <div class="signature-box">
            المسلم<br>
            <span class="signature-name">${escapeHtml(muslim)}</span><br>
            التوقيع: ....................................
        </div>
        <div class="signature-box">
            المستلم<br>
            <span class="signature-name">${escapeHtml(mustalem)}</span><br>
            التوقيع: ....................................
        </div>
    </div>`;

                    html += `</div>`;
                }

                html += `
</body>
</html>`;

                // Create a new window for printing
                const printWindow = window.open('', '_blank', 'width=800,height=600');

                if (!printWindow) {
                    alert('يرجى السماح بالنوافذ المنبثقة لطباعة المستند');
                    return;
                }

                printWindow.document.write(html);
                printWindow.document.close();

                // Wait for images to load before printing
                printWindow.onload = function() {
                    setTimeout(() => {
                        printWindow.focus();
                        printWindow.print();

                        // Close the print window after printing (optional)
                        // Uncomment the next line if you want to auto-close
                        // printWindow.close();
                    }, 250);
                };
            });
        });
        
    </script>
@endsection
