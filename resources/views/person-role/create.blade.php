@extends('layouts.app', ['pageTitle' => 'ربط القاده بالمهام'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">

        <div class="bg-white shadow-md rounded-lg p-6 mb-8  border-2 border-blue-300">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">ربط قائد جديد بمهام</h2>
            <form method="POST" action="{{ route('person-role.insert') }}">
                @csrf

                <!-- Hidden Requester ID -->
                <input type="hidden" name="RequestPersonID" value="{{ auth()->user()->PersonID }}">

                <!-- اختيار اسم الخادم -->
                <div class="mb-6">
                    <label for="person_id" class="block text-sm font-semibold text-gray-700 mb-2">اختر اسم الخادم</label>
                    <div class="relative">
                        <input type="text" id="person_search"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm py-2 px-4 pr-10"
                            placeholder="ابحث واختر اسم الخادم..." autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <!-- Hidden select for form submission -->
                        <select id="person_id" name="person_id" class="hidden">
                            <option disabled selected value="">اختر اسم الخادم</option>
                            @foreach ($khoddam as $khadem)
                                <option value="{{ $khadem->PersonID }}">{{ $khadem->PersonFullName }}</option>
                            @endforeach
                        </select>

                        <!-- Dropdown list -->
                        <div id="dropdown"
                            class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-auto hidden">
                            @foreach ($khoddam as $khadem)
                                <div class="dropdown-option px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                                    data-value="{{ $khadem->PersonID }}" data-text="{{ $khadem->PersonFullName }}">
                                    {{ $khadem->PersonFullName }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const searchInput = document.getElementById('person_search');
                        const hiddenSelect = document.getElementById('person_id');
                        const dropdown = document.getElementById('dropdown');
                        const options = dropdown.querySelectorAll('.dropdown-option');

                        // Show dropdown when input is focused
                        searchInput.addEventListener('focus', function() {
                            dropdown.classList.remove('hidden');
                            filterOptions();
                        });

                        // Hide dropdown when clicking outside
                        document.addEventListener('click', function(e) {
                            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                                dropdown.classList.add('hidden');
                            }
                        });

                        // Filter options based on search input
                        searchInput.addEventListener('input', function() {
                            filterOptions();
                        });

                        // Handle option selection
                        options.forEach(option => {
                            option.addEventListener('click', function() {
                                const value = this.getAttribute('data-value');
                                const text = this.getAttribute('data-text');

                                searchInput.value = text;
                                hiddenSelect.value = value;
                                dropdown.classList.add('hidden');

                                // Trigger change event on hidden select for form validation
                                hiddenSelect.dispatchEvent(new Event('change'));
                            });
                        });

                        function filterOptions() {
                            const searchTerm = searchInput.value.toLowerCase();
                            let hasVisibleOptions = false;

                            options.forEach(option => {
                                const text = option.getAttribute('data-text').toLowerCase();
                                if (text.includes(searchTerm)) {
                                    option.style.display = 'block';
                                    hasVisibleOptions = true;
                                } else {
                                    option.style.display = 'none';
                                }
                            });

                            // Show "No results found" message if no options match
                            let noResultsMsg = dropdown.querySelector('.no-results');
                            if (!hasVisibleOptions && searchTerm.length > 0) {
                                if (!noResultsMsg) {
                                    noResultsMsg = document.createElement('div');
                                    noResultsMsg.className = 'no-results px-4 py-2 text-gray-500 text-sm';
                                    noResultsMsg.textContent = 'لا توجد نتائج';
                                    dropdown.appendChild(noResultsMsg);
                                }
                                noResultsMsg.style.display = 'block';
                            } else if (noResultsMsg) {
                                noResultsMsg.style.display = 'none';
                            }
                        }

                        // Clear search when backspace/delete is pressed and input is empty
                        searchInput.addEventListener('keydown', function(e) {
                            if ((e.key === 'Backspace' || e.key === 'Delete') && this.value === '') {
                                hiddenSelect.value = '';
                                hiddenSelect.dispatchEvent(new Event('change'));
                            }
                        });
                    });
                </script>

                <!-- اختيار الدور/المهمة -->
                <div class="mb-6">
                    <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">اختر الدور/المهمة</label>
                    <select id="role_id" name="role_id"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm py-2 px-4">
                        <option disabled selected value="">اختر الدور/المهمة</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->RoleID }}">{{ $role->RoleName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full focus-visible:outline-none whitespace-nowrap bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 focus:bg-blue-200 focus:text-blue-700 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-100 disabled:text-blue-400 disabled:shadow-none">
                        إدخال
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
