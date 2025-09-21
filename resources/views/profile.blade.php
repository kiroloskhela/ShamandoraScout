@extends('layouts.app' , ['pageTitle' => "ملفي الشخصي"?? ''])
@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg border-4 transition-all duration-300" :class="editMode ? 'border-emerald-300' : 'border-blue-300'" x-data="{ editMode: false }">
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('img/mark.jpg') }}" class="h-32 w-32 rounded-full shadow-lg mb-4" alt="User Avatar">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ Auth::user()->FirstName }} {{ Auth::user()->SecondName }} {{ Auth::user()->ThirdName }} {{ Auth::user()->FourthName }}</h2>
        <p class="text-lg text-gray-500">{{ Auth::user()->ShamandoraCode }}</p>
    </div>

    <form>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-medium mb-1">الاسم الكامل</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ Auth::user()->FirstName }} {{ Auth::user()->SecondName }} {{ Auth::user()->ThirdName }} {{ Auth::user()->FourthName }}">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">الرقم الكشفي</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" readonly value="{{ Auth::user()->ShamandoraCode }}">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">البريد الإلكتروني</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ Auth::user()->PersonalEmail }}">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">الرقم القومي</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ Auth::user()->RaqamQawmy }}">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">رقم الهاتف المحمول</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ DB::table('PersonPhoneNumbers')->where('PersonID', Auth::id())->value('PersonPersonalMobileNumber') }}">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">تاريخ الميلاد</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ Auth::user()->DateOfBirth ?? '' }}">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">العنوان</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ DB::table('PersonalPhysicalAddress')->where('PersonID', Auth::id())->value('MainStreetName') }} {{ DB::table('PersonalPhysicalAddress')->where('PersonID', Auth::id())->value('SubStreetName') }}">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-medium mb-1">كلمة المرور</label>
            <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" :readonly="!editMode" value="{{ optional(App\Models\Password::where('PersonID', Auth::id())->first())->Password }}">
        </div>
        <div class="flex flex-col items-center gap-4 mt-8">
            <button type="button" class="px-8 py-3 bg-emerald-500 text-white rounded shadow hover:bg-emerald-600 transition w-48 text-lg font-semibold" @click="editMode = true" x-show="!editMode">تعديل</button>
            <button type="button" class="px-8 py-3 bg-red-500 text-white rounded shadow hover:bg-red-600 transition w-48 text-lg font-semibold" @click="if(confirm('هل تريد حفظ التعديلات؟')) { editMode = false; /* TODO: Add save logic here */ }" x-show="editMode">حفظ</button>
        </div>
    </form>
</div>

@endsection