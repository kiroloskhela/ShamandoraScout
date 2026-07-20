@extends('layouts.app', ['pageTitle' => __('Record exam mark')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تسجيل درجات الامتحان</h2>
                <p class="text-sm text-gray-500 mt-2">الدرجات أرقام صحيحة فقط (من غير كسور)، ويمكن أن تتجاوز 100.</p>
            </div>

            @if (session('error'))
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm text-center">
                    {{ session('error') }}
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

            <form method="POST" action="{{ route('personexammark.insert') }}">
                @csrf

                <div class="space-y-5">
                    <div class="relative">
                        <label for="person_search" class="block mb-2 text-sm text-gray-700">ابحث عن المخدوم</label>
                        <input type="text" id="person_search" autocomplete="off"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب الاسم أو الرقم أو الموبايل" value="">
                        <input type="hidden" id="person_id" name="person_id" value="{{ old('person_id') }}" required>
                        <div id="search_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        </div>
                    </div>

                    <div>
                        <label for="qetaa_id" class="block mb-2 text-sm text-gray-700">القطعة (وقت الامتحان)</label>
                        <select id="qetaa_id" name="qetaa_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="">اختر القطعة</option>
                            @foreach ($qetaas as $q)
                                <option value="{{ $q->QetaaID }}" @selected(old('qetaa_id') == $q->QetaaID)>
                                    {{ $q->QetaaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sana_marhala_id" class="block mb-2 text-sm text-gray-700">سنة المرحلة (وقت الامتحان)</label>
                        <select id="sana_marhala_id" name="sana_marhala_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="">اختر سنة المرحلة</option>
                            @foreach ($sanaMarhalas as $sm)
                                <option value="{{ $sm->SanaMarhalaID }}" @selected(old('sana_marhala_id') == $sm->SanaMarhalaID)>
                                    {{ $sm->SanaMarhalaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="theoretical_mark" class="block mb-2 text-sm text-gray-700">درجة النظري</label>
                            <input type="number" id="theoretical_mark" name="theoretical_mark" required min="0" max="999" step="1"
                                value="{{ old('theoretical_mark') }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                                placeholder="مثال: 80">
                        </div>
                        <div>
                            <label for="practical_mark" class="block mb-2 text-sm text-gray-700">درجة العملي</label>
                            <input type="number" id="practical_mark" name="practical_mark" required min="0" max="999" step="1"
                                value="{{ old('practical_mark') }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                                placeholder="مثال: 70">
                        </div>
                    </div>

                    <div>
                        <label for="exam_date" class="block mb-2 text-sm text-gray-700">تاريخ الامتحان</label>
                        <input type="date" id="exam_date" name="exam_date" required
                            value="{{ old('exam_date', date('Y-m-d')) }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="note" class="block mb-2 text-sm text-gray-700">{{ __('Note (optional)') }}</label>
                        <textarea id="note" name="note" rows="2"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="مثال: امتحان نصفي / نهائي">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('personexammark.index') }}"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 transition">{{ __('Back') }}</a>
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            حفظ الدرجات
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
            const qetaaSelect = document.getElementById('qetaa_id');
            const sanaSelect = document.getElementById('sana_marhala_id');
            let debounceTimer = null;

            function hideResults() { resultsBox.classList.add('hidden'); }
            function showResults() { resultsBox.classList.remove('hidden'); }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text ?? '';
                return div.innerHTML;
            }

            function renderNoResults(message, cssClass = 'text-gray-500') {
                resultsBox.innerHTML = `<div class="px-4 py-3 text-sm ${cssClass} text-center">${escapeHtml(message)}</div>`;
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
                    fetch(`{{ route('personexammark.searchPersons') }}?search=${encodeURIComponent(searchValue)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async (response) => {
                            if (!response.ok) throw new Error(await response.text() || 'Request failed');
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
                                const phone = person.PersonPersonalMobileNumber ?? 'بدون رقم';
                                const qetaa = person.QetaaName ?? '';
                                const sana = person.SanaMarhalaName ?? '';
                                item.textContent =
                                    `${personName} - (${personId}) - ${qetaa} / ${sana} - (${phone})`;

                                item.addEventListener('click', function() {
                                    personIdInput.value = personId;
                                    searchInput.value =
                                        `${personName} - (${personId}) - (${phone})`;
                                    if (person.QetaaID) qetaaSelect.value = String(person.QetaaID);
                                    if (person.SanaMarhalaID) sanaSelect.value = String(person.SanaMarhalaID);
                                    resultsBox.innerHTML = '';
                                    hideResults();
                                });

                                resultsBox.appendChild(item);
                            });
                            showResults();
                        })
                        .catch((error) => {
                            console.error(error);
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
