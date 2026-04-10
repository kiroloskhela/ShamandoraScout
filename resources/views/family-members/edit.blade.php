@extends('layouts.app', ['pageTitle' => 'تعديل فرد أسرة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-emerald-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل فرد أسرة</h2>
                <p class="text-sm text-gray-500 mt-2">يمكنك تعديل البيانات والروابط مع الأشخاص</p>
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

            <form method="POST" action="{{ route('family-members.update', $familyMember->FamilyID) }}">
                @csrf

                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">بيانات فرد الأسرة</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block mb-2 text-sm text-gray-700">الاسم الأول</label>
                            <input type="text" id="first_name" name="first_name"
                                value="{{ old('first_name', $familyMember->FirstName) }}" required
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="second_name" class="block mb-2 text-sm text-gray-700">الاسم الثاني</label>
                            <input type="text" id="second_name" name="second_name"
                                value="{{ old('second_name', $familyMember->SecondName) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="third_name" class="block mb-2 text-sm text-gray-700">الاسم الثالث</label>
                            <input type="text" id="third_name" name="third_name"
                                value="{{ old('third_name', $familyMember->ThirdName) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="fourth_name" class="block mb-2 text-sm text-gray-700">الاسم الرابع</label>
                            <input type="text" id="fourth_name" name="fourth_name"
                                value="{{ old('fourth_name', $familyMember->FourthName) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="email" class="block mb-2 text-sm text-gray-700">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $familyMember->Email) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="mobile_number" class="block mb-2 text-sm text-gray-700">رقم الموبايل</label>
                            <input type="text" id="mobile_number" name="mobile_number"
                                value="{{ old('mobile_number', $familyMember->MobileNumber) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block mb-2 text-sm text-gray-700">تاريخ الميلاد</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                value="{{ old('date_of_birth', $familyMember->DateOfBirth) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="raqam_qawmy" class="block mb-2 text-sm text-gray-700">الرقم القومي</label>
                            <input type="text" id="raqam_qawmy" name="raqam_qawmy"
                                value="{{ old('raqam_qawmy', $familyMember->RaqamQawmy) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">الربط بالأشخاص</h3>
                            <p class="text-sm text-gray-500">اختياري — يمكنك إضافة أو حذف أكثر من ربط</p>
                        </div>

                        <button type="button" id="add-assignment-row"
                            class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                            + إضافة ربط
                        </button>
                    </div>

                    <div id="assignment-rows" class="space-y-4">
                        @php
                            $oldPersonIds = old('person_ids');
                            $oldRelationIds = old('relation_type_ids');

                            if ($oldPersonIds !== null || $oldRelationIds !== null) {
                                $personValues = $oldPersonIds ?? [''];
                                $relationValues = $oldRelationIds ?? [''];
                                $rowsCount = max(count($personValues), count($relationValues));
                            } else {
                                $personValues = $assignments->pluck('PersonID')->toArray();
                                $relationValues = $assignments->pluck('RelationTypeID')->toArray();
                                $rowsCount = max(count($personValues), count($relationValues), 1);
                                if (empty($personValues)) {
                                    $personValues = [''];
                                }
                                if (empty($relationValues)) {
                                    $relationValues = [''];
                                }
                            }
                        @endphp

                        @for ($i = 0; $i < $rowsCount; $i++)
                            <div class="assignment-row p-4 border border-slate-200 rounded-lg bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                    <div class="md:col-span-5">
                                        <label class="block mb-2 text-sm text-gray-700">بحث عن الشخص</label>
                                        <input type="text"
                                            class="person-search w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none"
                                            placeholder="اكتب اسم الشخص للبحث...">
                                    </div>

                                    <div class="md:col-span-4">
                                        <label class="block mb-2 text-sm text-gray-700">الشخص</label>
                                        <select name="person_ids[]"
                                            class="person-select w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                                            <option value="">-- اختر الشخص --</option>
                                            @foreach ($persons as $person)
                                                <option value="{{ $person->PersonID }}"
                                                    {{ ($personValues[$i] ?? '') == $person->PersonID ? 'selected' : '' }}>
                                                    {{ $person->FullName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm text-gray-700">صلة القرابة</label>
                                        <select name="relation_type_ids[]"
                                            class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                                            <option value="">-- اختر --</option>
                                            @foreach ($relations as $relation)
                                                <option value="{{ $relation->RelationTypeID }}"
                                                    {{ ($relationValues[$i] ?? '') == $relation->RelationTypeID ? 'selected' : '' }}>
                                                    {{ $relation->RelationName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-1">
                                        <button type="button"
                                            class="remove-assignment-row w-full h-12 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition">
                                            حذف
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="flex justify-between gap-4 mt-8">
                    <a href="{{ route('family-members.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">
                        إلغاء
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assignmentRows = document.getElementById('assignment-rows');
            const addRowBtn = document.getElementById('add-assignment-row');

            const personsOptions = `
        <option value="">-- اختر الشخص --</option>
        @foreach ($persons as $person)
            <option value="{{ $person->PersonID }}">{{ $person->FullName }}</option>
        @endforeach
    `;

            const relationsOptions = `
        <option value="">-- اختر --</option>
        @foreach ($relations as $relation)
            <option value="{{ $relation->RelationTypeID }}">{{ $relation->RelationName }}</option>
        @endforeach
    `;

            function bindSearchForRow(row) {
                const searchInput = row.querySelector('.person-search');
                const select = row.querySelector('.person-select');

                if (!searchInput || !select) return;

                const originalOptions = Array.from(select.options).map(option => ({
                    value: option.value,
                    text: option.text,
                    selected: option.selected
                }));

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value !== '') {
                    searchInput.value = selectedOption.text;
                }

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.trim().toLowerCase();
                    const currentValue = select.value;

                    select.innerHTML = '';

                    originalOptions.forEach(option => {
                        const matches = option.text.toLowerCase().includes(searchTerm) || option
                            .value === '';

                        if (matches) {
                            const newOption = document.createElement('option');
                            newOption.value = option.value;
                            newOption.textContent = option.text;

                            if (option.value == currentValue) {
                                newOption.selected = true;
                            }

                            select.appendChild(newOption);
                        }
                    });
                });
            }

            function bindRemoveButtons() {
                document.querySelectorAll('.remove-assignment-row').forEach(button => {
                    button.onclick = function() {
                        const rows = document.querySelectorAll('.assignment-row');
                        if (rows.length > 1) {
                            this.closest('.assignment-row').remove();
                        } else {
                            const row = this.closest('.assignment-row');
                            row.querySelector('.person-search').value = '';
                            row.querySelector('.person-select').selectedIndex = 0;
                            row.querySelector('select[name="relation_type_ids[]"]').selectedIndex = 0;
                        }
                    };
                });
            }

            function addNewRow() {
                const row = document.createElement('div');
                row.className = 'assignment-row p-4 border border-slate-200 rounded-lg bg-gray-50';
                row.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label class="block mb-2 text-sm text-gray-700">بحث عن الشخص</label>
                    <input type="text" class="person-search w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none" placeholder="اكتب اسم الشخص للبحث...">
                </div>

                <div class="md:col-span-4">
                    <label class="block mb-2 text-sm text-gray-700">الشخص</label>
                    <select name="person_ids[]" class="person-select w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        ${personsOptions}
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm text-gray-700">صلة القرابة</label>
                    <select name="relation_type_ids[]" class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-emerald-500 focus:outline-none">
                        ${relationsOptions}
                    </select>
                </div>

                <div class="md:col-span-1">
                    <button type="button"
                        class="remove-assignment-row w-full h-12 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition">
                        حذف
                    </button>
                </div>
            </div>
        `;

                assignmentRows.appendChild(row);
                bindSearchForRow(row);
                bindRemoveButtons();
            }

            document.querySelectorAll('.assignment-row').forEach(row => bindSearchForRow(row));
            bindRemoveButtons();

            addRowBtn.addEventListener('click', addNewRow);
        });
    </script>
@endsection
