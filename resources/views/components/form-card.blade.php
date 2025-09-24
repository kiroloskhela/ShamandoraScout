@props([
    'title',
    'action',
    'method' => '',
    'inputValue' => '',
    'inputPlaceholder' => '',
    'inputLabel' => '',
    'inputName' => '',
    'submitText' => '',
    'submitColor' => '',
])

<div class="flex place-content-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-{{ $submitColor }}-300" dir="rtl">
        <!-- Card Title -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-gray-800">{{ $title }}</h2>
        </div>

        <form class="user" id="regForm" method="POST" action="{{ $action }}">
            @if ($method !== 'POST')
                @method($method)
            @endif
            @csrf

            <div class="space-y-6">
                <!-- Input Field -->
                <div class="relative">
                    <input id="{{ $inputName }}" type="text" name="{{ $inputName }}"
                        placeholder="{{ $inputPlaceholder }}" onfocusout="myFunction()" value="{{ $inputValue }}"
                        class="relative w-full h-12 px-4 text-sm placeholder-transparent transition-all border rounded-lg outline-none focus-visible:outline-none peer border-slate-200 text-slate-500 autofill:bg-white invalid:border-pink-500 invalid:text-pink-500 focus:border-{{ $submitColor }}-500 focus:outline-none invalid:focus:border-pink-500 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 text-right"
                        style="font-family: 'Cairo', sans-serif; font-size: medium" />
                    <label for="{{ $inputName }}"
                        class="cursor-text peer-focus:cursor-default peer-autofill:-top-2 absolute right-2 -top-2 z-[1] px-2 text-xs text-slate-400 transition-all before:absolute before:top-0 before:right-0 before:z-[-1] before:block before:h-full before:w-full before:bg-white before:transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-required:after:text-pink-500 peer-required:after:content-['\00a0*'] peer-invalid:text-pink-500 peer-focus:-top-2 peer-focus:text-xs peer-focus:text-{{ $submitColor }}-500 peer-invalid:peer-focus:text-pink-500 peer-disabled:cursor-not-allowed peer-disabled:text-slate-400 peer-disabled:before:bg-transparent">
                        {{ $inputLabel }}
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <input type="submit" id="submit-button" value="{{ $submitText }}"
                        class="inline-flex items-center justify-center h-12 gap-2 px-8 text-sm font-medium tracking-wide transition duration-300 rounded-full focus-visible:outline-none whitespace-nowrap bg-{{ $submitColor }}-50 text-{{ $submitColor }}-500 hover:bg-{{ $submitColor }}-100 hover:text-{{ $submitColor }}-600 focus:bg-{{ $submitColor }}-200 focus:text-{{ $submitColor }}-700 disabled:cursor-not-allowed disabled:border-{{ $submitColor }}-300 disabled:bg-{{ $submitColor }}-100 disabled:text-{{ $submitColor }}-400 disabled:shadow-none" />
                </div>
            </div>
        </form>
    </div>
</div>
