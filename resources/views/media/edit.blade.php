@extends('layouts.app', ['pageTitle' => __('Edit media link')])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-emerald-300">
        <!-- Title -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">تعديل رابط الوسائط</h2>
        </div>

        <!-- Display current event info -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">معلومات الفعالية:</h3>
            <p class="text-sm text-gray-600">
                <strong>{{ __('Season:') }}</strong> {{ $seasonEvent->SeasonName }} ({{ $seasonEvent->SeasonYear }})
            </p>
            <p class="text-sm text-gray-600">
                <strong>{{ __('Event:') }}</strong> {{ $seasonEvent->EventName }}
            </p>
        </div>

        <!-- Display errors -->
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="text-sm text-red-600">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('media.update', $media->MediaID) }}">
            @csrf
            @method('PATCH')
            
            <div class="space-y-6">
                <!-- Drive Link Input -->
                <div class="relative">
                    <label for="drive_link" class="block mb-2 text-sm text-gray-700">رابط Drive</label>
                    <input type="url" id="drive_link" name="drive_link" required
                        class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none"
                        placeholder="https://drive.google.com/..."
                        value="{{ old('drive_link', $media->DriveLink) }}">
                </div>

                <!-- Buttons -->
                <div class="flex justify-between gap-4">
                    <a href="{{ route('media.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">{{ __('Cancel') }}</a>
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                        حفظ التغييرات
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection