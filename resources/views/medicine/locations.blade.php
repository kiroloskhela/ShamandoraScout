@extends('layouts.app', ['pageTitle' => __('Medicine locations')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto border-2 border-cyan-300">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">أماكن الأدوية</h1>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('medicine.locations.store') }}"
                class="grid md:grid-cols-[1fr_auto] gap-3 mb-6">
                @csrf
                <input type="text" name="location_name" value="{{ old('location_name') }}"
                    class="h-12 ps-4 border rounded-lg border-slate-200 text-slate-700 focus:border-cyan-500 focus:outline-none"
                    placeholder="مثال: صندوق 4">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-cyan-50 text-cyan-700 hover:bg-cyan-100 transition">
                    إضافة مكان
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-right border border-slate-200">
                    <thead class="bg-slate-50 text-sm text-slate-700">
                        <tr>
                            <th class="p-3 border-b">اسم المكان</th>
                            <th class="p-3 border-b">{{ __('Status') }}</th>
                            <th class="p-3 border-b">{{ __('Edit') }}</th>
                            <th class="p-3 border-b">{{ __('Delete') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-800">
                        @foreach ($locations as $location)
                            @php($isStockLocation = $location->LocationName === 'ستوك')
                            <tr>
                                <form method="POST" action="{{ route('medicine.locations.update', $location->LocationID) }}">
                                    @csrf
                                    @method('PATCH')
                                    <td class="p-3 border-b">
                                        <input type="text" name="location_name" value="{{ $location->LocationName }}"
                                            {{ $isStockLocation ? 'readonly disabled' : '' }}
                                            class="w-full h-11 px-3 border rounded-lg text-right border-slate-200 focus:border-cyan-500 focus:outline-none {{ $isStockLocation ? 'bg-slate-100 text-slate-500' : '' }}">
                                        @if ($isStockLocation)
                                            <div class="mt-1 text-xs text-slate-500">مكان ثابت للنظام</div>
                                        @endif
                                    </td>
                                    <td class="p-3 border-b">
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" name="is_active" value="1"
                                                {{ $isStockLocation ? 'disabled' : '' }}
                                                {{ $location->IsActive ? 'checked' : '' }}>
                                            <span>{{ $location->IsActive ? 'مفعل' : 'غير مفعل' }}</span>
                                        </label>
                                    </td>
                                    <td class="p-3 border-b">
                                        @if ($isStockLocation)
                                            <span
                                                class="inline-flex items-center justify-center h-10 px-5 text-sm font-medium rounded-full bg-slate-100 text-slate-500">
                                                ثابت
                                            </span>
                                        @else
                                            <button type="submit"
                                                class="inline-flex items-center justify-center h-10 px-5 text-sm font-medium rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">{{ __('Save') }}</button>
                                        @endif
                                    </td>
                                </form>
                                <td class="p-3 border-b">
                                    @if ($isStockLocation)
                                        <span
                                            class="inline-flex items-center justify-center h-10 px-5 text-sm font-medium rounded-full bg-slate-100 text-slate-500">
                                            لا يحذف
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('medicine.locations.destroy', $location->LocationID) }}"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا المكان؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center h-10 px-5 text-sm font-medium rounded-full bg-red-50 text-red-700 hover:bg-red-100 transition">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('medicine.index') }}"
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ __('Back') }}</a>
            </div>
        </div>
    </div>
@endsection
