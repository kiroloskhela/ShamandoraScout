@extends('layouts.app' , ['pageTitle' => "ملفي الشخصي"?? ''])

@section('content')
<div class="max-w-4xl mx-auto mt-10 space-y-8">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg p-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 text-red-800 border border-red-200 rounded-lg p-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== Card 1: Profile Info ===== --}}
    <div
        x-data="{
            editMode: false,
            startEdit(){ this.editMode = true; },
            cancelEdit(){
                this.editMode = false;
                // Reset the form to original values
                $refs.profileForm.reset();
            }
        }"
        class="bg-white p-8 rounded-xl shadow-lg border-4 transition-all duration-300"
        :class="editMode ? 'border-emerald-300' : 'border-blue-300'"
    >
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('img/mark.jpg') }}" class="h-24 w-24 rounded-full shadow mb-4" alt="User Avatar">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ $user->FirstName }} {{ $user->SecondName }} {{ $user->ThirdName }} {{ $user->FourthName }}
            </h2>
            <p class="text-gray-500 mt-1">{{ $user->ShamandoraCode }}</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" x-ref="profileForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            {{-- If you wired the route as PUT/PATCH, uncomment the next line --}}
            {{-- @method('PUT') --}}

            {{-- FirstName --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">الاسم الأول</label>
                <input type="text" name="FirstName" value="{{ old('FirstName', $user->FirstName) }}"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       :readonly="!editMode">
                @error('FirstName') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- SecondName --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">اسم الأب</label>
                <input type="text" name="SecondName" value="{{ old('SecondName', $user->SecondName) }}"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       :readonly="!editMode">
                @error('SecondName') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ThirdName --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">اسم الجد</label>
                <input type="text" name="ThirdName" value="{{ old('ThirdName', $user->ThirdName) }}"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       :readonly="!editMode">
                @error('ThirdName') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- FourthName --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">اللقب</label>
                <input type="text" name="FourthName" value="{{ old('FourthName', $user->FourthName) }}"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       :readonly="!editMode">
                @error('FourthName') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ScoutJoiningYear --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">سنة الانضمام للكشاف</label>
                <input type="number" name="ScoutJoiningYear" value="{{ old('ScoutJoiningYear', $user->ScoutJoiningYear) }}"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       :readonly="!editMode" inputmode="numeric" min="1900" max="2100">
                @error('ScoutJoiningYear') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- PersonPersonalMobileNumber (from related table) --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">رقم الهاتف المحمول</label>
                <input type="text" name="PersonPersonalMobileNumber"
                       value="{{ old('PersonPersonalMobileNumber', $phone ?? '') }}"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       :readonly="!editMode" inputmode="tel" autocomplete="tel">
                @error('PersonPersonalMobileNumber') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Readonly fields (examples) --}}
            <div>
                <label class="block text-gray-700 font-medium mb-1">الرقم الكشفي</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" value="{{ $user->ShamandoraCode }}" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">تاريخ الميلاد</label>
                <input type="text" class="bg-gray-100 rounded px-3 py-2 w-full" value="{{ $user->DateOfBirth ?? '' }}" readonly>
            </div>

            {{-- Actions --}}
            <div class="md:col-span-2 flex items-center justify-center gap-4 mt-4">
                <button type="button"
                        class="px-8 py-3 bg-emerald-500 text-white rounded shadow hover:bg-emerald-600 transition font-semibold"
                        x-show="!editMode"
                        @click="startEdit()">تعديل</button>

                <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition font-semibold"
                        x-show="editMode">حفظ</button>

                <button type="button"
                        class="px-8 py-3 bg-gray-300 text-gray-800 rounded shadow hover:bg-gray-400 transition font-semibold"
                        x-show="editMode"
                        @click="cancelEdit()">إلغاء</button>
            </div>
        </form>
    </div>

    {{-- ===== Card 2: Change Password ===== --}}
    <div class="bg-white p-8 rounded-xl shadow-lg border-4 border-emerald-300">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">تغيير كلمة السر</h3>

        <form method="POST" action="{{ route('profile.updatePassword') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            <div class="md:col-span-2">
                <label for="new_password" class="block text-gray-700 font-medium mb-1">كلمة السر الجديدة</label>
                <input type="password" name="password" id="new_password"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       placeholder="ادخل كلمة سر جديدة" required minlength="6" autocomplete="new-password">
                @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">تأكيد كلمة السر</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="bg-gray-100 rounded px-3 py-2 w-full"
                       placeholder="اعد ادخال كلمة السر" required minlength="8" autocomplete="new-password">
            </div>

            <div class="md:col-span-2 flex items-center gap-4">
                <button type="submit"
                        class="px-6 py-2 bg-emerald-600 text-white rounded shadow hover:bg-emerald-700 transition font-semibold">
                    تعديل كلمة السر
                </button>
                <button type="reset"
                        class="px-6 py-2 bg-gray-300 text-gray-800 rounded shadow hover:bg-gray-400 transition font-semibold">
                    إلغاء
                </button>
            </div>
        </form>
    </div>



</div>
@endsection
