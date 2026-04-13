@extends('layouts.app', ['pageTitle' => 'تعديل لعبة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-emerald-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل لعبة</h2>
            </div>

            <form method="POST" action="{{ route('games.updates', $game->GameID) }}">
                @csrf
                <div class="space-y-6">

                    <div class="relative">
                        <label for="title" class="block mb-2 text-sm text-gray-700">عنوان اللعبة</label>
                        <input type="text" id="title" name="title" required value="{{ $game->Title }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل عنوان اللعبة">
                    </div>

                    <div class="relative">
                        <label for="description" class="block mb-2 text-sm text-gray-700">الوصف</label>
                        <textarea id="description" name="description" required
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            rows="4" placeholder="ادخل وصف اللعبة">{{ $game->GameDescription }}</textarea>
                    </div>

                    <div class="relative">
                        <label for="rules" class="block mb-2 text-sm text-gray-700">القوانين</label>
                        <textarea id="rules" name="rules"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            rows="3" placeholder="ادخل قوانين اللعبة">{{ $game->Rules }}</textarea>
                    </div>

                    <div class="relative">
                        <label for="point_system" class="block mb-2 text-sm text-gray-700">نظام النقاط</label>
                        <textarea id="point_system" name="point_system"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            rows="3" placeholder="ادخل نظام النقاط">{{ $game->PointSystem }}</textarea>
                    </div>

                    <div class="relative">
                        <label for="age_group" class="block mb-2 text-sm text-gray-700">الفئة العمرية</label>
                        <input type="text" id="age_group" name="age_group" value="{{ $game->AgeGroup }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل الفئة العمرية">
                    </div>

                    <div class="relative">
                        <label for="target" class="block mb-2 text-sm text-gray-700">الهدف</label>
                        <input type="text" id="target" name="target" value="{{ $game->Target }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل الهدف من اللعبة">
                    </div>

                    <div class="relative">
                        <label for="require_custody" class="block mb-2 text-sm text-gray-700">العهده المطلوبة</label>
                        <textarea id="require_custody" name="require_custody"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل العهده المطلوبة">{{ $game->RequireCustody }}</textarea>
                    </div>

                    <div class="relative">
                        <label for="reference_link" class="block mb-2 text-sm text-gray-700">الرابط المرجعي</label>
                        <input type="text" id="reference_link" name="reference_link" value="{{ $game->ReferenceLink }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-emerald-500 focus:outline-none"
                            placeholder="ادخل الرابط المرجعي">
                    </div>

                    <div class="flex justify-center gap-4">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                                   rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                            تحديث اللعبة
                        </button>

                        <a href="{{ route('games.index') }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                                   rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">
                            رجوع
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
