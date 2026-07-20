@extends('layouts.app' , ['pageTitle' => __('Delete season')])

@section('content')
<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-red-300">
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">هل تريد بالتأكيد حذف هذا الموسم؟</h2>
            <p class="text-gray-600 mt-2">اسم الموسم: <span class="font-bold">{{ $season->SeasonName }}</span></p>
            <p class="text-gray-600">السنة: <span class="font-bold">{{ $season->SeasonYear }}</span></p>
        </div>

        <form method="POST" action="{{ route('season.destroy', $season->SeasonID) }}">
            @method('DELETE')
            @csrf
            <div class="flex justify-between">
                <a href="{{ route('season.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">{{ __('Cancel') }}</a>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">{{ __('Delete') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
