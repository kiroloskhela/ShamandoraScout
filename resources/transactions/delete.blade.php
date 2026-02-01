@extends('layouts.app', ['pageTitle' => 'حذف دفعة'])

@section('content')
    <div class="flex place-content-center" dir="rtl">
        <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md border-2 border-red-300">

            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-red-700">تأكيد حذف الدفعة</h2>
                <p class="text-sm text-gray-600 mt-2">سيتم حذف الدفعة (Soft Delete) ولا يمكن التراجع.</p>
            </div>

            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-6 text-sm text-gray-800 space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">رقم العملية:</span><span
                        class="font-bold">{{ $trx->TransactionID }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">المبلغ:</span><span
                        class="font-bold">{{ number_format($trx->Amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">التاريخ:</span><span
                        class="font-bold">{{ $trx->TransactionDate }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">ملاحظات:</span><span
                        class="font-bold">{{ $trx->Notes ?? '-' }}</span></div>
            </div>

            <form method="POST" action="{{ route('transactions.destroy', $trx->TransactionID) }}">
                @csrf
                <div class="flex justify-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full
                               bg-red-600 text-white hover:bg-red-700 transition">
                        نعم، احذف
                    </button>
                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full
                          bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        إلغاء
                    </a>
                </div>
            </form>

        </div>
    </div>
@endsection
