@extends('layouts.app', ['pageTitle' => 'تعديل لعبة'])

@section('content')
    <div class="flex justify-center px-4 py-8">
        <div class="w-full max-w-2xl rounded-2xl border border-blue-200 bg-white p-8 shadow-lg" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-800">تعديل اللعبة</h2>
                <p class="mt-2 text-sm text-gray-500">قم بتعديل بيانات اللعبة بشكل واضح ومنظم</p>
            </div>

            <form method="POST" action="{{ route('games.update', $game->GameID) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="title" class="mb-2 block text-sm font-medium text-gray-700">عنوان اللعبة</label>
                        <input type="text" id="title" name="title" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل عنوان اللعبة" value="{{ old('title', $game->Title ?? '') }}">
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700">الوصف</label>
                        <textarea id="description" name="description" required rows="4"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل وصف اللعبة">{{ old('description', $game->Description ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="rules" class="mb-2 block text-sm font-medium text-gray-700">القوانين</label>
                        <textarea id="rules" name="rules" rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل قوانين اللعبة">{{ old('rules', $game->Rules ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="point_system" class="mb-2 block text-sm font-medium text-gray-700">نظام النقاط</label>
                        <textarea id="point_system" name="point_system" rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل نظام النقاط">{{ old('point_system', $game->PointSystem ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="age_group" class="mb-2 block text-sm font-medium text-gray-700">الفئة العمرية</label>
                        <input type="text" id="age_group" name="age_group"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل الفئة العمرية" value="{{ old('age_group', $game->AgeGroup ?? '') }}">
                    </div>

                    <div>
                        <label for="target" class="mb-2 block text-sm font-medium text-gray-700">الهدف</label>
                        <input type="text" id="target" name="target"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل الهدف من اللعبة" value="{{ old('target', $game->Target ?? '') }}">
                    </div>

                    <div>
                        <label for="require_custody" class="mb-2 block text-sm font-medium text-gray-700">العهدة
                            المطلوبة</label>
                        <textarea id="require_custody" name="require_custody" rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل العهدة المطلوبة">{{ old('require_custody', $game->RequireCustody ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="reference_link" class="mb-2 block text-sm font-medium text-gray-700">الرابط
                            المرجعي</label>
                        <input type="text" id="reference_link" name="reference_link"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل الرابط المرجعي"
                            value="{{ old('reference_link', $game->ReferenceLink ?? '') }}">
                    </div>

                    <div class="flex justify-center gap-3 pt-2">
                        <a href="{{ route('games.index') }}"
                            class="inline-flex h-12 items-center justify-center rounded-full bg-slate-100 px-8 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                            رجوع
                        </a>

                        <button type="submit"
                            class="inline-flex h-12 items-center justify-center rounded-full bg-blue-600 px-8 text-sm font-medium text-white transition hover:bg-blue-700">
                            حفظ التعديلات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
