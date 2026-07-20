@extends('layouts.app', ['pageTitle' => __('Create WhatsApp campaign')])

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-5xl" dir="rtl">
        <div class="mb-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300 mb-1">{{ __('WhatsApp campaigns') }}</p>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Create WhatsApp campaign') }}</h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Build a draft from the member directory, then review and send.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('whatsapp.campaigns.create-csv') }}"
                        class="inline-flex items-center h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        {{ __('From CSV') }}
                    </a>
                    <a href="{{ route('whatsapp.campaigns.index') }}"
                        class="inline-flex items-center h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        {{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 text-red-900 dark:text-red-200 px-5 py-4">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('whatsapp.campaigns._form')
    </div>
@endsection
