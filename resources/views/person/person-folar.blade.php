@extends('layouts.app', ['pageTitle' => __('Assign scout scarves')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100">{{ __('Assign scout scarves') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ __('Pick one scarf per member, or leave empty.') }}</p>
            </div>
            <a href="{{ route('person.index', ['id' => Auth::id()]) }}"
                class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                {{ __('Members data') }}
            </a>
        </div>

        <form method="POST" action="{{ route('person.folar.sync') }}">
            @csrf
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('Full name') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('Sector') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('Stage') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ __('Scout scarf') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($persons as $person)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $person->full_name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $person->QetaaName }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $person->SanaMarhalaName }}</td>
                                <td class="px-4 py-3">
                                    <select name="folar[{{ $person->PersonID }}]"
                                        class="w-full min-w-[12rem] rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                                        <option value="">{{ __('No scout scarf') }}</option>
                                        @foreach ($folars as $folar)
                                            <option value="{{ $folar->FolarID }}"
                                                @selected((int) ($person->FolarID ?? 0) === (int) $folar->FolarID)>
                                                {{ $folar->FolarName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No data found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($persons->isNotEmpty())
                <div class="mt-6">
                    <button type="submit"
                        class="inline-flex h-11 items-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white hover:bg-emerald-700">
                        {{ __('Save') }}
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
