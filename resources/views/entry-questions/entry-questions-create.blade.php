@extends('layouts.app', ['pageTitle' => 'إضافة سؤال جديد'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-blue-300" dir="rtl"
            x-data="questionForm()">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة سؤال جديد</h2>
                <p class="text-sm text-gray-500 mt-1">اختر القطاع ونوع السؤال وأدخل التفاصيل المطلوبة</p>
            </div>

            <form method="POST" action="{{ route('entry-questions.insert') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Qetaa -->
                    <div>
                        <label for="qetaa_id" class="block mb-2 text-sm text-gray-700">القطاع الكشفي</label>
                        <select id="qetaa_id" name="qetaa_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="" selected disabled>اختر القطاع الكشفي</option>
                            @foreach ($qetaat as $qetaa)
                                <option value="{{ $qetaa->QetaaID }}">{{ $qetaa->QetaaName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Question Type -->
                    <div>
                        <label for="required_answer_type" class="block mb-2 text-sm text-gray-700">نوع السؤال</label>
                        <select id="required_answer_type" name="required_answer_type" x-model="type" @change="onTypeChange"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            required>
                            <option value="" selected disabled>اختر نوع السؤال</option>
                            @foreach ($questionTypes as $questionType)
                                <option value="{{ $questionType->QuestionType }}">
                                    {{ $questionType->QuestionTypeInArabicWords }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Question Text -->
                    <div>
                        <label for="question_text" class="block mb-2 text-sm text-gray-700">نص السؤال</label>
                        <textarea id="question_text" name="question_text" rows="3" required
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل نص السؤال المطلوب"></textarea>
                    </div>

                    <!-- Required? -->
                    <div class="flex items-center gap-3">
                        <input id="questionIsRequired" type="checkbox" name="questionIsRequired" checked
                            class="h-4 w-4 border-slate-300 rounded text-blue-600 focus:ring-blue-500">
                        <label for="questionIsRequired" class="text-sm text-gray-700">سؤال مطلوب (إجباري)</label>
                    </div>

                    <!-- Multiple Choice Config -->
                    <template x-if="type === 'MultipleChoice'">
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex flex-col md:flex-row md:items-center md:gap-4">
                                <div class="grow">
                                    <label class="block mb-2 text-sm text-gray-700">عدد الاختيارات (بحد أقصى 6)</label>
                                    <input type="number" min="1" max="6" x-model.number="choicesCount"
                                        @input="normalizeCount"
                                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                                        placeholder="أدخل عدد الاختيارات">
                                </div>
                                <button type="button" @click="buildChoices"
                                    class="mt-3 md:mt-7 inline-flex items-center justify-center h-11 px-5 text-sm font-medium rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                                    توليد حقول الاختيارات
                                </button>
                            </div>

                            <!-- Hidden field required by controller -->
                            <input type="hidden" name="memberA" :value="choices.length">

                            <!-- Choices fields with names choice1..choiceN -->
                            <div class="mt-4 space-y-3" x-show="choices.length">
                                <template x-for="(choice, idx) in choices" :key="idx">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 text-sm"
                                            x-text="idx + 1"></span>
                                        <input type="text"
                                            class="w-full h-11 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-blue-500 focus:outline-none"
                                            :name="'choice' + (idx + 1)" :placeholder="'اختيار رقم ' + (idx + 1)"
                                            x-model="choices[idx]" required>
                                        <button type="button" @click="removeChoice(idx)"
                                            class="h-10 px-3 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm">
                                            إزالة
                                        </button>
                                    </div>
                                </template>
                                <div class="flex justify-end">
                                    <button type="button" @click="addChoice" :disabled="choices.length >= 6"
                                        class="mt-1 h-10 px-4 rounded-full bg-green-50 text-green-600 hover:bg-green-100 text-sm disabled:opacity-50">
                                        إضافة اختيار
                                    </button>
                                </div>
                            </div>


                        </div>
                    </template>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                            تأكيد إدخال السؤال
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function questionForm() {
            return {
                type: '', // 'MultipleChoice' or others
                choicesCount: 0,
                choices: [],
                onTypeChange() {
                    if (this.type !== 'MultipleChoice') {
                        this.choices = [];
                        this.choicesCount = 0;
                    }
                },
                normalizeCount() {
                    if (this.choicesCount > 6) this.choicesCount = 6;
                    if (this.choicesCount < 0) this.choicesCount = 0;
                },
                buildChoices() {
                    this.normalizeCount();
                    this.choices = Array.from({
                        length: this.choicesCount
                    }, (_, i) => this.choices[i] || '');
                },
                addChoice() {
                    if (this.choices.length < 6) this.choices.push('');
                },
                removeChoice(idx) {
                    this.choices.splice(idx, 1);
                    this.choicesCount = this.choices.length;
                },
            }
        }
    </script>
@endsection
