@extends('layouts.app', ['pageTitle' => 'حملة واتساب من CSV'])

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl" dir="rtl">
    <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">حملة من ملف CSV</h1>
            <p class="text-sm text-gray-500 mt-1">كل رقم يمكن أن يحصل على رسالة مختلفة. يُضاف كود مصر <span class="font-mono" dir="ltr">+20</span> تلقائياً.</p>
        </div>
        <a href="{{ route('whatsapp.campaigns.index') }}" class="px-3 py-2 border rounded-lg text-sm">رجوع</a>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc pr-5 text-sm space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border rounded-xl shadow-sm p-5 mb-6 space-y-3">
        <h2 class="font-bold text-gray-900">1) حمّل القالب</h2>
        <p class="text-sm text-gray-600">الملف يحتوي عمودين: <strong>Phone Number</strong> و <strong>Message</strong>.</p>
        <ul class="text-sm text-gray-600 list-disc pr-5 space-y-1">
            <li>مثال: <span class="font-mono" dir="ltr">1000485402</span> يصبح <span class="font-mono" dir="ltr">+201000485402</span></li>
            <li>أو مع صفر: <span class="font-mono" dir="ltr">01012345678</span> → <span class="font-mono" dir="ltr">+201012345678</span></li>
            <li>حد أقصى 2000 صف</li>
            <li>لو فتحت الملف في Excel وحفظته، الفاصل قد يصبح <span class="font-mono">;</span> — النظام يقبله تلقائياً</li>
        </ul>
        <a href="{{ route('whatsapp.campaigns.csv-template') }}"
            class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg text-sm font-semibold">
            تحميل قالب CSV
        </a>
    </div>

    <form method="POST" action="{{ route('whatsapp.campaigns.store-csv') }}" enctype="multipart/form-data"
        class="bg-white border rounded-xl shadow-sm p-5 space-y-5">
        @csrf
        <h2 class="font-bold text-gray-900">2) ارفع الملف وأنشئ المسودة</h2>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="name">اسم الحملة</label>
            <input id="name" name="name" required value="{{ old('name') }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
                placeholder="مثال: تذكير المخيم — مارس 2026">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="csv_file">ملف CSV</label>
            <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv" required
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 file:ml-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-emerald-800">
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1" for="min_delay_seconds">أقل تأخير (ث)</label>
                <input type="number" id="min_delay_seconds" name="min_delay_seconds" min="1" max="600"
                    value="{{ old('min_delay_seconds', 8) }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1" for="max_delay_seconds">أعلى تأخير (ث)</label>
                <input type="number" id="max_delay_seconds" name="max_delay_seconds" min="1" max="600"
                    value="{{ old('max_delay_seconds', 15) }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1" for="max_messages_per_hour">حد / ساعة</label>
                <input type="number" id="max_messages_per_hour" name="max_messages_per_hour" min="1" max="500"
                    value="{{ old('max_messages_per_hour', 60) }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5">
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold">
                رفع وإنشاء المسودة
            </button>
            <a href="{{ route('whatsapp.campaigns.create') }}" class="px-4 py-2.5 border rounded-lg text-sm text-gray-700">
                أو حملة من الدليل (قالب واحد)
            </a>
        </div>
    </form>
</div>
@endsection
