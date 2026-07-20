@extends('layouts.app', ['pageTitle' => __('Add special case')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة حالة خاصة</h2>
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

            <form method="POST" action="{{ route('personspecialcase.insert') }}">
                @csrf

                <div class="space-y-6">
                    <div class="relative">
                        <label for="person_search" class="block mb-2 text-sm text-gray-700">ابحث عن الشخص</label>

                        <input type="text" id="person_search" autocomplete="off"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب الاسم أو الرقم أو الموبايل" value="">

                        <input type="hidden" id="person_id" name="person_id" value="{{ old('person_id') }}" required>

                        <div id="search_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        </div>
                    </div>

                    <div class="relative">
                        <label for="note" class="block mb-2 text-sm text-gray-700">{{ __('Note') }}</label>
                        <textarea id="note" name="note" rows="4"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Write the note here') }}">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            إضافة الحالة الخاصة
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('person_search');
            const resultsBox = document.getElementById('search_results');
            const personIdInput = document.getElementById('person_id');

            let debounceTimer = null;

            function hideResults() {
                resultsBox.classList.add('hidden');
            }

            function showResults() {
                resultsBox.classList.remove('hidden');
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text ?? '';
                return div.innerHTML;
            }

            function renderNoResults(message, cssClass = 'text-gray-500') {
                resultsBox.innerHTML = `
                    <div class="px-4 py-3 text-sm ${cssClass} text-center">
                        ${escapeHtml(message)}
                    </div>
                `;
                showResults();
            }

            searchInput.addEventListener('input', function() {
                const searchValue = this.value.trim();

                personIdInput.value = '';
                clearTimeout(debounceTimer);

                if (searchValue.length < 2) {
                    resultsBox.innerHTML = '';
                    hideResults();
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('personspecialcase.searchPersons') }}?search=${encodeURIComponent(searchValue)}`, {
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
                            resultsBox.innerHTML = '';

                            if (!Array.isArray(persons) || !persons.length) {
                                renderNoResults('لا يوجد نتائج');
                                return;
                            }

                            persons.forEach((person) => {
                                const item = document.createElement('div');
                                item.className =
                                    'px-4 py-3 cursor-pointer hover:bg-blue-50 text-sm text-right border-b last:border-b-0';

                                const personName = person.PersonName ?? '';
                                const personId = person.PersonID ?? '';
                                const phone = person.PersonPersonalMobileNumber ??
                                    'بدون رقم';

                                item.textContent =
                                    `${personName} - (${personId}) - (${phone})`;

                                item.addEventListener('click', function() {
                                    personIdInput.value = personId;
                                    searchInput.value =
                                        `${personName} - (${personId}) - (${phone})`;
                                    resultsBox.innerHTML = '';
                                    hideResults();
                                });

                                resultsBox.appendChild(item);
                            });

                            showResults();
                        })
                        .catch((error) => {
                            console.error('Error fetching persons:', error);
                            renderNoResults('خطأ في تحميل الأشخاص', 'text-red-500');
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    hideResults();
                }
            });
        });
    </script>
@endsection
