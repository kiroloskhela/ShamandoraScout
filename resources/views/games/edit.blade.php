@extends('layouts.app', ['pageTitle' => __('Edit game')])

@section('content')
    <div class="flex justify-center px-4 py-8">
        <div class="w-full max-w-2xl rounded-2xl border border-blue-200 bg-white p-8 shadow-lg" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-800">{{ __('Edit game') }}</h2>
                <p class="mt-2 text-sm text-gray-500">{{ __('Edit the game details clearly and in an organized way') }}</p>
            </div>

            <form method="POST" action="{{ route('games.update', $game->GameID) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="title" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Game title') }}</label>
                        <input type="text" id="title" name="title" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter game title') }}" value="{{ old('title', $game->Title ?? '') }}">
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                        <textarea id="description" name="description" required rows="4"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter game description') }}">{{ old('description', $game->Description ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="rules" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Rules') }}</label>
                        <textarea id="rules" name="rules" rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter game rules') }}">{{ old('rules', $game->Rules ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="point_system" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Points system') }}</label>
                        <textarea id="point_system" name="point_system" rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter points system') }}">{{ old('point_system', $game->PointSystem ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="age_group" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Age group') }}</label>
                        <input type="text" id="age_group" name="age_group"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter age group') }}" value="{{ old('age_group', $game->AgeGroup ?? '') }}">
                    </div>

                    <div>
                        <label for="target" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Objective') }}</label>
                        <input type="text" id="target" name="target"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter the game objective') }}" value="{{ old('target', $game->Target ?? '') }}">
                    </div>

                    <div>
                        <label for="require_custody" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Required custody') }}</label>
                        <textarea id="require_custody" name="require_custody" rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter required custody items') }}">{{ old('require_custody', $game->RequireCustody ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="reference_link" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Reference link') }}</label>
                        <input type="text" id="reference_link" name="reference_link"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-right text-slate-700 focus:border-blue-500 focus:outline-none"
                            placeholder="{{ __('Enter reference link') }}"
                            value="{{ old('reference_link', $game->ReferenceLink ?? '') }}">
                    </div>

                    <div class="flex justify-center gap-3 pt-2">
                        <a href="{{ route('games.index') }}"
                            class="inline-flex h-12 items-center justify-center rounded-full bg-slate-100 px-8 text-sm font-medium text-slate-700 transition hover:bg-slate-200">{{ __('Back') }}</a>

                        <button type="submit"
                            class="inline-flex h-12 items-center justify-center rounded-full bg-blue-600 px-8 text-sm font-medium text-white transition hover:bg-blue-700">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
