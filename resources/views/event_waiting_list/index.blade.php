@extends('layouts.app', ['pageTitle' => __('Manage event waiting list')])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full border-2 border-blue-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ __('Manage event waiting list') }}</h2>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-100 dark:bg-green-900/40 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-blue-200 dark:border-slate-700 bg-blue-50 dark:bg-slate-800/60 p-4 text-sm text-blue-900 dark:text-slate-200">
                <div><strong>{{ __('Season:') }}</strong> {{ $event->SeasonName }} ({{ $event->SeasonYear }})</div>
                <div><strong>{{ __('Event:') }}</strong> {{ $event->EventTypeName }} - {{ $event->EventName }}</div>
                <div><strong>{{ __('Event start:') }}</strong> {{ $event->EventStartDate }}</div>
                <div><strong>{{ __('Event end:') }}</strong> {{ $event->EventEndDate }}</div>
            </div>

            <div class="mb-8 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-slate-100">{{ __('Add person to waiting list') }}</h3>
                </div>

                <form method="POST" action="{{ route('eventWaitingList.store', $event->SeasonEventID) }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block mb-2 text-sm text-gray-700">{{ __('Search for eligible person') }}</label>
                            <input type="text" id="person-search"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                                placeholder="{{ __('Search by name, PersonID, or mobile') }}">
                            <input type="hidden" name="person_id" id="person_id" value="{{ old('person_id') }}">
                            <div id="search-results"
                                class="mt-2 border rounded-lg bg-white shadow hidden max-h-80 overflow-y-auto"></div>
                        </div>

                        <div id="selected-person-box"
                            class="hidden rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                            <div><strong>{{ __('Name:') }}</strong> <span id="selected-person-name"></span></div>
                            <div><strong>PersonID:</strong> <span id="selected-person-id"></span></div>
                            <div><strong>{{ __('Mobile:') }}</strong> <span id="selected-person-mobile"></span></div>
                            <div><strong>{{ __('Sector:') }}</strong> <span id="selected-person-qetaa"></span></div>
                        </div>

                        <div class="flex justify-center gap-3">
                            <a href="{{ route('eventWaitingList.selector') }}"
                                class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Change event') }}</a>

                            <button type="submit"
                                class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">{{ __('Add to waiting list') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <x-data-table :data="$waitingList" title="{{ __('Waiting list') }}" tableId="WaitingList" :columns="[
                [
                    'key' => 'PersonFullName',
                    'label' => __('Name'),
                    'type' => 'label',
                    'cssClass' => 'text-blue-600 font-bold text-sm',
                ],
                [
                    'key' => 'PersonID',
                    'label' => __('ID number'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900 font-medium',
                ],
                [
                    'key' => 'PersonPersonalMobileNumber',
                    'label' => __('Mobile'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'MotherMobileNumber',
                    'label' =>__('Mother phone'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'QetaaName',
                    'label' => __('Sector'),
                    'type' => 'label',
                    'filter' => true,
                    'cssClass' => 'text-sm text-gray-800 font-medium',
                ],
                [
                    'key' => 'ServentFullName',
                    'label' => __('Added by servant'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
                [
                    'key' => 'CreatedAt',
                    'label' => __('Added at'),
                    'type' => 'text',
                    'cssClass' => 'text-sm text-gray-900',
                ],
            ]" :actions="[
                [
                    'name' => 'delete',
                    'label' => __('Delete'),
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

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[s]));
            }

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
                                    '<div class="p-3 text-sm text-gray-500">{{ __('No results') }}</div>';
                                return;
                            }

                            data.forEach(person => {
                                const item = document.createElement('div');
                                item.className =
                                    'p-3 border-b last:border-b-0 cursor-pointer text-gray-800 hover:bg-gray-50';

                                item.innerHTML = `
                                    <div>
                                        <div class="font-bold">${escapeHtml(person.PersonFullName)}</div>
                                        <div class="text-xs mt-1">PersonID: ${escapeHtml(person.PersonID)}</div>
                                        <div class="text-xs">${@json(__('Mobile:'))} ${escapeHtml(person.PersonPersonalMobileNumber ?? '-')}</div>
                                        <div class="text-xs">${@json(__('Sector:'))} ${escapeHtml(person.QetaaNames ?? '-')}</div>
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
                                '<div class="p-3 text-sm text-red-500">{{ __('An error occurred while searching') }}</div>';
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
