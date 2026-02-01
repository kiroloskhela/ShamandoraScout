@extends('layouts.app', ['pageTitle' => 'تعديل دفعة'])

@section('content')
    <div class="flex place-content-center" dir="rtl">
        <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md border-2 border-green-300">

            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل دفعة</h2>
                <p class="text-sm text-gray-600 mt-1">
                    الحد الأقصى المسموح لهذه الدفعة: <span class="font-bold">{{ number_format($remaining, 2) }}</span>
                </p>
            </div>

            <form method="POST" action="{{ route('transactions.update', $trx->TransactionID) }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">المبلغ</label>
                        <input type="number" step="0.01" name="amount" value="{{ $trx->Amount }}" required
                            class="w-full h-12 px-4 border rounded-xl text-right border-slate-200 text-slate-600
                                  focus:border-green-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700">ملاحظات الدفعة</label>
                        <input type="text" name="notes" value="{{ $trx->Notes }}"
                            class="w-full h-12 px-4 border rounded-xl text-right border-slate-200 text-slate-600
                                  focus:border-green-500 focus:outline-none"
                            placeholder="سبب التعديل...">
                    </div>

                    <div class="flex justify-center gap-4">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full
                                   bg-green-600 text-white hover:bg-green-700 transition">
                            حفظ
                        </button>

                        <a href="{{ url()->previous() }}"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full
                              bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            إلغاء
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
