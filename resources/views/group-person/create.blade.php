@extends('layouts.app' , ['pageTitle' => "إدارة الأشخاص في المجموعة"])

@section('content')
<div class="container mx-auto px-4 py-8" dir="rtl">
    <div class="bg-white shadow-lg rounded-lg p-8 mb-8 border-2 border-blue-300">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center" style="font-family: 'Cairo', sans-serif;">
            ربط الأشخاص بالمجموعة الكشفية
        </h2>

        <form method="POST" action="{{ route('group-person.insert') }}" class="space-y-6">
            @csrf

            <!-- المجموعة الكشفية -->
            <div>
                <label for="group_id" class="block text-sm font-semibold text-gray-700 mb-2"
                       style="font-family: 'Cairo', sans-serif; text-align: right;">
                    اختر المجموعة الكشفية
                </label>
                <select name="group_id" id="group_id" required
                    class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-400 focus:ring focus:ring-blue-100 text-right"
                    style="font-family: 'Cairo', sans-serif;">
                    <option value="" disabled selected>اختر المجموعة الكشفية</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->GroupID }}">{{ $group->GroupInfo }}</option>
                    @endforeach
                </select>
            </div>

            <!-- الأشخاص (searchable & multi-select) -->
            <div>
                <label for="person_search" class="block text-sm font-semibold text-gray-700 mb-2"
                       style="font-family: 'Cairo', sans-serif;">
                    اختر الاسم أو الكود الخاص بالشخص/الأشخاص للربط
                </label>
                <div class="relative">
                    <input type="text" id="person_search"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 
                               focus:border-blue-500 text-sm py-2 px-4 pr-10"
                        placeholder="ابحث واختر الأشخاص..." autocomplete="off">

                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Hidden selected values -->
                    <div id="selected_persons" class="flex flex-wrap gap-2 mt-2"></div>
                    <input type="hidden" id="person_ids" name="person_id[]">

                    <!-- Dropdown -->
                    <div id="dropdown"
                        class="absolute z-20 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 
                               max-h-60 overflow-auto hidden">
                        @foreach($persons as $person)
                            <div class="dropdown-option px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                                 data-value="{{ $person->PersonID }}"
                                 data-text="{{ $person->FullName }}">
                                {{ $person->FullName }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">يمكنك اختيار أكثر من شخص واحد</p>
            </div>

            <!-- الدور/المهمة -->
            <div>
                <label for="group_role_id" class="block text-sm font-semibold text-gray-700 mb-2"
                       style="font-family: 'Cairo', sans-serif;">
                    اختر دور الشخص في المجموعة
                </label>
                <select id="group_role_id" name="group_role_id" required
                    class="w-full h-12 px-4 text-sm border rounded-lg border-slate-200 text-slate-600 focus:border-blue-400 focus:ring focus:ring-blue-100 text-right">
                    <option disabled selected value="">اختر دور الشخص</option>
                    @foreach($groupRoles as $groupRole)
                        <option value="{{ $groupRole->GroupRoleID }}">{{ $groupRole->GroupRoleName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit -->
            <div class="flex justify-center">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-semibold rounded-full bg-blue-500 text-white hover:bg-blue-600 transition duration-300">
                    إدخال
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('person_search');
    const dropdown = document.getElementById('dropdown');
    const options = dropdown.querySelectorAll('.dropdown-option');
    const selectedContainer = document.getElementById('selected_persons');
    const hiddenInput = document.getElementById('person_ids');
    let selectedValues = [];

    function updateHiddenInput() {
        hiddenInput.value = selectedValues.join(',');
    }

    function addTag(value, text) {
        if (!selectedValues.includes(value)) {
            selectedValues.push(value);
            updateHiddenInput();

            const tag = document.createElement('div');
            tag.className = 'flex items-center bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm';
            tag.innerHTML = `${text} <span class="ml-2 cursor-pointer text-red-500">×</span>`;
            tag.querySelector('span').addEventListener('click', () => {
                selectedContainer.removeChild(tag);
                selectedValues = selectedValues.filter(v => v !== value);
                updateHiddenInput();
            });
            selectedContainer.appendChild(tag);
        }
    }

    function filterOptions() {
        const term = searchInput.value.toLowerCase();
        let hasVisible = false;
        options.forEach(option => {
            const text = option.getAttribute('data-text').toLowerCase();
            if (text.includes(term)) {
                option.style.display = 'block';
                hasVisible = true;
            } else {
                option.style.display = 'none';
            }
        });
        dropdown.classList.toggle('hidden', !hasVisible);
    }

    searchInput.addEventListener('focus', () => dropdown.classList.remove('hidden'));
    searchInput.addEventListener('input', filterOptions);

    options.forEach(option => {
        option.addEventListener('click', function() {
            addTag(this.getAttribute('data-value'), this.getAttribute('data-text'));
            searchInput.value = '';
            dropdown.classList.add('hidden');
        });
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== searchInput) {
            dropdown.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection
