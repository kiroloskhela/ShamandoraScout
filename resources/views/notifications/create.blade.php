@extends('layouts.app', ['pageTitle' => __('Send notification')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-green-300">

            {{-- Title --}}
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800">إرسال إشعار</h2>
            </div>

            {{-- Errors --}}
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Success --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('notifications.send') }}">
                @csrf

                <div class="space-y-6">

                    {{-- Select Person --}}
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">اختر الشخص</label>
                        <select name="person_id"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 text-slate-600 focus:border-green-500 focus:outline-none"
                            required>
                            <option value="">-- اختر شخص --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->PersonID }}">
                                    {{ $user->FirstName }} {{ $user->SecondName }} {{ $user->ThirdName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">عنوان الإشعار</label>
                        <input type="text" name="title"
                            class="w-full h-12 ps-4 border rounded-lg border-slate-200 focus:border-green-500 focus:outline-none"
                            placeholder="ادخل عنوان الإشعار" required>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label class="block mb-2 text-sm text-gray-700">نص الإشعار</label>
                        <textarea name="body"
                            class="w-full px-4 py-3 border rounded-lg text-right border-slate-200 focus:border-green-500 focus:outline-none"
                            rows="3" placeholder="ادخل نص الإشعار" required></textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition">
                            إرسال
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
