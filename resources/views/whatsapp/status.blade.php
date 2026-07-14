@extends('layouts.app', ['pageTitle' => 'حالة واتساب'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-gray-800">حالة جسر الواتساب</h1>
            <a href="{{ route('whatsapp.status') }}"
                class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold text-sm">تحديث</a>
        </div>

        @if ($error)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3">
                {{ $error }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-100 p-6 space-y-4 max-w-xl">
            <div>
                <div class="text-sm text-gray-500 mb-1">عنوان الجسر</div>
                <div class="font-mono text-sm break-all">{{ $base }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500 mb-1">الاتصال</div>
                @if (!$reachable)
                    <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-800 text-sm font-semibold">غير متاح</span>
                @elseif ($connected)
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-sm font-semibold">متصل</span>
                @else
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-sm font-semibold">بانتظار مسح QR</span>
                @endif
            </div>

            @if ($reachable && !$connected && $qr)
                <div>
                    <div class="text-sm text-gray-600 mb-2">امسح الرمز من واتساب → الأجهزة المرتبطة</div>
                    <img src="{{ $qr }}" alt="WhatsApp QR" class="w-64 h-64 border border-gray-200 rounded-lg bg-white">
                </div>
            @elseif ($reachable && !$connected && !$qr)
                <p class="text-sm text-gray-600">لا يوجد QR حالياً. أعد تشغيل الجسر أو انتظر بضع ثوانٍ ثم حدّث الصفحة.</p>
            @elseif ($connected)
                <p class="text-sm text-gray-600">الجلسة محفوظة على القرص. لا تحذف مجلد <code class="font-mono text-xs">whatsapp-bridge/auth_session</code>.</p>
            @endif
        </div>
    </div>
@endsection
