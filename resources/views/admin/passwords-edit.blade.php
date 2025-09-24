@extends('layouts.app', ['pageTitle' => 'تعديل كلمة السر'])

@section('content')
<x-form-card 
    title="تعديل كلمة السر للمستخدم: {{ $user->FirstName }} {{ $user->SecondName }} {{ $user->ThirdName }} {{ $user->FourthName }}"
    :action="route('admin.passwords.update', $user->PersonID)"
    method="POST"
    inputType="password"
    :inputValue="''"
    inputPlaceholder="ادخل كلمة سر جديدة"
    inputLabel="كلمة السر الجديدة"
    submitText="تعديل كلمة السر"
    submitColor="emerald"
    inputName="password"
>
    <div class="mt-4">
        <label for="wa_message" class="block text-sm font-medium text-gray-700">رسالة واتساب (تظهر للمستخدم)</label>
        <textarea id="wa_message" name="wa_message" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="اكتب الرسالة التي سترسل للمستخدم مع كلمة السر الجديدة..."></textarea>
        <small class="text-gray-500">مثال: كلمة المرور الجديدة الخاصة بك هي: [كلمة السر]</small>
    </div>
</x-form-card>
@endsection
