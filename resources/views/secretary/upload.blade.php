@extends('layouts.app', ['pageTitle' => 'رفع مستند اجتماع القادة'])

@section('content')
    <div class="max-w-xl mx-auto bg-white rounded-xl shadow p-6">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
                <ul class="list-disc pr-5">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('secretary.upload') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm text-gray-600 mb-1">تاريخ الاجتماع</label>
                <input type="date" name="document_date" class="w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">اختر الملف (PDF / DOC / DOCX)</label>
                <input type="file" name="document_file" accept=".pdf,.doc,.docx" class="w-full" required>
                <p class="text-xs text-gray-500 mt-1">الحد الأقصى 10MB</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    رفع وحفظ
                </button>
                <a href="{{ route('secretary.index') }}" class="text-gray-600 hover:underline">رجوع</a>
            </div>
        </form>
    </div>
@endsection
