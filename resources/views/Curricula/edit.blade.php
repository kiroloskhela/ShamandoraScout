@extends('layouts.app', ['pageTitle' => __('Edit lecture')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-green-300">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل محاضرة</h2>
                <p class="text-sm text-gray-500 mt-1">قم بتعديل بيانات المحاضرة ثم احفظ التغييرات</p>
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

            @if (session('status'))
                <div class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('curricula.update', $curriculum->CurriculaID) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="space-y-6">

                    <!-- Curricula Name -->
                    <div class="relative">
                        <label for="curricula_name" class="block mb-2 text-sm text-gray-700">اسم المحاضرة</label>
                        <input type="text" id="curricula_name" name="curricula_name"
                            value="{{ old('curricula_name', $curriculum->CurriculaName) }}" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                      focus:border-green-500 focus:outline-none">
                    </div>

                    <!-- Category -->
                    <div class="relative">
                        <label for="curricula_category_id" class="block mb-2 text-sm text-gray-700">التصنيف</label>
                        <select id="curricula_category_id" name="curricula_category_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                       focus:border-green-500 focus:outline-none">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->CurriculaCategoryID }}"
                                    {{ $cat->CurriculaCategoryID == old('curricula_category_id', $curriculum->CurriculaCategoryID) ? 'selected' : '' }}>
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
                                       focus:border-green-500 focus:outline-none">
                            @foreach ($marhalat as $m)
                                <option value="{{ $m->MarhalaID }}"
                                    {{ $m->MarhalaID == old('marhala_id', $curriculum->MarhalaID) ? 'selected' : '' }}>
                                    {{ $m->MarhalaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-center gap-3 pt-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide
                                   rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition">
                            تحديث وحفظ
                        </button>

                        <a href="{{ route('curricula.index') }}"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium tracking-wide
                                  rounded-full bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-700 transition">{{ __('Back') }}</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
