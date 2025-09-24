@extends('layouts.app', ['pageTitle' => 'Testing Upload'])

@section('content')
    <div class="max-w-xl mx-auto mt-10">
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg p-3 mb-4">
                {!! session('success') !!}
            </div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold mb-4">📸 Upload to Google Drive</h2>

            <form action="{{ route('testing.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="photo" accept="image/*" class="mb-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                    Upload
                </button>
            </form>
        </div>
    </div>
@endsection
