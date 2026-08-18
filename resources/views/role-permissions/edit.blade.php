@extends('layouts.app', ['pageTitle' => __('Role access')])

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Role access') }}</h1>
        <p class="text-sm text-gray-600 mb-6">
            {{ __('Choose a role, then tick what it may use on the website, the mobile app, and the API. SuperAdmin always has full access and is not edited here.') }}
        </p>

        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="get" action="{{ route('role-permissions.edit') }}" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="role_id">{{ __('Role') }}</label>
            <div class="flex gap-2">
                <select id="role_id" name="role_id" class="border rounded-lg px-3 py-2 min-w-[16rem]"
                    onchange="this.form.submit()">
                    @foreach ($roles as $role)
                        <option value="{{ $role->RoleID }}" @selected($selected && $role->RoleID === $selected->RoleID)>
                            {{ $role->RoleName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if ($selected && $selected->RoleName === 'SuperAdmin')
            <p class="text-sm text-gray-600">{{ __('SuperAdmin is not edited in the matrix.') }}</p>
        @elseif ($selected)
            <form method="post" action="{{ route('role-permissions.update') }}">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selected->RoleID }}">
                <input type="hidden" name="auth_version" value="{{ $authVersion }}">

                @foreach ($catalog as $platform => $items)
                    <section class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3 uppercase tracking-wide">{{ $platform }}</h2>
                        <div class="bg-white border rounded-lg divide-y">
                            @foreach ($items as $item)
                                <label class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="keys[]" value="{{ $item['key'] }}"
                                        class="mt-1" @checked($item['granted'])>
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">{{ $item['label'] }}</span>
                                        <span class="block text-xs text-gray-500 font-mono">{{ $item['key'] }}</span>
                                        @if ($item['danger'])
                                            <span class="inline-block mt-1 text-xs text-amber-700">{{ __('Sensitive') }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="password">
                        {{ __('Confirm with your password') }}
                    </label>
                    <input type="password" name="password" id="password" required
                        class="border rounded-lg px-3 py-2 w-full max-w-sm">
                </div>

                <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg">
                    {{ __('Save access') }}
                </button>
            </form>
        @endif
    </div>
@endsection
