@extends('layouts.app', ['pageTitle' => 'إدارة الأشخاص في المجموعة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-blue-300" dir="rtl">
            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">ربط الأشخاص بالمجموعة الكشفية</h2>
            </div>

            <form method="POST" action="{{ route('group-person.insert') }}">
                @csrf
                <div class="space-y-6">
                    <!-- المجموعة الكشفية -->
                    <div class="relative">
                        <label for="group_id" class="block mb-2 text-sm text-gray-700">اختر المجموعة الكشفية</label>
                        <select name="group_id" id="group_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 
                               focus:border-blue-500 focus:outline-none">
                            <option value="" disabled selected>اختر المجموعة الكشفية</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->GroupID }}">{{ $group->GroupInfo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الأشخاص (searchable & multi-select) -->
                    <div class="relative">
                        <label for="person_search" class="block mb-2 text-sm text-gray-700">
                            اختر الاسم أو الكود الخاص بالشخص/الأشخاص للربط
                        </label>

                        <!-- Search input -->
                        <div class="relative">
                            <input type="text" id="person_search" autocomplete="off"
                                class="w-full h-12 px-4 pr-10 border rounded-lg text-right border-slate-200 text-slate-600 
                                   focus:border-blue-500 focus:outline-none"
                                placeholder="ابحث واختر الأشخاص...">

                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Selected chips -->
                            <div id="selected_persons" class="flex flex-wrap gap-2 mt-2"></div>

                            <!-- Dropdown -->
                            <div id="dropdown"
                                class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 
                                   max-h-60 overflow-auto hidden">
                                @foreach ($persons as $person)
                                    <button type="button"
                                        class="dropdown-option w-full text-right px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                                        data-value="{{ $person->PersonID }}" data-text="{{ $person->FullName }}">
                                        {{ $person->FullName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">يمكنك اختيار أكثر من شخص واحد</p>
                    </div>

                    <!-- الدور/المهمة -->
                    <div class="relative">
                        <label for="group_role_id" class="block mb-2 text-sm text-gray-700">اختر دور الشخص في
                            المجموعة</label>
                        <select id="group_role_id" name="group_role_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 
                               focus:border-blue-500 focus:outline-none">
                            <option disabled selected value="">اختر دور الشخص</option>
                            @foreach ($groupRoles as $groupRole)
                                <option value="{{ $groupRole->GroupRoleID }}">{{ $groupRole->GroupRoleName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                               rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            إدخال
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('person_search');
                const dropdown = document.getElementById('dropdown');
                const selectedContainer = document.getElementById('selected_persons');

                // نبقي مصفوفة للقيم المُختارة لتفادي التكرار
                const selectedValues = new Set();

                // إظهار/إخفاء وإعادة ترشيح العناصر
                function filterOptions() {
                    const term = (searchInput.value || '').toLowerCase().trim();
                    const options = dropdown.querySelectorAll('.dropdown-option');
                    let visibleCount = 0;

                    options.forEach(option => {
                        const text = option.getAttribute('data-text').toLowerCase();
                        const isMatch = term === '' || text.includes(term);
                        option.classList.toggle('hidden', !isMatch);
                        if (isMatch) visibleCount++;
                    });

                    dropdown.classList.toggle('hidden', visibleCount === 0);
                }

                // إنشاء input مخفي جديد للـ Laravel name="person_id[]"
                function createHiddenInput(value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'person_id[]';
                    input.value = value;
                    return input;
                }

                // إضافة chip لشخص مختار
                function addChip(value, text) {
                    if (selectedValues.has(value)) return;

                    selectedValues.add(value);

                    const chip = document.createElement('div');
                    chip.className = 'flex items-center gap-2 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm';

                    const title = document.createElement('span');
                    title.textContent = text;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'leading-none text-red-500 hover:text-red-600';
                    removeBtn.setAttribute('aria-label', 'حذف');
                    removeBtn.textContent = '×';

                    // hidden input
                    const hidden = createHiddenInput(value);

                    removeBtn.addEventListener('click', () => {
                        selectedValues.delete(value);
                        chip.remove();
                        hidden.remove();
                    });

                    chip.appendChild(title);
                    chip.appendChild(removeBtn);
                    selectedContainer.appendChild(chip);
                    // نضيف الـ input المخفي بجانب الكارد (ضمن نفس النموذج)
                    selectedContainer.closest('form').appendChild(hidden);
                }

                // أحداث الإدخال
                searchInput.addEventListener('focus', () => {
                    filterOptions();
                    dropdown.classList.remove('hidden');
                });

                searchInput.addEventListener('input', filterOptions);

                // اختيار عنصر من القائمة
                dropdown.addEventListener('click', function(e) {
                    const btn = e.target.closest('.dropdown-option');
                    if (!btn) return;

                    addChip(btn.getAttribute('data-value'), btn.getAttribute('data-text'));
                    searchInput.value = '';
                    dropdown.classList.add('hidden');
                    searchInput.focus();
                });

                // إغلاق القائمة عند النقر خارجها
                document.addEventListener('click', function(e) {
                    const isInside = dropdown.contains(e.target) || e.target === searchInput;
                    if (!isInside) dropdown.classList.add('hidden');
                });

                // دعم لوحة المفاتيح: سهميْن + Enter/Escape
                let activeIndex = -1;

                function moveActive(delta) {
                    const items = Array.from(dropdown.querySelectorAll('.dropdown-option:not(.hidden)'));
                    if (items.length === 0) return;

                    activeIndex = (activeIndex + delta + items.length) % items.length;
                    items.forEach((el, i) => el.classList.toggle('bg-blue-50', i === activeIndex));
                    items[activeIndex].scrollIntoView({
                        block: 'nearest'
                    });
                }

                searchInput.addEventListener('keydown', (e) => {
                    if (dropdown.classList.contains('hidden')) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        moveActive(1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        moveActive(-1);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const items = Array.from(dropdown.querySelectorAll('.dropdown-option:not(.hidden)'));
                        if (items[activeIndex]) {
                            items[activeIndex].click();
                            activeIndex = -1;
                        }
                    } else if (e.key === 'Escape') {
                        dropdown.classList.add('hidden');
                        activeIndex = -1;
                    }
                });
            });
        </script>
    @endpush
@endsection
