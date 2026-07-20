@extends('layouts.app', ['pageTitle' => __('Upload new lecture')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">رفع محاضرة جديدة</h2>
                <p class="text-sm text-gray-500 mt-1">أدخل بيانات المحاضرة ثم ارفع الملف (PDF / DOC / DOCX)</p>
            </div>

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="leading-5">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('curricula.insert') }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">

                    <!-- Curricula Name -->
                    <div class="relative">
                        <label for="curricula_name" class="block mb-2 text-sm text-gray-700">اسم المحاضرة</label>
                        <input type="text" id="curricula_name" name="curricula_name" value="{{ old('curricula_name') }}"
                            required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                               focus:border-blue-500 focus:outline-none"
                            placeholder="اكتب اسم المحاضرة">
                    </div>

                    <!-- Category -->
                    <div class="relative">
                        <label for="curricula_category_id" class="block mb-2 text-sm text-gray-700">التصنيف</label>
                        <select id="curricula_category_id" name="curricula_category_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                               focus:border-blue-500 focus:outline-none">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->CurriculaCategoryID }}"
                                    {{ $cat->CurriculaCategoryID == old('curricula_category_id') ? 'selected' : '' }}>
                                    {{ $cat->CurriculaCategoryName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Marhala -->
                    <div class="relative">
                        <label for="marhala_id" class="block mb-2 text-sm text-gray-700">{{ __('Stage') }}</label>
                        <select id="marhala_id" name="marhala_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                               focus:border-blue-500 focus:outline-none">
                            @foreach ($marhalat as $m)
                                <option value="{{ $m->MarhalaID }}"
                                    {{ $m->MarhalaID == old('marhala_id') ? 'selected' : '' }}>
                                    {{ $m->MarhalaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- File (PDF/DOC/DOCX) -->
                    <div class="relative">
                        <label for="curricula_file" class="block mb-2 text-sm text-gray-700">ملف المحاضرة</label>

                        <!-- Dropzone-style wrapper (native input) -->
                        <label for="curricula_file"
                            class="flex flex-col items-center justify-center w-full h-28 px-4 border-2 border-dashed rounded-lg
                               text-slate-500 hover:text-slate-700 border-slate-200 hover:border-blue-300 cursor-pointer
                               transition">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V8m0 0l-2 2m2-2l2 2m4 8h4a2 2 0 002-2V9.5a2 2 0 00-.586-1.414l-3.5-3.5A2 2 0 0013.5 4H9a2 2 0 00-2 2v2" />
                            </svg>
                            <span class="text-sm">اسحب الملف هنا أو اضغط للاختيار</span>
                            <span class="text-xs text-gray-400 mt-1">PDF / DOC / DOCX • حد أقصى 10MB</span>
                        </label>

                        <input id="curricula_file" type="file" name="curricula_file"
                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required class="hidden">

                        {{-- Selected file name preview --}}
                        <p id="selected_file_name" class="mt-2 text-xs text-gray-500"></p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide
                               rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition">
                            رفع وحفظ
                        </button>
                        <a href="{{ route('curricula.index') }}"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium tracking-wide
                              rounded-full bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-700 transition">{{ __('Back') }}</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Tiny inline script to show selected file name --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('curricula_file');
            const nameEl = document.getElementById('selected_file_name');

            input.addEventListener('change', function() {
                if (input.files && input.files.length > 0) {
                    nameEl.textContent = 'الملف المختار: ' + input.files[0].name;
                } else {
                    nameEl.textContent = '';
                }
            });
        });
    </script>
@endsection
