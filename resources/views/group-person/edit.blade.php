@extends('layouts.app', ['pageTitle' => 'إدارة الأشخاص في المجموعة'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-emerald-300" dir="rtl">

            <!-- Title -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل ربط شخص بالمجموعة الكشفية</h2>
            </div>

            <form method="POST" action="{{ route('group-person.update', $personGroupRoleRow->PersonGroupRoleID) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-6">

                    <!-- المجموعة الكشفية -->
                    <div class="relative">
                        <label for="group_id" class="block mb-2 text-sm text-gray-700">{{ __('Choose scout group') }}</label>
                        <select name="group_id" id="group_id" required
                            class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none">
                            @if (optional($selectedGroup)->GroupID)
                                <option value="{{ $selectedGroup->GroupID }}" selected>{{ $selectedGroup->GroupInfo }}
                                </option>
                            @else
                                <option value="" selected disabled>{{ __('Choose scout group') }}</option>
                            @endif

                            @foreach ($groups as $group)
                                <option value="{{ $group->GroupID }}">{{ $group->GroupInfo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الشخص (ثابت في التعديل) -->
                    <div class="relative">
                        <label class="block mb-2 text-sm text-gray-700">الاسم والكود الخاص بالشخص</label>

                        <div
                            class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3 bg-slate-50">
                            <div class="text-sm text-slate-700">
                                {{ optional($person)->FullName ?? '—' }}
                            </div>
                            <span
                                class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                PersonID: {{ optional($person)->PersonID ?? '—' }}
                            </span>
                        </div>

                        <!-- نمرر الـ PersonID في حقل مخفي -->
                        <input type="hidden" name="person_id" value="{{ optional($person)->PersonID }}">
                        <p class="mt-2 text-xs text-gray-500">لا يمكن تغيير الشخص في شاشة التعديل. استخدم شاشة الإدخال لربط
                            أشخاص جدد.</p>
                    </div>

                    <!-- الدور / المهمة -->
                    <div class="relative">
                        <label for="group_role_id" class="block mb-2 text-sm text-gray-700">اختر دور الشخص في
                            المجموعة</label>

                        @php
                            $prefilledRoleId =
                                optional($selectedGroupRole)->GroupRoleID ?? optional($makhdoomGroupRole)->GroupRoleID;
                            $prefilledRoleName =
                                optional($selectedGroupRole)->GroupRoleName ??
                                optional($makhdoomGroupRole)->GroupRoleName;
                        @endphp

                        @if ($isKhadem)
                            <select id="group_role_id" name="group_role_id" required
                                class="w-full h-12 px-4 border rounded-lg text-right border-slate-200 text-slate-600 focus:border-emerald-500 focus:outline-none">
                                @if ($prefilledRoleId)
                                    <option value="{{ $prefilledRoleId }}" selected>{{ $prefilledRoleName }}</option>
                                @else
                                    <option disabled selected value="">اختر دور الشخص</option>
                                @endif
                                @foreach ($groupRoles as $groupRole)
                                    <option value="{{ $groupRole->GroupRoleID }}">{{ $groupRole->GroupRoleName }}</option>
                                @endforeach
                            </select>
                        @else
                            <!-- غير خادم: نظهر الدور المقروء فقط ونثبت القيمة بحقل مخفي -->
                            <div
                                class="w-full h-12 px-4 flex items-center rounded-lg border border-slate-200 bg-slate-50 text-right text-slate-600">
                                {{ $prefilledRoleName ?? 'عضو' }}
                            </div>
                            <input type="hidden" name="group_role_id" value="{{ $prefilledRoleId }}">
                            <p class="mt-2 text-xs text-gray-500">الدور ثابت لهذه الفئة ولا يمكن تغييره.</p>
                        @endif
                    </div>

                    <!-- أزرار -->
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('group-person.index') }}"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition">{{ __('Cancel') }}</a>

                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">{{ __('Edit') }}</button>
                    </div>

                </div>
            </form>

            <!-- ملاحظات الأخطاء (اختياري) -->
            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 text-sm">
                    <div class="font-bold mb-2">حدثت أخطاء في الإدخال:</div>
                    <ul class="list-disc pr-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
@endsection
