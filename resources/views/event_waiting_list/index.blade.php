@extends('layouts.app', ['pageTitle' => 'إدارة قائمة انتظار الفعالية'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إدارة قائمة انتظار الفعالية</h2>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <div><strong>الموسم:</strong> {{ $event->SeasonName }} ({{ $event->SeasonYear }})</div>
                <div><strong>الفعالية:</strong> {{ $event->EventTypeName }} - {{ $event->EventName }}</div>
                <div><strong>بداية الفعالية:</strong> {{ $event->EventStartDate }}</div>
                <div><strong>نهاية الفعالية:</strong> {{ $event->EventEndDate }}</div>
            </div>

            <div class="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">إضافة شخص إلى قائمة الانتظار</h3>
                </div>

                <form method="POST" action="{{ route('eventWaitingList.store', $event->SeasonEventID) }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block mb-2 text-sm text-gray-700">بحث عن شخص مؤهل</label>
                            <input type="text" id="person-search"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                                placeholder="ابحث بالاسم أو PersonID أو الموبايل">
                            <input type="hidden" name="person_id" id="person_id" value="{{ old('person_id') }}">
                            <div id="search-results"
                                class="mt-2 border rounded-lg bg-white shadow hidden max-h-80 overflow-y-auto"></div>
                        </div>

                        <div id="selected-person-box"
                            class="hidden rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                            <div><strong>الاسم:</strong> <span id="selected-person-name"></span></div>
                            <div><strong>PersonID:</strong> <span id="selected-person-id"></span></div>
                            <div><strong>الموبايل:</strong> <span id="selected-person-mobile"></span></div>
                            <div><strong>القطاع:</strong> <span id="selected-person-qetaa"></span></div>
                        </div>

                        <div class="flex justify-center gap-3">
                            <a href="{{ route('eventWaitingList.selector') }}"
                                class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                                تغيير الفعالية
                            </a>

                            <button type="submit"
                                class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                                إضافة إلى قائمة الانتظار
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <x-data-table :data="$waitingList" title="قائمة الانتظار" tableId="WaitingList" :columns="[
                [
                    'key' => 'PersonFullName',
                    'label' => 'الاسم',
                    'type' => 'label',
                    'cssClass' => 'text-blue-600 font-bold text-sm',
                ],
                [
                    'key' => 'PersonID',
                    'label' => 'رقم الهوية',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 font-medium',
                ],
                [
                    'key' => 'PersonPersonalMobileNumber',
                    'label' => 'الموبايل',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'MotherMobileNumber',
                    'label' =>'هاتف الام',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'QetaaName',
                    'label' => 'القطاع',
                    'type' => 'label',
                    'filter' => true,
                    'cssClass' => 'text-sm text-gray-800 font-medium',
                ],
                [
                    'key' => 'ServentFullName',
                    'label' => 'أضافه الخادم',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'CreatedAt',
                    'label' => 'تاريخ الإضافة',
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
            ]" :actions="[
                [
                    'name' => 'delete',
                    'label' => 'حذف',
                    'route' => route('eventWaitingList.deletePage', ':id'),
                    'idField' => 'SeasonEventWaitingListID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200',
                ],
            ]"
                :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('person-search');
            const resultsBox = document.getElementById('search-results');
            const personIdInput = document.getElementById('person_id');

            const selectedBox = document.getElementById('selected-person-box');
            const selectedName = document.getElementById('selected-person-name');
            const selectedId = document.getElementById('selected-person-id');
            const selectedMobile = document.getElementById('selected-person-mobile');
            const selectedQetaa = document.getElementById('selected-person-qetaa');

            let timeout = null;

            searchInput.addEventListener('input', function() {
                const q = this.value.trim();
                clearTimeout(timeout);

                if (q.length < 1) {
                    resultsBox.classList.add('hidden');
                    resultsBox.innerHTML = '';
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(
                            `{{ route('eventWaitingList.searchEligiblePersons', $event->SeasonEventID) }}?q=${encodeURIComponent(q)}`
                        )
                        .then(res => res.json())
                        .then(data => {
                            resultsBox.innerHTML = '';
                            resultsBox.classList.remove('hidden');

                            if (!data.length) {
                                resultsBox.innerHTML =
                                    '<div class="p-3 text-sm text-gray-500">لا توجد نتائج</div>';
                                return;
                            }

                            data.forEach(person => {
                                const item = document.createElement('div');
                                item.className =
                                    'p-3 border-b last:border-b-0 cursor-pointer text-gray-800 hover:bg-gray-50';

                                item.innerHTML = `
                                    <div>
                                        <div class="font-bold">${person.PersonFullName}</div>
                                        <div class="text-xs mt-1">PersonID: ${person.PersonID}</div>
                                        <div class="text-xs">الموبايل: ${person.PersonPersonalMobileNumber ?? '-'}</div>
                                        <div class="text-xs">القطاع: ${person.QetaaNames ?? '-'}</div>
                                    </div>
                                `;

                                item.addEventListener('click', function() {
                                    personIdInput.value = person.PersonID;
                                    searchInput.value = person.PersonFullName;
                                    selectedName.textContent = person
                                        .PersonFullName;
                                    selectedId.textContent = person.PersonID;
                                    selectedMobile.textContent = person
                                        .PersonPersonalMobileNumber ?? '-';
                                    selectedQetaa.textContent = person
                                        .QetaaNames ?? '-';
                                    selectedBox.classList.remove('hidden');
                                    resultsBox.classList.add('hidden');
                                });

                                resultsBox.appendChild(item);
                            });
                        })
                        .catch(() => {
                            resultsBox.innerHTML =
                                '<div class="p-3 text-sm text-red-500">حدث خطأ أثناء البحث</div>';
                            resultsBox.classList.remove('hidden');
                        });
                }, 250);
            });

            document.addEventListener('click', function(e) {
                if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                    resultsBox.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
