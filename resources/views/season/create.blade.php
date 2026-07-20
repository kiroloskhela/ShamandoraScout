@extends('layouts.app' , ['pageTitle' => __('Add new season')])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300">
        <!-- Card Title -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">إضافة موسم جديد</h2>
        </div>

        <form method="POST" action="{{ route('season.insert') }}">
            @csrf
            <div class="space-y-6">
                <!-- Season Name -->
                <div class="relative">
        <input id="season_name" type="text" name="season_name" 
    placeholder="ادخل اسم الموسم (اختياري)" 
    value="{{ old('season_name') }}"
    class="peer w-full h-12 px-4 text-sm placeholder-transparent transition-all border rounded-lg text-right border-slate-200 text-slate-500 focus:border-blue-500 focus:outline-none" />
  <label for="season_name"
                      class="cursor-text peer-focus:cursor-default peer-autofill:-top-2 absolute right-2 -top-2 z-[1] px-2 text-xs text-slate-400 transition-all before:absolute before:top-0 before:right-0 before:z-[-1] before:block before:h-full before:w-full before:bg-white before:transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-required:after:text-blue-500 peer-required:after:content-['\00a0*'] peer-invalid:text-blue-500 peer-focus:-top-2 peer-focus:text-xs peer-focus:text-blue-500 peer-invalid:peer-focus:text-blue-500 peer-disabled:cursor-not-allowed peer-disabled:text-slate-400 peer-disabled:before:bg-transparent">
                          اسم الموسم
                    </label>
                </div>

                <!-- Season Year -->
                <div class="relative">
                    <input id="season_year" type="number" name="season_year" required
                        placeholder="ادخل السنة" 
                        value="{{ old('season_year') }}"
                        class="peer w-full h-12 px-4 text-sm placeholder-transparent transition-all border rounded-lg text-right border-slate-200 text-slate-500 focus:border-blue-500 focus:outline-none" />
                    <label for="season_year"
                        class="cursor-text peer-focus:cursor-default peer-autofill:-top-2 absolute right-2 -top-2 z-[1] px-2 text-xs text-slate-400 transition-all before:absolute before:top-0 before:right-0 before:z-[-1] before:block before:h-full before:w-full before:bg-white before:transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-required:after:text-blue-500 peer-required:after:content-['\00a0*'] peer-invalid:text-blue-500 peer-focus:-top-2 peer-focus:text-xs peer-focus:text-blue-500 peer-invalid:peer-focus:text-blue-500 peer-disabled:cursor-not-allowed peer-disabled:text-slate-400 peer-disabled:before:bg-transparent">{{ __('Year') }}</label>
                </div>

                <!-- Submit -->
                <div class="flex justify-center">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                        إضافة موسم
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
