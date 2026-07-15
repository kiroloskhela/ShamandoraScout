@extends('layouts.app', ['pageTitle' => 'مسح من القائمة السوداء'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">مسح من القائمة السوداء</h2>
            </div>

            <form method="POST" action="{{ route('personblacklist.destroy', $black->BlackListID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Person') }}</label>
                        <input type="text" value="{{ $black->PersonName }} - ({{ $black->PersonID }})" readonly
                            class="w-full h-12 px-4 border rounded-lg bg-gray-50 text-right border-slate-200 text-slate-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Note') }}</label>
                        <textarea rows="4" readonly
                            class="w-full px-4 py-3 border rounded-lg bg-gray-50 text-right border-slate-200 text-slate-600 focus:outline-none">{{ $black->Note }}</textarea>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition">{{ __('Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
