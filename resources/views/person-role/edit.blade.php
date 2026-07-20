@extends('layouts.app', ['pageTitle' => __('Edit leader role link')])

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-white shadow-md rounded-lg p-6 mb-8  border-2 border-emerald-300">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">تعديل ربط قائد بدور</h2>

        <form method="POST" action="{{ route('person-role.update', $personSelected->PersonRoleID) }}">
            @method('PATCH')
            @csrf

            <!-- Hidden Requester ID -->
            <input type="hidden" name="RequestPersonID" value="{{ Auth()->user()->PersonID }}">


            <!-- اسم القائد (غير قابل للتعديل) -->
            <div class="mb-6">
                <label for="person" class="block text-sm font-semibold text-gray-700 mb-2">اسم القائد</label>
                <input type="text" id="person" name="person" value="{{ $personSelected->PersonFullName }}" disabled
                    class="w-full bg-gray-100 border border-gray-300 rounded-lg py-2 px-4 text-sm text-gray-700">
            </div>

            <!-- اختيار الدور/المهمة -->
            <div class="mb-6">
                <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">اختر الدور/المهمة</label>
                <select id="role_id" name="role_id"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm py-2 px-4">
                    <option selected disabled value="{{ $personSelected->RoleID }}">
                        {{ $personSelected->RoleName }} (الحالي)
                    </option>
                    @foreach($roles as $role)
                    <option value="{{ $role->RoleID }}">{{ $role->RoleName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full focus-visible:outline-none whitespace-nowrap bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 focus:bg-emerald-200 focus:text-emerald-700 disabled:cursor-not-allowed disabled:border-emerald-300 disabled:bg-emerald-100 disabled:text-emerald-400 disabled:shadow-none">
                    تحديث
                </button>
            </div>
        </form>
    </div>

</div>
@endsection