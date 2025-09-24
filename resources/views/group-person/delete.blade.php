@extends('layouts.app', ['pageTitle' => 'حذف ربط شخص بالمجموعة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-red-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تأكيد حذف ربط الشخص بالمجموعة</h2>
                <p class="text-sm text-gray-500 mt-2">هذه العملية لا يمكن التراجع عنها.</p>
            </div>

            <!-- Person info -->
            <div class="rounded-lg border border-slate-200 mb-6">
                <div class="px-4 py-3 bg-slate-50 rounded-t-lg text-sm font-semibold text-slate-700">بيانات الشخص</div>
                <div class="p-4 text-slate-700 text-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium">الاسم الكامل:</span>
                        <span>{{ $person->FullName }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-medium">الكود:</span>
                        <span>{{ $person->ShamandoraCode }}</span>
                    </div>
                </div>
            </div>

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($multiple)
                <!-- Multiple links: show a list to choose which to delete -->
                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800 text-sm">
                    يوجد أكثر من ربط لهذا الشخص. اختر المجموعة المطلوب حذف الربط معها.
                </div>

                <div class="space-y-3">
                    @foreach ($links as $link)
                        <form method="POST" action="{{ route('group-person.destroy', $person->PersonID) }}"
                            class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-4">
                            @csrf
                            @method('DELETE')

                            <div class="text-sm">
                                <div class="font-semibold text-slate-800">{{ $link->GroupInfo ?: '—' }}</div>
                                <div class="text-slate-600">الدور: {{ $link->GroupRoleName }}</div>
                            </div>

                            <input type="hidden" name="group_id" value="{{ $link->GroupID }}">


                            <button type="submit"
                                class="inline-flex items-center justify-center h-10 px-5 text-sm font-medium rounded-full bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition">
                                حذف هذا الربط
                            </button>
                        </form>
                    @endforeach
                </div>
            @else
                <!-- Single link confirm -->
                @php $link = $links->first(); @endphp

                <div class="rounded-lg border border-slate-200 mb-6">
                    <div class="px-4 py-3 bg-slate-50 rounded-t-lg text-sm font-semibold text-slate-700">تفاصيل الربط</div>
                    <div class="p-4 text-slate-700 text-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium">المجموعة:</span>
                            <span>{{ $link->GroupInfo ?: '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium">الدور:</span>
                            <span>{{ $link->GroupRoleName }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 text-sm">
                    <div class="font-bold mb-1">تحذير:</div>
                    <p>سيتم حذف هذا الربط نهائيًا. تأكد قبل المتابعة.</p>
                </div>

                <form method="POST" action="{{ route('group-person.destroy', $person->PersonID) }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="group_id" value="{{ $link->GroupID }}">

                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('group-person.index') }}"
                            class="inline-flex items-center justify-center h-11 px-6 text-sm font-medium rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            إلغاء
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center h-11 px-8 text-sm font-medium rounded-full bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition">
                            حذف نهائي
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
