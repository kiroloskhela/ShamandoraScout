@extends('layouts.app', ['pageTitle' => 'إضافة ضيف'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-blue-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة ضيف</h2>
                <p class="text-sm text-gray-500 mt-2">يمكنك إدخال بيانات الضيف وربطه بشخص من النظام</p>
            </div>

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('guests.store') }}">
                @csrf

                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">بيانات الضيف</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block mb-2 text-sm text-gray-700">{{ __('First name') }}</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="second_name" class="block mb-2 text-sm text-gray-700">{{ __('Second name') }}</label>
                            <input type="text" id="second_name" name="second_name" value="{{ old('second_name') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="third_name" class="block mb-2 text-sm text-gray-700">{{ __('Third name') }}</label>
                            <input type="text" id="third_name" name="third_name" value="{{ old('third_name') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="fourth_name" class="block mb-2 text-sm text-gray-700">{{ __('Fourth name') }}</label>
                            <input type="text" id="fourth_name" name="fourth_name" value="{{ old('fourth_name') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="email" class="block mb-2 text-sm text-gray-700">{{ __('Email') }}</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="mobile_number" class="block mb-2 text-sm text-gray-700">{{ __('Mobile number') }}</label>
                            <input type="text" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block mb-2 text-sm text-gray-700">{{ __('Date of birth') }}</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="raqam_qawmy" class="block mb-2 text-sm text-gray-700">{{ __('National ID') }}</label>
                            <input type="text" id="raqam_qawmy" name="raqam_qawmy" value="{{ old('raqam_qawmy') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div class="md:col-span-2 relative">
                            <label for="person_search" class="block mb-2 text-sm text-gray-700">{{ __('Linked person') }}</label>

                            <input type="text" id="person_search" autocomplete="off" placeholder="ابحث واختر الشخص..."
                                value="{{ old('person_name') }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-blue-500 focus:outline-none">

                            <input type="hidden" id="person_id" name="person_id" value="{{ old('person_id') }}">

                            <div id="person_results"
                                class="hidden absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach ($persons as $person)
                                    <div class="person-option px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-slate-100 last:border-b-0"
                                        data-id="{{ $person->PersonID }}" data-name="{{ $person->FullName }}">
                                        <div class="text-sm font-medium text-slate-800">
                                            {{ $person->FullName }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            ID: {{ $person->PersonID }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @error('person_id')
                                <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-between gap-4 mt-8">
                    <a href="{{ route('guests.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">{{ __('Cancel') }}</a>

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('person_search');
            const hiddenInput = document.getElementById('person_id');
            const resultsBox = document.getElementById('person_results');
            const options = Array.from(document.querySelectorAll('.person-option'));

            function showResults() {
                resultsBox.classList.remove('hidden');
            }

            function hideResults() {
                resultsBox.classList.add('hidden');
            }

            function filterResults() {
                const keyword = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                options.forEach(option => {
                    const name = (option.dataset.name || '').toLowerCase();
                    const id = (option.dataset.id || '').toLowerCase();

                    if (keyword === '' || name.includes(keyword) || id.includes(keyword)) {
                        option.style.display = '';
                        visibleCount++;
                    } else {
                        option.style.display = 'none';
                    }
                });

                if (visibleCount > 0) {
                    showResults();
                } else {
                    hideResults();
                }
            }

            options.forEach(option => {
                option.addEventListener('click', function() {
                    searchInput.value = this.dataset.name;
                    hiddenInput.value = this.dataset.id;
                    hideResults();
                });
            });

            searchInput.addEventListener('focus', function() {
                filterResults();
            });

            searchInput.addEventListener('input', function() {
                hiddenInput.value = '';
                filterResults();
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    hideResults();
                }
            });
        });
    </script>
@endpush
