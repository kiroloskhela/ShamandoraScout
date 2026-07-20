@extends('layouts.app', ['pageTitle' => __('Reserve medicine')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border-2 border-amber-300">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ __('Reserve medicine') }}</h1>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('medicine.locks.store') }}">
                @csrf

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 relative">
                        <label for="medicine_search" class="block mb-2 text-sm text-gray-700">{{ __('Medicine') }}</label>
                        <input type="text" id="medicine_search" autocomplete="off"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-amber-500 focus:outline-none"
                            placeholder="{{ __('Type medicine name, type, or place') }}">
                        <input type="hidden" id="medicine_id" name="medicine_id" value="{{ old('medicine_id') }}"
                            required>
                        <div id="medicine_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                        </div>
                        <div id="medicine_status" class="mt-2 text-xs text-slate-500"></div>
                    </div>

                    <div>
                        <label for="location_id" class="block mb-2 text-sm text-gray-700">{{ __('Lock location') }}</label>
                        <select id="location_id" name="location_id" required disabled
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-amber-500 focus:outline-none disabled:bg-slate-100">
                            <option value="">{{ __('-- Choose medicine first --') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="quantity" id="quantity_label" class="block mb-2 text-sm text-gray-700">{{ __('Quantity') }}</label>
                        <div class="flex rounded-lg border border-slate-200 overflow-hidden focus-within:border-amber-500">
                            <input type="number" id="quantity" name="quantity" min="1" step="1" required
                                value="{{ old('quantity', 1) }}"
                                class="w-full h-12 px-4 text-right text-slate-700 focus:outline-none">
                            <span id="quantity_unit"
                                class="inline-flex items-center px-4 bg-slate-50 text-sm text-slate-600 border-r border-slate-200">{{ __('Unit') }}</span>
                        </div>
                    </div>

                    <div>
                        <label for="starts_at" class="block mb-2 text-sm text-gray-700">{{ __('Lock start date') }}</label>
                        <input type="date" id="starts_at" name="starts_at"
                            value="{{ old('starts_at', now()->toDateString()) }}" required
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="ends_at" class="block mb-2 text-sm text-gray-700">{{ __('Lock end date') }}</label>
                        <input type="date" id="ends_at" name="ends_at"
                            value="{{ old('ends_at', now()->toDateString()) }}" required
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label for="lock_reason" class="block mb-2 text-sm text-gray-700">{{ __('Lock reason') }}</label>
                        <input type="text" id="lock_reason" name="lock_reason"
                            value="{{ old('lock_reason', 'معسكر') }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-amber-500 focus:outline-none"
                            placeholder="{{ __('Example: camp') }}">
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="block mb-2 text-sm text-gray-700">{{ __('Notes') }}</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-amber-500 focus:outline-none"
                            placeholder="{{ __('Optional') }}">
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100 transition">{{ __('Lock quantity') }}</button>
                </div>
            </form>
        </div>

        <x-data-table :data="$locks->toArray()" title="{{ __('Medicine lock log') }}" :header-buttons="[
            [
                'label' => __('Medicine stock'),
                'route' => route('medicine.index'),
                'cssClass' =>
                    'bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
        ]" :columns="[
            [
                'key' => 'MedicineName',
                'label' => __('Medicine'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'LocationName',
                'label' => __('Place'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
                'filter' => true,
            ],
            [
                'key' => 'QuantityText',
                'label' => __('Quantity'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 font-bold',
            ],
            [
                'key' => 'LockReason',
                'label' => __('Reason'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'StartsAt',
                'label' => __('From'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'EndsAt',
                'label' => __('To'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'StatusLabel',
                'label' => __('Status'),
                'type' => 'badge',
                'cssClass' => 'px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800',
                'filter' => true,
            ],
            [
                'key' => 'CreatorName',
                'label' => __('By'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
        ]"
            :actions="[
                [
                    'name' => 'release',
                    'label' => __('Release lock'),
                    'route' => route('medicine.locks.release', ':id'),
                    'idField' => 'MedicineStockLockID',
                    'method' => 'PATCH',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 transition-colors duration-200',
                ],
            ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const medicines = Object.values(@json($medicines->keyBy('MedicineID')));
            const oldMedicineId = @json(old('medicine_id'));
            const oldLocationId = @json(old('location_id'));

            const medicineSearch = document.getElementById('medicine_search');
            const medicineResults = document.getElementById('medicine_results');
            const medicineIdInput = document.getElementById('medicine_id');
            const medicineStatus = document.getElementById('medicine_status');
            const locationSelect = document.getElementById('location_id');
            const quantityInput = document.getElementById('quantity');
            const quantityLabel = document.getElementById('quantity_label');
            const quantityUnit = document.getElementById('quantity_unit');
            const startsAtInput = document.getElementById('starts_at');
            const endsAtInput = document.getElementById('ends_at');

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text ?? '';
                return div.innerHTML;
            }

            function activeLocations(medicine) {
                return (medicine?.Locations || []).filter((location) => Number(location.AvailableAmount) > 0);
            }

            function resetMedicineSelection() {
                medicineIdInput.value = '';
                medicineStatus.textContent = '';
                quantityLabel.textContent = @json(__('Quantity'));
                quantityUnit.textContent = @json(__('Unit'));
                quantityInput.removeAttribute('max');
                locationSelect.innerHTML = '<option value="">{{ __('-- Choose medicine first --') }}</option>';
                locationSelect.disabled = true;
            }

            function renderMedicineResults(searchValue = '') {
                const needle = searchValue.trim().toLowerCase();
                medicineResults.innerHTML = '';

                const filtered = medicines
                    .filter((medicine) => {
                        const haystack = [
                            medicine.MedicineName,
                            medicine.TypeLabel,
                            medicine.LocationBreakdown,
                            medicine.AvailableBreakdown,
                        ].join(' ').toLowerCase();

                        return !needle || haystack.includes(needle);
                    })
                    .slice(0, 30);

                if (!filtered.length) {
                    medicineResults.innerHTML =
                        '<div class="px-4 py-3 text-sm text-gray-500 text-center">{{ __('No results') }}</div>';
                    medicineResults.classList.remove('hidden');
                    return;
                }

                filtered.forEach((medicine) => {
                    const selectable = !medicine.IsExpired && Number(medicine.AvailableAmount) > 0;
                    const item = document.createElement('div');
                    item.className = selectable ?
                        'px-4 py-3 cursor-pointer hover:bg-amber-50 text-sm text-right border-b last:border-b-0' :
                        'px-4 py-3 text-sm text-right border-b last:border-b-0 bg-slate-50 text-slate-400';

                    item.innerHTML = `
                        <div class="font-bold">${escapeHtml(medicine.MedicineName)}</div>
                        <div class="text-xs mt-1">${escapeHtml(medicine.TypeLabel)} | ${@json(__('Available:'))} ${escapeHtml(medicine.AvailableText)} | ${@json(__('Locked:'))} ${escapeHtml(medicine.LockedText)}</div>
                        <div class="text-xs mt-1 text-slate-500">${escapeHtml(medicine.AvailableBreakdown)}</div>
                    `;

                    if (selectable) {
                        item.addEventListener('click', function() {
                            selectMedicine(medicine);
                            medicineResults.classList.add('hidden');
                        });
                    }

                    medicineResults.appendChild(item);
                });

                medicineResults.classList.remove('hidden');
            }

            function selectMedicine(medicine, selectedLocationId = null) {
                medicineIdInput.value = medicine.MedicineID;
                medicineSearch.value =
                    `${medicine.MedicineName} - ${medicine.TypeLabel} - ${@json(__('Available'))} ${medicine.AvailableText}`;
                quantityLabel.textContent = `${@json(__('Quantity'))} (${medicine.UnitLabel})`;
                quantityUnit.textContent = medicine.UnitLabel;
                medicineStatus.textContent =
                    `${@json(__('Available:'))} ${medicine.AvailableText} | ${@json(__('Locked:'))} ${medicine.LockedText} | ${medicine.AvailableBreakdown}`;

                const locations = activeLocations(medicine);
                locationSelect.innerHTML = '';
                locationSelect.disabled = locations.length === 0;

                locations.forEach((location) => {
                    const option = document.createElement('option');
                    option.value = location.LocationID;
                    option.textContent =
                        `${location.LocationName} - ${@json(__('Available'))} ${location.AvailableAmount} ${medicine.UnitLabel} (${@json(__('Locked'))} ${location.LockedAmount})`;
                    locationSelect.appendChild(option);
                });

                if (selectedLocationId && locations.some((location) => String(location.LocationID) === String(selectedLocationId))) {
                    locationSelect.value = selectedLocationId;
                }

                updateMaxQuantity(medicine);
            }

            function updateMaxQuantity(medicine = null) {
                const selectedMedicine = medicine || medicines.find((item) => String(item.MedicineID) === String(medicineIdInput.value));
                const selectedLocation = activeLocations(selectedMedicine).find((location) => String(location.LocationID) === String(locationSelect.value));

                if (!selectedLocation) return;

                quantityInput.max = selectedLocation.AvailableAmount;
                if (!quantityInput.value || Number(quantityInput.value) < 1) quantityInput.value = 1;
                if (Number(quantityInput.value) > Number(selectedLocation.AvailableAmount)) {
                    quantityInput.value = selectedLocation.AvailableAmount;
                }
            }

            medicineSearch.addEventListener('input', function() {
                resetMedicineSelection();
                renderMedicineResults(this.value);
            });

            medicineSearch.addEventListener('focus', function() {
                renderMedicineResults(this.value);
            });

            locationSelect.addEventListener('change', function() {
                updateMaxQuantity();
            });

            startsAtInput.addEventListener('change', function() {
                if (!endsAtInput.value || endsAtInput.value < startsAtInput.value) {
                    endsAtInput.value = startsAtInput.value;
                }

                endsAtInput.min = startsAtInput.value;
            });

            endsAtInput.min = startsAtInput.value;

            document.addEventListener('click', function(e) {
                if (!medicineSearch.contains(e.target) && !medicineResults.contains(e.target)) {
                    medicineResults.classList.add('hidden');
                }
            });

            if (oldMedicineId) {
                const selected = medicines.find((medicine) => String(medicine.MedicineID) === String(oldMedicineId));
                if (selected) selectMedicine(selected, oldLocationId);
            }
        });
    </script>
@endsection
