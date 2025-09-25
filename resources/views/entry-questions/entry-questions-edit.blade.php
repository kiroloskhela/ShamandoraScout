{{-- resources/views/entry-questions/entry-questions-edit.blade.php --}}
@extends('layouts.app', ['pageTitle' => 'تعديل سؤال'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-emerald-300" dir="rtl">

            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل سؤال</h2>
                <p class="text-sm text-gray-500 mt-1">يمكنك تغيير القطاع ونص السؤال وتعديل الاختيارات</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700 text-sm">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="editQuestionForm" class="space-y-6" method="POST"
                action="{{ route('entry-questions.update', $entryQuestion->QuestionID) }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">نوع السؤال</label>
                        <input type="text" value="{{ $entryQuestion->QuestionTypeInArabicWords }}" disabled
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 bg-slate-50">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">القطاع الحالي</label>
                        <input type="text" value="{{ $entryQuestion->QetaaName }}" disabled
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 bg-slate-50">
                    </div>
                </div>

                <div>
                    <label for="qetaa_id" class="block mb-2 text-sm text-gray-700">اختر قطاعًا جديدًا (اختياري)</label>
                    <select id="qetaa_id" name="qetaa_id" required
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none">
                        @foreach ($qetaat as $qetaa)
                            <option value="{{ $qetaa->QetaaID }}"
                                {{ $qetaa->QetaaID == $qetaaSelected->QetaaID ? 'selected' : '' }}>
                                {{ $qetaa->QetaaName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="question_text" class="block mb-2 text-sm text-gray-700">نص السؤال</label>
                    <textarea id="question_text" name="question_text" rows="3" required
                        class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none"
                        placeholder="ادخل نص السؤال المطلوب">{{ old('question_text', $entryQuestion->QuestionText) }}</textarea>
                </div>

                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="questionNotToBeShown"
                            {{ $entryQuestion->NotToBeShown == 1 ? 'checked' : '' }}
                            class="h-4 w-4 border-slate-300 rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">إخفاء السؤال</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="questionIsRequired"
                            {{ $entryQuestion->IsRequired == 1 ? 'checked' : '' }}
                            class="h-4 w-4 border-slate-300 rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">سؤال مطلوب (إجباري)</span>
                    </label>
                </div>

                @if ($entryQuestion->RequiredAnswerType == 'MultipleChoice')
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-700">الاختيارات (من 1 إلى 6)</p>
                                <p class="text-xs text-slate-500 mt-1">يمكنك إضافة أو إزالة اختيارات. كل اختيار ظاهر يجب أن
                                    يحتوي على قيمة قبل الحفظ.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="addChoiceBtn"
                                    class="h-11 px-4 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 text-sm disabled:opacity-50">
                                    + إضافة اختيار
                                </button>
                            </div>
                        </div>

                        <!-- hidden count field that controller reads -->
                        <input type="hidden" id="answers" name="answers"
                            value="{{ max(1, count($arrayOfMCAnswers ?? [])) }}">

                        <!-- choices list -->
                        <div id="choicesList" class="mt-4 space-y-3">
                            @php
                                $current = $arrayOfMCAnswers ?? [];
                                if (count($current) === 0) {
                                    $current = [''];
                                }
                                $current = array_slice($current, 0, 6);
                            @endphp

                            @foreach ($current as $i => $answer)
                                <div class="flex items-center gap-3 choice-row">
                                    <span
                                        class="index-badge inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 text-sm">{{ $loop->iteration }}</span>
                                    <input type="text"
                                        class="choice-input w-full h-11 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none"
                                        name="answer{{ $loop->iteration }}"
                                        placeholder="اختيار رقم {{ $loop->iteration }}" value="{{ $answer }}"
                                        required>
                                    <button type="button"
                                        class="remove-btn h-10 px-3 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm">
                                        إزالة
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- No Alpine. Pure vanilla JS below. --}}
    <script>
        (function() {
            const maxChoices = 6;
            const list = document.getElementById('choicesList');
            const answersField = document.getElementById('answers');
            const addBtn = document.getElementById('addChoiceBtn');
            const form = document.getElementById('editQuestionForm');

            if (!list) return; // not MultipleChoice

            function renumber() {
                const rows = Array.from(list.querySelectorAll('.choice-row'));
                rows.forEach((row, idx) => {
                    const n = idx + 1;
                    row.querySelector('.index-badge').textContent = n;
                    const input = row.querySelector('.choice-input');
                    input.name = 'answer' + n;
                    input.placeholder = 'اختيار رقم ' + n;
                });
                answersField.value = rows.length;
                // enable/disable add/remove based on count
                if (addBtn) addBtn.disabled = rows.length >= maxChoices;
                rows.forEach((row) => {
                    const remove = row.querySelector('.remove-btn');
                    remove.disabled = rows.length <= 1; // keep at least 1
                    remove.classList.toggle('opacity-50', remove.disabled);
                    remove.classList.toggle('cursor-not-allowed', remove.disabled);
                });
            }

            function addChoice(value = '') {
                const count = list.querySelectorAll('.choice-row').length;
                if (count >= maxChoices) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-center gap-3 choice-row';

                const badge = document.createElement('span');
                badge.className =
                    'index-badge inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 text-sm';
                badge.textContent = 'X'; // will be set in renumber
                wrapper.appendChild(badge);

                const input = document.createElement('input');
                input.type = 'text';
                input.className =
                    'choice-input w-full h-11 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none';
                input.required = true;
                input.value = value || '';
                wrapper.appendChild(input);

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'remove-btn h-10 px-3 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm';
                remove.textContent = 'إزالة';
                remove.addEventListener('click', () => {
                    const rows = list.querySelectorAll('.choice-row');
                    if (rows.length <= 1) return; // keep at least 1
                    wrapper.remove();
                    renumber();
                });
                wrapper.appendChild(remove);

                list.appendChild(wrapper);
                renumber();
            }

            // hook add button
            if (addBtn) {
                addBtn.addEventListener('click', () => addChoice(''));
            }

            // hook existing remove buttons
            list.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const rows = list.querySelectorAll('.choice-row');
                    if (rows.length <= 1) return; // keep at least 1
                    e.target.closest('.choice-row').remove();
                    renumber();
                });
            });

            // validate on submit: no empty visible inputs
            form.addEventListener('submit', (e) => {
                const inputs = list.querySelectorAll('.choice-input');
                for (const inp of inputs) {
                    if (!inp.value.trim()) {
                        e.preventDefault();
                        alert('من فضلك اكتب قيمة لكل اختيار ظاهر أو احذفه قبل الحفظ.');
                        inp.focus();
                        return;
                    }
                }
                // answers field already synced by renumber()
            });

            // initial sync
            renumber();

        })();
    </script>
@endsection
