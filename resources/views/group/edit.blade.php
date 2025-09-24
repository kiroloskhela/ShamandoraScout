@extends('layouts.app', ['pageTitle' => 'تعديل بيانات المجموعة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-emerald-300" dir="rtl">
            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل بيانات المجموعة</h2>
            </div>

            <form method="POST" action="{{ route('group.update', $groupSelected->GroupID) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <!-- Group Type -->
                    <div class="relative">
                        <label for="group_type_id" class="block mb-2 text-sm text-gray-700">اختر نوع المجموعة</label>
                        <select id="group_type_id" name="group_type_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-emerald-500 focus:outline-none">
                            @foreach ($groupTypes as $groupType)
                                <option value="{{ $groupType->GroupTypeID }}"
                                    {{ $groupSelected->GroupTypeID == $groupType->GroupTypeID ? 'selected' : '' }}>
                                    {{ $groupType->GroupTypeName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Included Under Group -->
                    <div class="relative">
                        <label for="included_under_group_id" class="block mb-2 text-sm text-gray-700">
                            اختر المجموعة التي تتضمنها (المجموعة الأكبر منها)
                        </label>
                        <select id="included_under_group_id" name="included_under_group_id"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-emerald-500 focus:outline-none">
                            <option value="">بدون مجموعة أب</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->GroupID }}"
                                    {{ $groupSelected->IncludedUnderGroupID == $group->GroupID ? 'selected' : '' }}>
                                    {{ $group->GroupInfo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Group Name -->
                    <div class="relative">
                        <label for="group_name" class="block mb-2 text-sm text-gray-700">اسم المجموعة</label>
                        <input type="text" id="group_name" name="group_name" required
                            value="{{ $groupSelected->GroupName }}"
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600
                                   focus:border-emerald-500 focus:outline-none"
                            placeholder="ادخل اسم المجموعة">
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide 
                                   rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                            حفظ التعديلات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
