@extends('layouts.app', ['pageTitle' => 'تعديل حملة واتساب'])

@section('content')
<div class="container mx-auto px-4 py-8" dir="rtl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تعديل المسودة: {{ $campaign->name }}</h1>
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('whatsapp.campaigns._form')
</div>
@endsection
