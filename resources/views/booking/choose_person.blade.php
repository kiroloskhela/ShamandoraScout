@extends('layouts.app', ['pageTitle' => 'اختيار الشخص'])

@section('content')
    <div class="min-h-[70vh] flex items-center justify-center" dir="rtl">
        <div class="w-full max-w-2xl">

            <div class="text-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">اختيار الشخص للحجز</h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $eventInfo->SeasonName }} ({{ $eventInfo->SeasonYear }}) — {{ $eventInfo->EventName }}
                </p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="p-4">
                    <input id="spotlight" type="text" placeholder="ابحث بالاسم أو رقم الهاتف أو الكود..."
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 text-slate-700 focus:outline-none focus:border-blue-500">
                </div>

                <div id="results" class="max-h-80 overflow-auto border-t border-gray-100 hidden"></div>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('booking.create') }}" class="text-sm text-gray-500 hover:text-gray-700">رجوع</a>
            </div>

        </div>
    </div>

    <script>
        const input = document.getElementById('spotlight');
        const results = document.getElementById('results');
        let timer = null;

        input.addEventListener('input', function() {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) {
                results.classList.add('hidden');
                results.innerHTML = '';
                return;
            }

            timer = setTimeout(() => {
                fetch(`{{ route('booking.searchPerson') }}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => {
                        results.classList.remove('hidden');
                        if (!data.length) {
                            results.innerHTML =
                                `<div class="p-4 text-sm text-gray-500">لا يوجد نتائج</div>`;
                            return;
                        }

                        results.innerHTML = data.map(p => `
                    <a href="{{ url('booking/event/' . $seasonEventID . '/person') }}/${p.PersonID}"
                       class="block px-4 py-3 hover:bg-blue-50 border-b border-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-bold text-gray-800">${p.FullName}</div>
                                <div class="text-xs text-gray-500">
                                    كود: ${p.ShamandoraCode ?? '-'} • هاتف: ${p.PersonPersonalMobileNumber ?? '-'}
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                ${p.QetaaName ?? ''} ${p.SanaMarhalaName ? '• '+p.SanaMarhalaName : ''}
                            </div>
                        </div>
                    </a>
                `).join('');
                    })
                    .catch(() => {
                        results.classList.remove('hidden');
                        results.innerHTML = `<div class="p-4 text-sm text-red-500">خطأ في البحث</div>`;
                    });
            }, 250);
        });
    </script>
@endsection
