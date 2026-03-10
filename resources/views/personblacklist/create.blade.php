@extends('layouts.app', ['pageTitle' => 'إضافة إلى القائمة السوداء'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة إلى القائمة السوداء</h2>
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

            <form method="POST" action="{{ route('personblacklist.insert') }}">
                @csrf

                <div class="space-y-6">
                    <div class="relative">
                        <label for="person_search" class="block mb-2 text-sm text-gray-700">ابحث عن الشخص</label>
                        <input type="text" id="person_search" autocomplete="off"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب الاسم أو الرقم">

                        <input type="hidden" id="person_id" name="person_id" value="{{ old('person_id') }}" required>

                        <div id="search_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        </div>
                    </div>

                    <div class="relative">
                        <label for="note" class="block mb-2 text-sm text-gray-700">ملاحظة</label>
                        <textarea id="note" name="note" rows="4"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب الملاحظة هنا">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            إضافة إلى القائمة السوداء
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

            let debounceTimer;

            function hideResults() {
                resultsBox.classList.add('hidden');
            }

            function showResults() {
                resultsBox.classList.remove('hidden');
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
                    fetch(
                            `{{ route('personblacklist.searchPersons') }}?search=${encodeURIComponent(searchValue)}`)
                        .then(response => response.json())
                        .then(persons => {
                            resultsBox.innerHTML = '';

                            if (!persons.length) {
                                resultsBox.innerHTML = `
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        لا يوجد نتائج
                                    </div>
                                `;
                                showResults();
                                return;
                            }

                            persons.forEach(person => {
                                const item = document.createElement('div');
                                item.className =
                                    'px-4 py-3 cursor-pointer hover:bg-blue-50 text-sm text-right border-b last:border-b-0';
                                item.textContent =
                                    `${person.PersonName} - (${person.PersonID})`;

                                item.addEventListener('click', function() {
                                    personIdInput.value = person.PersonID;
                                    searchInput.value =
                                        `${person.PersonName} - (${person.PersonID})`;
                                    resultsBox.innerHTML = '';
                                    hideResults();
                                });

                                resultsBox.appendChild(item);
                            });

                            showResults();
                        })
                        .catch(error => {
                            console.error('Error fetching persons:', error);
                            resultsBox.innerHTML = `
                                <div class="px-4 py-3 text-sm text-red-500 text-center">
                                    خطأ في تحميل الأشخاص
                                </div>
                            `;
                            showResults();
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
