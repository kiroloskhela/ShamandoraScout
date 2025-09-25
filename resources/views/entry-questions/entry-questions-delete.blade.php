@extends('layouts.app', ['pageTitle' => 'حذف سؤال'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-red-300" dir="rtl">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تأكيد حذف السؤال</h2>
                <p class="text-sm text-gray-500 mt-1">هل أنت متأكد أنك تريد حذف هذا السؤال؟ هذا الإجراء لا يمكن التراجع عنه.
                </p>
            </div>

            <!-- Question summary -->
            <div class="rounded-lg border border-slate-200">
                <dl class="divide-y divide-slate-200">

                    <div class="px-4 py-3 grid grid-cols-4 gap-4">
                        <dt class="text-sm text-slate-500 col-span-1">رقم السؤال</dt>
                        <dd class="text-sm text-slate-800 col-span-3">{{ $entryQuestions->QuestionID }}</dd>
                    </div>

                    <div class="px-4 py-3 grid grid-cols-4 gap-4">
                        <dt class="text-sm text-slate-500 col-span-1">القطاع</dt>
                        <dd class="text-sm text-slate-800 col-span-3">{{ $entryQuestions->QetaaName }}</dd>
                    </div>

                    <div class="px-4 py-3 grid grid-cols-4 gap-4">
                        <dt class="text-sm text-slate-500 col-span-1">نوع الإجابة</dt>
                        <dd class="text-sm text-slate-800 col-span-3">{{ $entryQuestions->QuestionTypeInArabicWords }}</dd>
                    </div>

                    <div class="px-4 py-3 grid grid-cols-4 gap-4">
                        <dt class="text-sm text-slate-500 col-span-1">نص السؤال</dt>
                        <dd class="text-sm text-slate-800 col-span-3">{{ $entryQuestions->QuestionText }}</dd>
                    </div>

                    <div class="px-4 py-3 grid grid-cols-4 gap-4">
                        <dt class="text-sm text-slate-500 col-span-1">السؤال مطلوب؟</dt>
                        <dd class="text-sm text-slate-800 col-span-3">
                            {{ (int) $entryQuestions->IsRequired === 1 ? 'نعم' : 'لا' }}</dd>
                    </div>

                    @php
                        $choices =
                            trim((string) ($entryQuestions->MCAnswer ?? '')) !== ''
                                ? explode('|', $entryQuestions->MCAnswer)
                                : [];
                    @endphp

                    @if (!empty($choices))
                        <div class="px-4 py-3 grid grid-cols-4 gap-4">
                            <dt class="text-sm text-slate-500 col-span-1">الاختيارات</dt>
                            <dd class="text-sm text-slate-800 col-span-3">
                                <ul class="list-disc pr-5 space-y-1">
                                    @foreach ($choices as $i => $c)
                                        <li><span class="text-slate-500">({{ $i + 1 }})</span> {{ $c }}
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif

                </dl>
            </div>

            <!-- Confirm actions -->
            <div class="mt-6 flex items-center justify-center gap-3">
                <form method="POST" action="{{ route('entry-questions.destroy', $entryQuestions->QuestionID) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center justify-center h-11 px-6 rounded-full bg-red-600 text-white hover:bg-red-700 text-sm font-medium">
                        نعم، احذف
                    </button>
                </form>

                <a href="{{ route('entry-questions.index') }}"
                    class="inline-flex items-center justify-center h-11 px-6 rounded-full bg-red-50 text-red-700 hover:bg-red-100 text-sm font-medium">
                    إلغاء
                </a>
            </div>

        </div>
    </div>
@endsection
