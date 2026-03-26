@extends('layouts.app', ['pageTitle' => 'استرداد مع خصم جزء'])

@section('content')
    <div class="container mx-auto px-4 py-8" dir="rtl">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">استرداد المبلغ مع خصم جزء</h2>
                <p class="text-sm text-slate-500 mt-1">قم بتحديد الجزء الذي سيتم خصمه، وسيتم استرداد باقي المبلغ للملتحق.</p>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                        <ul class="list-disc pr-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <div class="text-sm text-slate-500 mb-1">اسم الملتحق</div>
                        <div class="font-bold text-slate-800">{{ $booking->PersonFullName }}</div>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <div class="text-sm text-slate-500 mb-1">المبلغ المدفوع</div>
                        <div class="font-bold text-green-700">{{ number_format($booking->AmountPaid, 2) }}</div>
                    </div>
                </div>

                <form
                    action="{{ route('eventBookingFinance.partialRefundStore', $booking->SeasonEventParticipantFinanceID) }}"
                    method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="deduction_amount" class="block mb-2 text-sm text-gray-700">الجزء المخصوم</label>
                        <input type="number" step="0.01" min="0" name="deduction_amount" id="deduction_amount"
                            value="{{ old('deduction_amount') }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-orange-500 focus:outline-none"
                            placeholder="اكتب الجزء الذي سيتم خصمه من المبلغ المدفوع">
                        <p class="text-xs text-slate-500 mt-2">
                            مثال: إذا كان المدفوع 1000 وكتبت 200، سيتم استرداد 800 والاحتفاظ بـ 200.
                        </p>
                    </div>

                    <div>
                        <label for="notes" class="block mb-2 text-sm text-gray-700">ملاحظات</label>
                        <textarea name="notes" id="notes" rows="4"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-orange-500 focus:outline-none"
                            placeholder="ملاحظات إضافية إن وجدت">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                            تنفيذ الاسترداد
                        </button>

                        <a href="{{ route('eventBookingFinance.index', $booking->SeasonEventID) }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                            رجوع
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
