@extends('layouts.app', ['pageTitle' => __('Delete exam marks')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">مسح سجل درجات امتحان</h2>
            </div>

            <form method="POST" action="{{ route('personexammark.destroy', $mark->ExamMarkID) }}">
                @csrf

                <div class="space-y-4 text-sm text-gray-700 text-right">
                    <p><span class="font-semibold">المخدوم:</span> {{ $mark->PersonName }} ({{ $mark->PersonID }})</p>
                    <p><span class="font-semibold">القطعة:</span> {{ $mark->QetaaName }}</p>
                    <p><span class="font-semibold">سنة المرحلة:</span> {{ $mark->SanaMarhalaName }}</p>
                    <p><span class="font-semibold">نظري:</span> {{ $mark->TheoreticalMark }}</p>
                    <p><span class="font-semibold">عملي:</span> {{ $mark->PracticalMark }}</p>
                    <p><span class="font-semibold">التاريخ:</span> {{ $mark->ExamDate }}</p>
                </div>

                <div class="flex justify-center gap-3 mt-8">
                    <a href="{{ route('personexammark.index') }}"
                        class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 transition">{{ __('Back') }}</a>
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition">
                        تأكيد المسح
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
