@extends('layouts.app', ['pageTitle' => __('Send notification')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-green-300">

            {{-- Title --}}
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إرسال إشعار</h2>
            </div>

            {{-- Errors --}}
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Success --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('notifications.send') }}" id="notification-form">
                @csrf

                <div class="space-y-6">

                    {{-- Search person --}}
                    <div class="relative">
                        <label for="person_search" class="block mb-2 text-sm text-gray-700">ابحث عن الشخص</label>
                        <input type="text" id="person_search" autocomplete="off"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none"
                            placeholder="الاسم أو رقم الهوية أو رقم الموبايل">
                        <input type="hidden" name="person_id" id="person_id" value="{{ old('person_id') }}" required>
                        <div id="person_results"
                            class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-72 overflow-y-auto"></div>
                        <p id="selected-person" class="mt-2 text-sm text-green-700 hidden"></p>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">عنوان الإشعار</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 focus:border-green-500 focus:outline-none"
                            placeholder="ادخل عنوان الإشعار" required>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">نص الإشعار</label>
                        <textarea name="body"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 focus:border-green-500 focus:outline-none"
                            rows="3" placeholder="ادخل نص الإشعار" required>{{ old('body') }}</textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition">
                            إرسال
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('person_search');
            const resultsBox = document.getElementById('person_results');
            const personIdInput = document.getElementById('person_id');
            const selectedLabel = document.getElementById('selected-person');
            const searchUrl = @json(route('person.search'));
            let debounceTimer = null;

            function show(box) {
                box.classList.remove('hidden');
            }

            function hide(box) {
                box.classList.add('hidden');
            }

            function personLabel(person) {
                const name = person.FullName || '';
                const id = person.PersonID ?? '';
                const code = person.ShamandoraCode || '';
                const phone = person.PersonPersonalMobileNumber || '';

                return [name, id, code, phone].filter(Boolean).join(' — ');
            }

            function selectPerson(person) {
                personIdInput.value = person.PersonID;
                searchInput.value = personLabel(person);
                selectedLabel.textContent = personLabel(person);
                show(selectedLabel);
                resultsBox.innerHTML = '';
                hide(resultsBox);
            }

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                personIdInput.value = '';
                hide(selectedLabel);
                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    resultsBox.innerHTML = '';
                    hide(resultsBox);
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async (response) => {
                            if (!response.ok) {
                                throw new Error('Request failed');
                            }
                            return response.json();
                        })
                        .then((people) => {
                            resultsBox.innerHTML = '';

                            if (!Array.isArray(people) || !people.length) {
                                const empty = document.createElement('div');
                                empty.className = 'px-4 py-3 text-sm text-gray-500 text-center';
                                empty.textContent = 'لا توجد نتائج';
                                resultsBox.appendChild(empty);
                                show(resultsBox);
                                return;
                            }

                            people.forEach((person) => {
                                const item = document.createElement('div');
                                item.className =
                                    'px-4 py-3 cursor-pointer hover:bg-green-50 text-sm text-right border-b last:border-b-0';
                                item.textContent = personLabel(person);
                                item.addEventListener('click', function() {
                                    selectPerson(person);
                                });
                                resultsBox.appendChild(item);
                            });

                            show(resultsBox);
                        })
                        .catch(() => {
                            resultsBox.innerHTML = '';
                            const error = document.createElement('div');
                            error.className = 'px-4 py-3 text-sm text-red-500 text-center';
                            error.textContent = 'حدث خطأ أثناء البحث';
                            resultsBox.appendChild(error);
                            show(resultsBox);
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    hide(resultsBox);
                }
            });

            document.getElementById('notification-form').addEventListener('submit', function(e) {
                if (!personIdInput.value) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        });
    </script>
@endsection
