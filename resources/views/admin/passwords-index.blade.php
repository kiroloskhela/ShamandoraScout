@extends('layouts.app', ['pageTitle' => 'إدارة كلمات المرور'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h2 class="mb-6 text-xl font-bold text-center">إدارة كلمات المرور</h2>

        <x-table-server-search :q="$q ?? ''"
            placeholder="{{ __('Name / code / phone / ID') }}" />

        <table
            class="min-w-full bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b text-center">رقم المستخدم</th>
                    <th class="px-4 py-2 border-b text-center">{{ __('Full name') }}</th>
                    <th class="px-4 py-2 border-b text-center">{{ __('Shamandora code') }}</th>
                    <th class="px-4 py-2 border-b text-center">{{ __('Phone number') }}</th>
                    <th class="px-4 py-2 border-b text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-2 border-b text-center">{{ $user->PersonID }}</td>
                        <td class="px-4 py-2 border-b font-bold text-blue-600 dark:text-blue-300 text-center">
                            {{ trim($user->FirstName . ' ' . $user->SecondName . ' ' . $user->ThirdName . ' ' . $user->FourthName) }}
                        </td>
                        <td class="px-4 py-2 border-b text-center">{{ $user->ShamandoraCode }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $user->PersonPersonalMobileNumber }}</td>
                        <td class="px-4 py-2 border-b text-center">
                            <a href="{{ route('admin.passwords.edit', $user->PersonID) }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2">تعديل
                                كلمة السر</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('No results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 flex justify-center">
            {{ $users->links() }}
        </div>
    </div>
@endsection
