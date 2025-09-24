@extends('layouts.app', ['pageTitle' => "طلب صرف عهدة جديدة"])

@section('content')
<div class="container mx-auto px-4 py-8" dir="rtl">

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">طلب صرف عهدة</h1>
        <p class="text-gray-600">اختر الموسم والفعالية ثم أضف الأصناف والكميات</p>
    </div>

    <!-- Season & Event -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border-2 border-blue-300">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label>اختر الموسم</label>
                <select id="season_id" class="w-full border rounded p-2 text-right">
                    <option value="">-- اختر الموسم --</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->SeasonID }}">{{ $season->SeasonName }} ({{ $season->SeasonYear }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>اختر الفعالية</label>
                <select id="event_id" class="w-full border rounded p-2 text-right" disabled>
                    <option value="">-- اختر الفعالية --</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Searchable Inventory -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border-2 border-green-300">
        <h3>الأصناف</h3>
        <input type="text" id="itemSearch" placeholder="ابحث عن الصنف" class="w-full border rounded p-2 text-right mb-2" autocomplete="off">
        <div id="searchResults" class="bg-white shadow rounded max-h-48 overflow-y-auto"></div>
    </div>

    <!-- Selected Items -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border-2 border-yellow-300">
        <h3>الأصناف المختارة</h3>
        <table class="w-full text-center" id="selectedItemsTable">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الصنف</th>
                    <th>الوحدة</th>
                    <th>الكمية</th>
                    <th>حذف</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Signatures -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <!-- Dropdown for Qetaat -->
        <div class="md:col-span-2">
            <label for="qetaa" class="block mb-2">اختر القطاع</label>
            <select id="qetaa" name="qetaa" class="w-full border rounded p-2 text-right">
                <option value="">-- اختر القطاع --</option>
                @foreach($qetaat as $qetaa)
                    <option value="{{ $qetaa->QetaaID }}">{{ $qetaa->QetaaName }}</option>
                @endforeach
            </select>
        </div>

        <!-- Muslim -->
        <div>
            <label for="muslim">المُسَلِّم</label>
            <input type="text" id="muslim" name="muslim" class="w-full border rounded p-2 text-right">
        </div>

        <!-- Mustalem -->
        <div>
            <label for="mustalem">المُستَلِم</label>
            <input type="text" id="mustalem" name="mustalem" class="w-full border rounded p-2 text-right">
        </div>
    </div>

    <!-- Generate PDF -->
    <div class="text-center mb-6">
        <button type="button" id="generatePdf" class="px-8 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600">تحميل الـ PDF</button>
    </div>

    <!-- Hidden HTML for PDF -->
    <div id="htmlOutput" style="display:none;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seasonSelect = document.getElementById('season_id');
    const eventSelect = document.getElementById('event_id');
    const searchInput = document.getElementById('itemSearch');
    const searchResults = document.getElementById('searchResults');
    const selectedTableBody = document.querySelector('#selectedItemsTable tbody');
    const htmlOutput = document.getElementById('htmlOutput');
    let selectedItems = [];

    const inventory = @json($inventory);

    // Load events when season changes
    seasonSelect.addEventListener('change', function() {
        const seasonId = seasonSelect.value;
        eventSelect.innerHTML = '<option>جاري التحميل...</option>';
        eventSelect.disabled = true;
        
        if (!seasonId) {
            eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
            return;
        }

        fetch('{{ route("inventory-issue.getEventsForSeason") }}?seasonID=' + seasonId)
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
            })
            .catch(error => {
                console.error('Error:', error);
                eventSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
    });

    // Search items
    searchInput.addEventListener('input', function() {
        const query = searchInput.value.toLowerCase().trim();
        searchResults.innerHTML = '';
        if (!query) return;

        const matches = inventory.filter(item => item.ItemName.toLowerCase().includes(query));

        matches.forEach(item => {
            const div = document.createElement('div');
            div.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
            div.textContent = item.ItemName + ' (الحد الأقصى: ' + (item.ItemQuantity || 10) + ')';
            div.dataset.id = item.InventoryID;
            div.dataset.name = item.ItemName;
            div.dataset.unit = item.ItemMeasuringUnit;
            div.dataset.max = item.ItemQuantity || 10;
            searchResults.appendChild(div);

            div.addEventListener('click', function() {
                const exists = selectedItems.find(i => i.id == item.InventoryID);
                if (!exists) {
                    selectedItems.push({
                        id: item.InventoryID,
                        name: item.ItemName,
                        unit: item.ItemMeasuringUnit,
                        qty: 1,
                        max: item.ItemQuantity || 10
                    });
                }
                renderSelectedItems();
                searchResults.innerHTML = '';
                searchInput.value = '';
            });
        });
    });

    function renderSelectedItems() {
        selectedTableBody.innerHTML = '';
        selectedItems.forEach((item, idx) => {
            const tr = document.createElement('tr');

            // Index cell
            const tdIdx = document.createElement('td');
            tdIdx.textContent = idx + 1;
            tr.appendChild(tdIdx);

            // Name cell
            const tdName = document.createElement('td');
            tdName.textContent = item.name;
            tr.appendChild(tdName);

            // Unit cell
            const tdUnit = document.createElement('td');
            tdUnit.textContent = item.unit;
            tr.appendChild(tdUnit);

            // Quantity cell
            const tdQty = document.createElement('td');
            const inputQty = document.createElement('input');
            inputQty.type = 'number';
            inputQty.value = item.qty;
            inputQty.min = 1;
            inputQty.max = item.max;
            inputQty.className = 'w-20 text-center';
            inputQty.dataset.idx = idx;
            inputQty.addEventListener('input', function() {
                let val = parseInt(inputQty.value) || 1;
                if (val > item.max) val = item.max;
                if (val < 1) val = 1;
                inputQty.value = val;
                selectedItems[idx].qty = val;
            });
            tdQty.appendChild(inputQty);
            tr.appendChild(tdQty);

            // Remove button cell
            const tdRemove = document.createElement('td');
            const btnRemove = document.createElement('button');
            btnRemove.type = 'button';
            btnRemove.className = 'remove-item bg-red-500 text-white px-2 rounded';
            btnRemove.textContent = '❌';
            btnRemove.dataset.idx = idx;
            btnRemove.addEventListener('click', function() {
                selectedItems.splice(idx, 1);
                renderSelectedItems();
            });
            tdRemove.appendChild(btnRemove);
            tr.appendChild(tdRemove);

            selectedTableBody.appendChild(tr);
        });
    }

    document.getElementById('generatePdf').addEventListener('click', function() {
        const selectedEvent = eventSelect.options[eventSelect.selectedIndex];
        const eventName = selectedEvent ? selectedEvent.text : 'الفعالية';

        const qetaaSelect = document.getElementById('qetaa');
        const qetaaName = qetaaSelect.options[qetaaSelect.selectedIndex]?.text || '...........';

        const muslim = document.getElementById('muslim').value || '';
        const mustalem = document.getElementById('mustalem').value || '';

        const maxItemsPerPage = 15; // Items per page
        const totalItems = selectedItems.length;
        const totalPages = Math.ceil(totalItems / maxItemsPerPage);

        let html = `
        <style>
            @page { margin: 0; size: A4; }
            .page {
                font-family: Arial, sans-serif;
                width: 21cm;
                height: 29.7cm;
                padding: 1.5cm;
                box-sizing: border-box;
                margin: 0;
                background: #fff;
                position: relative;
                page-break-after: always;
            }
            .page:last-child { page-break-after: avoid; }
            .header {
                position: fixed;
                top: 1.5cm;
                left: 1.5cm;
                right: 1.5cm;
                height: 6cm;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 1cm;
                background: #fff;
                z-index: 10;
            }
            .footer {
                position: fixed;
                bottom: 1.5cm;
                left: 1.5cm;
                right: 1.5cm;
                height: 3cm;
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                border-top: 2px solid #000;
                padding-top: 10px;
                background: #fff;
                z-index: 10;
            }
            .content {
                margin-top: 7cm;
                margin-bottom: 4cm;
                position: relative;
                z-index: 5;
            }
            .bg-overlay {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 60%;
                height: auto;
                opacity: 0.1;
                z-index: 1;
                filter: grayscale(100%);
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 20px;
                background: transparent;
            }
            table th, table td {
                border: 1px solid #000;
                text-align: center;
                padding: 8px;
                background: transparent;
            }
        </style>
        `;

        for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
            const startIdx = pageIndex * maxItemsPerPage;
            const endIdx = Math.min(startIdx + maxItemsPerPage, totalItems);
            const pageItems = selectedItems.slice(startIdx, endIdx);

            html += `<div class="page">`;

            // ---------- HEADER ----------
            html += `
            <div class="header">
                <!-- Top Logo -->
                <div style="margin-bottom: 25px; width: 100%; display: flex; justify-content: center;">
                    <img src="{{ asset('img/shamandora.png') }}" style="height: 140px;" onerror="this.style.display='none'">
                </div>

                <!-- Info Row (Left - Center - Right) -->
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; font-size: 22px; font-weight: bold; margin: 20px 0;">
                    <div style="flex: 1; text-align: left;">الفريق: ${qetaaName}</div>
                    <div style="flex: 1; text-align: center; margin: 0 90px;">عهدة ${eventName}</div>
                    <div style="flex: 1; text-align: right;"> التاريخ :  &nbsp&nbsp&nbsp / &nbsp&nbsp&nbsp  /   &nbsp&nbsp&nbsp   </div>
                </div>
            </div>`;

            // ---------- BACKGROUND ----------
            html += `<img src="{{ asset('img/shamandora.png') }}" class="bg-overlay">`;

            // ---------- CONTENT ----------
            html += `<div class="content">`;
            html += generateTable(pageItems, startIdx);
            html += `</div>`;

            // ---------- FOOTER ----------
            html += `
            <div class="footer">
                <div style="text-align: right;">
                    المسلم<br>
                    <span style="display: inline-block; border-bottom: 1px solid #000; width: 200px; height: 25px;">${muslim}</span><br>
                    التوقيع: ....................................
                </div>
                <div style="text-align: right;">
                    المستلم<br>
                    <span style="display: inline-block; border-bottom: 1px solid #000; width: 200px; height: 25px;">${mustalem}</span><br>
                    التوقيع: ....................................
                </div>
            </div>`;

            html += `</div>`; // end page
        }

        htmlOutput.innerHTML = html;

        // Print
        const printContents = htmlOutput.innerHTML;
        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();

        // ---------- TABLE GENERATOR ----------
        function generateTable(items, startIndex) {
            let table = `
            <table>
                <thead>
                    <tr>
                        <th>م</th>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>الوحدة</th>
                    </tr>
                </thead>
                <tbody>`;

            items.forEach((item, index) => {
                const counter = startIndex + index + 1;
                table += `
                    <tr>
                        <td>${counter}</td>
                        <td>${item.name}</td>
                        <td>${item.qty}</td>
                        <td>${item.unit}</td>
                    </tr>`;
            });

            table += `</tbody></table>`;
            return table;
        }
    });

    renderSelectedItems();
});
</script>
@endsection
