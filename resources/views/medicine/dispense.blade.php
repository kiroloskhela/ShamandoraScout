@extends('layouts.app', ['pageTitle' => __('Dispense medicine')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl mx-auto border-2 border-emerald-300">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ __('Dispense medicine') }}</h1>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('medicine.dispense.store') }}" id="dispenseForm">
                @csrf

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 relative">
                        <label for="medicine_search" class="block mb-2 text-sm text-gray-700">{{ __('Medicine') }}</label>
                        <input type="text" id="medicine_search" autocomplete="off"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none"
                            placeholder="{{ __('Type medicine name, type, or place') }}">
                        <input type="hidden" id="medicine_id" name="medicine_id" value="{{ old('medicine_id') }}"
                            required>

                        <div id="medicine_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                        </div>

                        <div id="medicine_status" class="mt-2 text-xs text-slate-500"></div>
                    </div>

                    <div>
                        <label for="location_id" class="block mb-2 text-sm text-gray-700">{{ __('Medicine place') }}</label>
                        <select id="location_id" name="location_id" required disabled
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none disabled:bg-slate-100">
                            <option value="">{{ __('-- Choose medicine first --') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="quantity" id="quantity_label" class="block mb-2 text-sm text-gray-700">{{ __('Quantity') }}</label>
                        <div class="flex rounded-lg border border-slate-200 overflow-hidden focus-within:border-emerald-500">
                            <input type="number" id="quantity" name="quantity" min="1" step="1" required
                                value="{{ old('quantity', 1) }}"
                                class="w-full h-12 px-4 text-right text-slate-700 focus:outline-none">
                            <span id="quantity_unit"
                                class="inline-flex items-center px-4 bg-slate-50 text-sm text-slate-600 border-r border-slate-200">{{ __('Unit') }}</span>
                        </div>
                    </div>

                    <div class="md:col-span-2 relative">
                        <label for="person_search" class="block mb-2 text-sm text-gray-700">{{ __('Person') }}</label>
                        <input type="text" id="person_search" autocomplete="off"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none"
                            placeholder="{{ __('Type name, code, or mobile number') }}">
                        <input type="hidden" id="person_id" name="person_id" value="{{ old('person_id') }}" required>

                        <div id="person_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="block mb-2 text-sm text-gray-700">{{ __('Notes') }}</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none"
                            placeholder="{{ __('Optional') }}">
                    </div>
                </div>

                <div class="flex justify-center gap-3 mt-8">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">{{ __('Record dispense') }}</button>

                    <a href="{{ route('medicine.records') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition">{{ __('Log') }}</a>
                </div>
            </form>
        </div>
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

            const personSearch = document.getElementById('person_search');
            const personResults = document.getElementById('person_results');
            const personIdInput = document.getElementById('person_id');

            let personDebounceTimer = null;

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text ?? '';
                return div.innerHTML;
            }

            function show(box) {
                box.classList.remove('hidden');
            }

            function hide(box) {
                box.classList.add('hidden');
            }

            function activeLocations(medicine) {
                return (medicine?.Locations || []).filter((location) => Number(location.AvailableAmount) > 0);
            }

            function medicineLabel(medicine) {
                return `${medicine.MedicineName} - ${medicine.TypeLabel} - ${@json(__('Available'))} ${medicine.AvailableText}`;
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
                    show(medicineResults);
                    return;
                }

                filtered.forEach((medicine) => {
                    const selectable = !medicine.IsExpired && Number(medicine.AvailableAmount) > 0;
                    const item = document.createElement('div');
                    item.className = selectable ?
                        'px-4 py-3 cursor-pointer hover:bg-emerald-50 text-sm text-right border-b last:border-b-0' :
                        'px-4 py-3 text-sm text-right border-b last:border-b-0 bg-slate-50 text-slate-400';

                    item.innerHTML = `
                        <div class="font-bold">${escapeHtml(medicine.MedicineName)}</div>
                        <div class="text-xs mt-1">${escapeHtml(medicine.TypeLabel)} | ${@json(__('Available:'))} ${escapeHtml(medicine.AvailableText)} | ${@json(__('Locked:'))} ${escapeHtml(medicine.LockedText)}</div>
                        <div class="text-xs mt-1 text-slate-500">${escapeHtml(medicine.AvailableBreakdown)}</div>
                    `;

                    if (selectable) {
                        item.addEventListener('click', function() {
                            selectMedicine(medicine);
                            hide(medicineResults);
                        });
                    }

                    medicineResults.appendChild(item);
                });

                show(medicineResults);
            }

            function selectMedicine(medicine, selectedLocationId = null) {
                medicineIdInput.value = medicine.MedicineID;
                medicineSearch.value = medicineLabel(medicine);
                quantityLabel.textContent = `${@json(__('Quantity'))} (${medicine.UnitLabel})`;
                quantityUnit.textContent = medicine.UnitLabel;
                medicineStatus.textContent =
                    `${@json(__('Available:'))} ${medicine.AvailableText} | ${@json(__('Locked:'))} ${medicine.LockedText} | ${@json(__('Expiry date:'))} ${medicine.ExpirationDate} | ${@json(__('Status:'))} ${medicine.StatusLabel}`;

                const locations = activeLocations(medicine);
                locationSelect.innerHTML = '';
                locationSelect.disabled = locations.length === 0;

                if (!locations.length) {
                    locationSelect.innerHTML = '<option value="">{{ __('No location has available quantity') }}</option>';
                    quantityInput.removeAttribute('max');
                    return;
                }

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

                if (!selectedLocation) {
                    quantityInput.removeAttribute('max');
                    return;
                }

                quantityInput.max = selectedLocation.AvailableAmount;

                if (!quantityInput.value || Number(quantityInput.value) < 1) {
                    quantityInput.value = 1;
                }

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

            personSearch.addEventListener('input', function() {
                const searchValue = this.value.trim();
                personIdInput.value = '';
                clearTimeout(personDebounceTimer);

                if (searchValue.length < 2) {
                    personResults.innerHTML = '';
                    hide(personResults);
                    return;
                }

                personDebounceTimer = setTimeout(() => {
                    fetch(`{{ route('medicine.search-persons') }}?search=${encodeURIComponent(searchValue)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async (response) => {
                            if (!response.ok) {
                                const text = await response.text();
                                throw new Error(text || 'Request failed');
                            }
                            return response.json();
                        })
                        .then((persons) => {
                            personResults.innerHTML = '';

                            if (!Array.isArray(persons) || !persons.length) {
                                personResults.innerHTML =
                                    '<div class="px-4 py-3 text-sm text-gray-500 text-center">{{ __('No results') }}</div>';
                                show(personResults);
                                return;
                            }

                            persons.forEach((person) => {
                                const item = document.createElement('div');
                                item.className =
                                    'px-4 py-3 cursor-pointer hover:bg-emerald-50 text-sm text-right border-b last:border-b-0';

                                const personName = person.PersonName ?? '';
                                const personId = person.PersonID ?? '';
                                const code = person.ShamandoraCode ?? '';
                                const phone = person.PersonPersonalMobileNumber ?? @json(__('No number'));
                                const label = `${personName} - (${personId}) - ${code} - ${phone}`;

                                item.textContent = label;
                                item.addEventListener('click', function() {
                                    personIdInput.value = personId;
                                    personSearch.value = label;
                                    personResults.innerHTML = '';
                                    hide(personResults);
                                });

                                personResults.appendChild(item);
                            });

                            show(personResults);
                        })
                        .catch((error) => {
                            console.error('Error fetching persons:', error);
                            personResults.innerHTML =
                                '<div class="px-4 py-3 text-sm text-red-500 text-center">{{ __('Error loading people') }}</div>';
                            show(personResults);
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!medicineSearch.contains(e.target) && !medicineResults.contains(e.target)) {
                    hide(medicineResults);
                }

                if (!personSearch.contains(e.target) && !personResults.contains(e.target)) {
                    hide(personResults);
                }
            });

            if (oldMedicineId) {
                const selected = medicines.find((medicine) => String(medicine.MedicineID) === String(oldMedicineId));
                if (selected) {
                    selectMedicine(selected, oldLocationId);
                }
            }
        });
    </script>
@endsection
