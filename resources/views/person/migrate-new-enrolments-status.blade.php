@extends('layouts.app', ['pageTitle' => $result->hasFailures() ? __('Migration completed with partial failures') : __('Migration completed')])

@section('content')
    <div class="max-w-xl mx-auto px-4 py-10">
        <div @class([
            'rounded-2xl border shadow-sm p-8 text-center space-y-5',
            'border-emerald-200 dark:border-emerald-900 bg-white dark:bg-slate-900' => ! $result->hasFailures(),
            'border-amber-200 dark:border-amber-900 bg-white dark:bg-slate-900' => $result->hasFailures(),
        ])>
            <div @class([
                'mx-auto w-14 h-14 rounded-full flex items-center justify-center text-2xl font-black',
                'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' => ! $result->hasFailures(),
                'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300' => $result->hasFailures(),
            ])>
                {{ $result->hasFailures() ? '!' : '✓' }}
            </div>

            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">
                {{ $result->hasFailures() ? __('Migration completed with partial failures') : __('Migration completed') }}
            </h2>

            @if ($result->migrated_count > 0)
                <p class="text-sm sm:text-base text-emerald-700 dark:text-emerald-300 leading-relaxed">
                    @if ($result->migrated_count === 1)
                        {{ __(':count enrolment migrated successfully.', ['count' => $result->migrated_count]) }}
                    @else
                        {{ __(':count enrolments migrated successfully.', ['count' => $result->migrated_count]) }}
                    @endif
                </p>
            @endif

            @if ($result->failed_count > 0)
                <p class="text-sm sm:text-base text-amber-700 dark:text-amber-300 leading-relaxed">
                    @if ($result->failed_count === 1)
                        {{ __(':count enrolment failed to migrate.', ['count' => $result->failed_count]) }}
                    @else
                        {{ __(':count enrolments failed to migrate.', ['count' => $result->failed_count]) }}
                    @endif
                </p>

                <div class="text-left rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/70 dark:bg-amber-950/20 p-4 space-y-2">
                    <p class="text-sm font-bold text-amber-900 dark:text-amber-100">{{ __('Failed enrolments') }}</p>
                    <ul class="text-sm text-amber-900/90 dark:text-amber-100/90 space-y-1 list-disc ps-5">
                        @foreach ($result->failures as $failure)
                            <li>{{ __('Person #:id — :message', ['id' => $failure['person_id'], 'message' => $failure['message']]) }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($result->migrated_count === 0)
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ __('No approved enrolments were pending migration.') }}
                </p>
            @else
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ __('Approved new enrolments were transferred to the main system successfully.') }}
                </p>
            @endif

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="{{ route('person.new-enrolments-migrate-index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-3 transition-colors duration-200">
                    {{ __('Back to migration buttons') }}
                </a>
                <a href="{{ route('person.index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold px-5 py-3 transition-colors duration-200">
                    {{ __('Go to system users') }}
                </a>
            </div>
        </div>
    </div>
@endsection
