@extends('layouts.app', ['pageTitle' => 'حملة واتساب'])

@section('content')
<div class="container mx-auto px-4 py-8" dir="rtl">
    <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $campaign->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">الحالة: <strong>{{ $campaign->status }}</strong>
                · أُنشئت {{ optional($campaign->created_at)->format('Y-m-d H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('whatsapp.campaigns.index') }}" class="px-3 py-2 border rounded-lg text-sm">رجوع</a>
            @if ($campaign->isEditable())
                <a href="{{ route('whatsapp.campaigns.edit', $campaign) }}" class="px-3 py-2 border rounded-lg text-sm">تعديل</a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-900 px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="grid md:grid-cols-4 gap-4 mb-6">
        @foreach ([
            'total' => 'الإجمالي',
            'pending' => 'قيد الانتظار',
            'sent' => 'أُرسلت',
            'failed' => 'فشلت',
            'skipped' => 'تخطّيت',
            'cancelled' => 'أُلغيت',
        ] as $key => $label)
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <div class="text-xs text-gray-500">{{ $label }}</div>
                <div class="text-2xl font-bold">{{ $counts[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white border rounded-lg p-4 mb-6 space-y-3">
        <h2 class="font-bold">القالب</h2>
        <pre class="whitespace-pre-wrap text-sm bg-gray-50 p-3 rounded">{{ $campaign->message_template }}</pre>
        <p class="text-sm text-gray-600">تأخير {{ $campaign->min_delay_seconds }}–{{ $campaign->max_delay_seconds }} ث · حد {{ $campaign->max_messages_per_hour }}/ساعة</p>

        <div class="flex flex-wrap gap-2 pt-2">
            @if ($campaign->canStart())
                <form method="POST" action="{{ route('whatsapp.campaigns.confirm', $campaign) }}" class="flex flex-wrap items-center gap-3"
                    onsubmit="return confirm('تأكيد بدء إرسال الحملة؟');">
                    @csrf
                    @if (($counts['pending'] ?? 0) > $highCountThreshold)
                        <label class="text-sm text-amber-800 flex items-center gap-2">
                            <input type="checkbox" name="acknowledge_high_count" value="1" required>
                            أؤكد أن عدد المستلمين كبير ({{ $counts['pending'] }})
                        </label>
                    @endif
                    <button class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">تأكيد والإرسال</button>
                </form>
            @endif
            @if ($campaign->canPause())
                <form method="POST" action="{{ route('whatsapp.campaigns.pause', $campaign) }}">@csrf
                    <button class="bg-amber-500 text-white px-4 py-2 rounded-lg text-sm">إيقاف مؤقت</button>
                </form>
            @endif
            @if ($campaign->canResume())
                <form method="POST" action="{{ route('whatsapp.campaigns.resume', $campaign) }}">@csrf
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">استئناف</button>
                </form>
            @endif
            @if ($campaign->canCancel())
                <form method="POST" action="{{ route('whatsapp.campaigns.cancel', $campaign) }}"
                    onsubmit="return confirm('إلغاء الحملة؟');">@csrf
                    <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">إلغاء</button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-right">PersonID</th>
                    <th class="px-3 py-2 text-right">الهاتف</th>
                    <th class="px-3 py-2 text-right">الرسالة</th>
                    <th class="px-3 py-2 text-right">الحالة</th>
                    <th class="px-3 py-2 text-right">Message ID</th>
                    <th class="px-3 py-2 text-right">خطأ</th>
                    <th class="px-3 py-2 text-right">أُرسلت</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($campaign->recipients as $r)
                    <tr class="border-t align-top">
                        <td class="px-3 py-2">{{ $r->person_id }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $r->phone }}</td>
                        <td class="px-3 py-2 max-w-xs"><div class="line-clamp-3 whitespace-pre-wrap">{{ $r->personalized_message }}</div></td>
                        <td class="px-3 py-2">{{ $r->status }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $r->whatsapp_message_id ?: '—' }}</td>
                        <td class="px-3 py-2 text-red-700 text-xs">{{ $r->error_message ?: '—' }}</td>
                        <td class="px-3 py-2 text-xs">{{ optional($r->sent_at)->format('Y-m-d H:i:s') ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
