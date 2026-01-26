@extends('layouts.app', ['pageTitle' => 'إضافة مكان جديد'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-blue-300" dir="rtl">
            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إضافة مكان جديد</h2>
            </div>

            <form method="POST" action="{{ route('place.insert') }}">
                @csrf
                <div class="space-y-6">

                    <!-- Location -->
                    <div class="relative">
                        <label for="location_id" class="block mb-2 text-sm text-gray-700">اختر الموقع</label>
                        <select id="location_id" name="location_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none">
                            <option value="" disabled selected>اختر الموقع</option>

                            @foreach ($locations as $location)
                                <option value="{{ $location->LocationID }}">
                                    {{ $location->LocationName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Place Name -->
                    <div class="relative">
                        <label for="place_name" class="block mb-2 text-sm text-gray-700">اسم المكان</label>
                        <input type="text" id="place_name" name="place_name" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-blue-500 focus:outline-none"
                            placeholder="ادخل اسم المكان">
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                                   rounded-full bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 transition">
                            إضافة المكان
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
