@extends('layouts.app', ['pageTitle' => 'تعديل القائمة السوداء'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-green-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل القائمة السوداء</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('personblacklist.updates', $black->BlackListID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">{{ __('Person') }}</label>
                        <input type="text" value="{{ $black->PersonName }} - ({{ $black->PersonID }})" readonly
                            class="w-full h-12 px-4 border rounded-lg bg-gray-50 text-right border-slate-200 text-slate-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="note" class="block mb-2 text-sm text-gray-700">{{ __('Note') }}</label>
                        <textarea id="note" name="note" rows="4"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none"
                            placeholder="{{ __('Write the note here') }}">{{ old('note', $black->Note) }}</textarea>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition">{{ __('Edit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
