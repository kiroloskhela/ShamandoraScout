@extends('layouts.app', ['pageTitle' => 'تعديل ضيف'])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-5xl border-2 border-green-300" dir="rtl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">تعديل بيانات الضيف</h2>
                <p class="text-sm text-gray-500 mt-2">يمكنك تعديل بيانات الضيف وربطه بشخص من النظام</p>
            </div>

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('guests.update', $guest->GuestID) }}">
                @csrf

                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">بيانات الضيف</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block mb-2 text-sm text-gray-700">{{ __('First name') }}</label>
                            <input type="text" id="first_name" name="first_name"
                                value="{{ old('first_name', $guest->FirstName) }}" required
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="second_name" class="block mb-2 text-sm text-gray-700">{{ __('Second name') }}</label>
                            <input type="text" id="second_name" name="second_name"
                                value="{{ old('second_name', $guest->SecondName) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="third_name" class="block mb-2 text-sm text-gray-700">{{ __('Third name') }}</label>
                            <input type="text" id="third_name" name="third_name"
                                value="{{ old('third_name', $guest->ThirdName) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="fourth_name" class="block mb-2 text-sm text-gray-700">{{ __('Fourth name') }}</label>
                            <input type="text" id="fourth_name" name="fourth_name"
                                value="{{ old('fourth_name', $guest->FourthName) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="email" class="block mb-2 text-sm text-gray-700">{{ __('Email') }}</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $guest->Email) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="mobile_number" class="block mb-2 text-sm text-gray-700">{{ __('Mobile number') }}</label>
                            <input type="text" id="mobile_number" name="mobile_number"
                                value="{{ old('mobile_number', $guest->MobileNumber) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block mb-2 text-sm text-gray-700">{{ __('Date of birth') }}</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                value="{{ old('date_of_birth', $guest->DateOfBirth) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="raqam_qawmy" class="block mb-2 text-sm text-gray-700">{{ __('National ID') }}</label>
                            <input type="text" id="raqam_qawmy" name="raqam_qawmy"
                                value="{{ old('raqam_qawmy', $guest->RaqamQawmy) }}"
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label for="person_id" class="block mb-2 text-sm text-gray-700">{{ __('Linked person') }}</label>
                            <select id="person_id" name="person_id" required
                                class="w-full h-12 px-4 border rounded-lg border-slate-200 text-slate-700 focus:border-green-500 focus:outline-none">
                                <option value="">-- اختر الشخص --</option>
                                @foreach ($persons as $person)
                                    <option value="{{ $person->PersonID }}"
                                        {{ old('person_id', $guest->PersonID) == $person->PersonID ? 'selected' : '' }}>
                                        {{ $person->FullName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between gap-4 mt-8">
                    <a href="{{ route('guests.index') }}"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-600 transition">{{ __('Cancel') }}</a>

                    <button type="submit"
                        class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-100 hover:text-emerald-600 transition">
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
