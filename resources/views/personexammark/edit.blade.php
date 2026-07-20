@extends('layouts.app', ['pageTitle' => __('Edit exam marks')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg border-2 border-green-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل درجات الامتحان</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('personexammark.updates', $mark->ExamMarkID) }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">المخدوم</label>
                        <input type="text" value="{{ $mark->PersonName }} - ({{ $mark->PersonID }})" readonly
                            class="w-full h-12 px-4 border rounded-lg bg-gray-50 text-right border-slate-200 text-slate-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="qetaa_id" class="block mb-2 text-sm text-gray-700">القطعة (وقت الامتحان)</label>
                        <select id="qetaa_id" name="qetaa_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                            @foreach ($qetaas as $q)
                                <option value="{{ $q->QetaaID }}" @selected(old('qetaa_id', $mark->QetaaID) == $q->QetaaID)>
                                    {{ $q->QetaaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sana_marhala_id" class="block mb-2 text-sm text-gray-700">سنة المرحلة (وقت الامتحان)</label>
                        <select id="sana_marhala_id" name="sana_marhala_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                            @foreach ($sanaMarhalas as $sm)
                                <option value="{{ $sm->SanaMarhalaID }}" @selected(old('sana_marhala_id', $mark->SanaMarhalaID) == $sm->SanaMarhalaID)>
                                    {{ $sm->SanaMarhalaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="theoretical_mark" class="block mb-2 text-sm text-gray-700">درجة النظري</label>
                            <input type="number" id="theoretical_mark" name="theoretical_mark" required min="0" max="999" step="1"
                                value="{{ old('theoretical_mark', $mark->TheoreticalMark) }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label for="practical_mark" class="block mb-2 text-sm text-gray-700">درجة العملي</label>
                            <input type="number" id="practical_mark" name="practical_mark" required min="0" max="999" step="1"
                                value="{{ old('practical_mark', $mark->PracticalMark) }}"
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="exam_date" class="block mb-2 text-sm text-gray-700">تاريخ الامتحان</label>
                        <input type="date" id="exam_date" name="exam_date" required
                            value="{{ old('exam_date', \Illuminate\Support\Str::of($mark->ExamDate)->before(' ')) }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="note" class="block mb-2 text-sm text-gray-700">{{ __('Note (optional)') }}</label>
                        <textarea id="note" name="note" rows="2"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none">{{ old('note', $mark->Note) }}</textarea>
                    </div>

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('personexammark.index') }}"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 transition">{{ __('Back') }}</a>
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition">
                            حفظ التعديل
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
