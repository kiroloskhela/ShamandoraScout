@extends('layouts.app', ['pageTitle' => 'تأكيد حذف الربط'])

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12" dir="rtl">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-xl border border-red-300">
        <h2 class="text-2xl font-extrabold text-red-700 mb-6 text-center">⚠️ تأكيد حذف الربط</h2>

        <!-- Info Section -->
        <div class="bg-red-100 rounded-lg p-6 mb-6 text-gray-800 space-y-2 text-right">
            <p><strong>رقم الربط:</strong> {{ $personRole->PersonRoleID }}</p>
            <p><strong>كود الشمندورة:</strong> {{ $personRole->ShamandoraCode ?? 'غير متاح' }}</p>
            <p><strong>اسم القائد:</strong> {{ $personRole->FirstName }} {{ $personRole->SecondName }}
                {{ $personRole->ThirdName }}</p>
            <p><strong>اسم الدور:</strong> {{ $personRole->RoleName ?? 'غير متاح' }}</p>
        </div>

        <p class="text-center text-lg text-gray-700 font-medium mb-6">
            هل أنت متأكد أنك تريد حذف هذا الربط؟
        </p>

        <!-- Form Actions -->
        <form method="POST" action="{{ route('person-role.destroy', $personRole->PersonRoleID) }}">
            @method('DELETE')
            @csrf

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <button type="submit"
                    class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full focus-visible:outline-none whitespace-nowrap bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 focus:bg-red-200 focus:text-red-700 disabled:cursor-not-allowed disabled:border-red-300 disabled:bg-red-100 disabled:text-red-400 disabled:shadow-none">
                    نعم، احذف
                </button>

                <a href="{{ route('person-role.index') }}"
                    class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full focus-visible:outline-none whitespace-nowrap bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 focus:bg-gray-200 focus:text-gray-700 disabled:cursor-not-allowed disabled:border-gray-300 disabled:bg-gray-100 disabled:text-gray-400 disabled:shadow-none">{{ __('Back') }}</a>
            </div>
        </form>
    </div>

</div>
@endsection