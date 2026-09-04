{{-- Price interval rows (date range × audience × price). Exposes window.FinanceIntervals. --}}
<div class="border rounded-lg p-4 bg-gray-50">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-gray-800">{{ __('Price intervals') }}</h3>
        <button type="button" id="add-interval-btn"
            class="{{ $addButtonClass ?? 'bg-blue-600 hover:bg-blue-700' }} text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">{{ __('Add price interval') }}</button>
    </div>

    <div id="intervals-container" class="space-y-4"></div>

    <p class="text-xs text-gray-500 mt-4">
        {{ __('Note: if the last interval ends before the event starts, remaining days are filled automatically with the last price.') }}
    </p>
    <p class="text-xs text-gray-500 mt-1">
        {{ __('Every sector linked to the event must have a price. Families and guests are optional; without a price they cannot book.') }}
    </p>
</div>

<script>
    window.FinanceIntervals = (function() {
        const container = document.getElementById('intervals-container');
        const addBtn = document.getElementById('add-interval-btn');
        const L = {
            from: @json(__('From date')),
            to: @json(__('To date')),
            price: @json(__('Price')),
            remove: @json(__('Delete interval')),
            duplicate: @json(__('Duplicate interval')),
            appliesTo: @json(__('Applies to')),
            selectAll: @json(__('Select all')),
            families: @json(__('Families')),
            guests: @json(__('Guests')),
            noSectors: @json(__('Choose an event to load its sectors.')),
        };
        const FIXED = [{ key: 'FAMILY', label: L.families }, { key: 'GUEST', label: L.guests }];
        const INPUT_CLASS =
            'w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none';
        const CHIP_ON = ['bg-blue-50', 'border-blue-400', 'text-blue-700'];

        let sectors = [];
        let nextIndex = 0;

        function options() {
            return sectors
                .map(s => ({ key: 'Q:' + s.QetaaID, label: String(s.QetaaName ?? '') }))
                .concat(FIXED);
        }

        function el(tag, className, text) {
            const node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined) node.textContent = text;
            return node;
        }

        function button(className, text) {
            const node = el('button', className, text);
            node.type = 'button';
            return node;
        }

        function field(labelText, inputNode) {
            const wrap = el('div');
            wrap.append(el('label', 'block mb-2 text-sm text-gray-700', labelText), inputNode);
            return wrap;
        }

        function input(type, name, value) {
            const node = document.createElement('input');
            node.type = type;
            node.name = name;
            node.value = value ?? '';
            node.required = true;
            node.className = INPUT_CLASS;
            if (type === 'number') {
                node.step = '1';
                node.min = '0';
            }
            return node;
        }

        function checkedKeys(row) {
            return Array.from(row.querySelectorAll('.audience-chips input:checked')).map(cb => cb.value);
        }

        function paintChip(label, checked) {
            CHIP_ON.forEach(cls => label.classList.toggle(cls, checked));
        }

        // row._wanted holds the audience keys the row should have checked, including sector keys
        // that arrived from old()/edit data before the event's sectors were loaded.
        function renderChips(row) {
            const chips = row.querySelector('.audience-chips');
            const hint = row.querySelector('.no-sectors-hint');
            const selectAll = row.dataset.selectAll === '1';

            chips.replaceChildren();
            options().forEach(opt => {
                if (selectAll) row._wanted.add(opt.key);

                const label = el('label',
                    'inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-gray-700 cursor-pointer select-none');
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.name = `intervals[${row.dataset.index}][audience][]`;
                cb.value = opt.key;
                cb.checked = row._wanted.has(opt.key);
                cb.className = 'w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500';
                cb.addEventListener('change', function() {
                    row.dataset.selectAll = '0';
                    cb.checked ? row._wanted.add(opt.key) : row._wanted.delete(opt.key);
                    paintChip(label, cb.checked);
                });
                label.append(cb, el('span', '', opt.label));
                paintChip(label, cb.checked);
                chips.appendChild(label);
            });

            hint.classList.toggle('hidden', sectors.length > 0);
        }

        function rowValues(row) {
            return {
                start_date: row.querySelector('input[name$="[start_date]"]').value,
                end_date: row.querySelector('input[name$="[end_date]"]').value,
                price: row.querySelector('input[name$="[price]"]').value,
                audience: checkedKeys(row),
            };
        }

        function addRow(data = {}) {
            const index = nextIndex++;
            const row = el('div', 'border rounded-lg p-4 bg-white interval-row space-y-4');
            row.dataset.index = String(index);
            // A brand-new row applies to everyone until the user unticks something.
            row.dataset.selectAll = Array.isArray(data.audience) ? '0' : '1';
            row._wanted = new Set(Array.isArray(data.audience) ? data.audience.map(String) : []);

            const grid = el('div', 'grid grid-cols-1 md:grid-cols-4 gap-4');
            grid.append(
                field(L.from, input('date', `intervals[${index}][start_date]`, data.start_date)),
                field(L.to, input('date', `intervals[${index}][end_date]`, data.end_date)),
                field(L.price, input('number', `intervals[${index}][price]`, data.price)),
            );

            const actions = el('div', 'flex items-end gap-2');
            const duplicateBtn = button(
                'flex-1 inline-flex items-center justify-center h-12 px-3 text-sm font-medium rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition',
                L.duplicate);
            const removeBtn = button(
                'flex-1 inline-flex items-center justify-center h-12 px-3 text-sm font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition',
                L.remove);
            actions.append(duplicateBtn, removeBtn);
            grid.appendChild(actions);

            const audience = el('div');
            const head = el('div', 'flex items-center justify-between mb-2');
            const selectAllBtn = button('text-xs font-medium text-blue-600 hover:underline', L.selectAll);
            head.append(el('span', 'text-sm text-gray-700', L.appliesTo), selectAllBtn);
            audience.append(
                head,
                el('div', 'flex flex-wrap gap-2 audience-chips'),
                el('p', 'text-xs text-gray-500 mt-2 hidden no-sectors-hint', L.noSectors),
            );

            row.append(grid, audience);
            container.appendChild(row);
            renderChips(row);

            selectAllBtn.addEventListener('click', function() {
                const boxes = Array.from(row.querySelectorAll('.audience-chips input'));
                const turnOn = !boxes.every(cb => cb.checked);
                boxes.forEach(cb => {
                    cb.checked = turnOn;
                    cb.dispatchEvent(new Event('change'));
                });
            });
            duplicateBtn.addEventListener('click', () => addRow(rowValues(row)));
            removeBtn.addEventListener('click', function() {
                row.remove();
                ensureOne();
            });

            return row;
        }

        function rows() {
            return Array.from(container.querySelectorAll('.interval-row'));
        }

        function ensureOne() {
            if (!rows().length) addRow();
        }

        function setSectors(list) {
            sectors = Array.isArray(list) ? list : [];
            rows().forEach(renderChips);
        }

        addBtn.addEventListener('click', () => addRow());

        return { addRow, setSectors, ensureOne };
    })();
</script>
