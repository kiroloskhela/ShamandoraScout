@extends('layouts.app', ['pageTitle' => __('Game details')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-blue-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تفاصيل اللعبة</h2>
            </div>

            <div class="space-y-6">

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">رقم اللعبة</label>
                    <div class="w-full min-h-12 px-4 py-3 border rounded-lg bg-gray-50 text-slate-700">
                        {{ $game->GameID }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">عنوان اللعبة</label>
                    <div class="w-full min-h-12 px-4 py-3 border rounded-lg bg-gray-50 text-slate-700">
                        {{ $game->Title }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">الوصف</label>
                    <div class="w-full px-4 py-3 border rounded-lg bg-gray-50 text-slate-700 whitespace-pre-line">
                        {{ $game->GameDescription }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">القوانين</label>
                    <div class="w-full px-4 py-3 border rounded-lg bg-gray-50 text-slate-700 whitespace-pre-line">
                        {{ $game->Rules ?: 'لا يوجد' }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">نظام النقاط</label>
                    <div class="w-full px-4 py-3 border rounded-lg bg-gray-50 text-slate-700 whitespace-pre-line">
                        {{ $game->PointSystem ?: 'لا يوجد' }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">{{ __('Age group') }}</label>
                    <div class="w-full min-h-12 px-4 py-3 border rounded-lg bg-gray-50 text-slate-700">
                        {{ $game->AgeGroup ?: 'لا يوجد' }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">الهدف</label>
                    <div class="w-full min-h-12 px-4 py-3 border rounded-lg bg-gray-50 text-slate-700">
                        {{ $game->Target ?: 'لا يوجد' }}
                    </div>
                </div>

                <div class="relative">
                    <label class="block mb-2 text-sm text-gray-700">الرابط المرجعي</label>
                    <div class="w-full min-h-12 px-4 py-3 border rounded-lg bg-gray-50 text-slate-700">
                        @if ($game->ReferenceLink)
                            <a href="{{ $game->ReferenceLink }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $game->ReferenceLink }}
                            </a>
                        @else
                            لا يوجد
                        @endif
                    </div>
                </div>

                <div class="flex justify-center gap-4">

                    <a href="{{ route('games.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                               rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">{{ __('Back') }}</a>
                </div>

            </div>
        </div>
    </div>
@endsection
