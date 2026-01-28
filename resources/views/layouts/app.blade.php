<!DOCTYPE html>
<html lang="ar" dir="rtl" class="bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'الشمندوره البحريه')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
    @stack('styles')
    <script src="//unpkg.com/alpinejs" defer></script>

</head>

<body class="bg-gray-50 ">
    <!-- Main Layout Container -->
    <div class="flex flex-col h-screen">
        <!-- Main Wrapper -->
        <div class="flex flex-1 flex-col md:flex-row">
            <!-- Sidebar Overlay (Mobile) -->
            <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black/50 lg:hidden hidden"></div>

            <!-- Sidebar Navigation -->
            <aside id="sidebar"
                class="fixed top-0 bottom-0 right-0 z-50 w-80 bg-white border-l border-gray-200 shadow-xl transform translate-x-full transition-transform duration-300 lg:translate-x-0 lg:static lg:shadow-none lg:w-72 lg:flex lg:flex-col lg:sticky lg:top-0 lg:h-screen">

                <!-- Mobile Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 lg:hidden">
                    <h2 class="text-lg font-semibold text-gray-800">القائمة</h2>
                    <button id="closeSidebar"
                        class="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- User Profile Section -->
                <div class="flex flex-col items-center p-6 border-b border-gray-200">
                    <div class=" relative mb-3">
                        <img src="https://i.pravatar.cc/60?img=7" alt="User Avatar"
                            class="w-16 h-16 rounded-full border-2 border-white shadow-sm">
                        <span
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full"></span>
                    </div>
                    <div class="text-center">
                        <h4 class="font-medium text-gray-800">{{ Auth::user()->FirstName ?? '' }}
                            {{ Auth::user()->SecondName ?? '' }}
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->ShamandoraCode ?? '' }}</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto py-4 lg:overflow-y-auto" style="max-height: calc(100vh - 200px);">
                    @if (Auth::check())
                        @php
                            $user = Auth::user();
                            $userRoles = $user->role()->get();
                        @endphp

                        @if (Auth::check() && Auth::user()->role()->get()->contains('RoleName', 'SuperAdmin'))
                            <!-- System Constants Menu -->
                            <div class="px-3 mb-2">
                                <div x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                        <span class="font-medium">ثوابت النظام</span>
                                        <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">


                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('rotab.index') }}>الرتب الكشفية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('blood.index') }}>فصائل الدم</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('marhala.index') }}>المراحل الدراسية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('qetaa.index') }}>القطاعات الكشفية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('sana-marhala.index') }}>السنوات والمراحل الدراسية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('entry-questions.index') }}>أسئلة فورم ادخال بيانات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('district.index') }}>الأحياء السكنية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('manteqa.index') }}>المناطق السكنية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('faculty.index') }}>الكليات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('university.index') }}>الجامعات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('role.index') }}>الأدوار والمهام</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('person-role.index') }}>ربط الأدوار والمهام</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('group-type.index') }}>أنواع المجموعات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('group.index') }}>ربط المجموعات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('event-type.index') }}>أنواع الأحداث والمناسبات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('event.index') }}> الأحداث والمناسبات الكشفية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('group-person.index') }}>ربط الأشخاص بالمجموعات</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('season.index') }}>إدارة المواسم</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href={{ route('season-event.index') }}>ربط موسم بفعالية</a>
                                        <!-- <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                href={{ route('betaka.index') }}>ايجازة بطاقة تقدم</a>
                           -->
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Federations Menu -->
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">الاتحاقات</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">

                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ url('/liveform') }}>فورم التسجيل LIVE</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ url('/new-enrolments') }}>مراجعة طلبات الالتحاق</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ url('/max-limits') }}>الحد الأقصى للطلبات</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ url('/entry-questions') }}>التحكم في أسئلة القطاعات</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ url('/new-enrolments/analytics') }}>احصائيات طلبات الالتحاق</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ url('/new-enrolments/migrations') }}> تحويل الطلبات إلى النظام
                                    الرئيسي</a>
                            </div>
                        </div>
                    </div>


                    <!-- Media Menu -->
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">الميديا</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                @if (Auth::check() &&
                                        Auth::user()->role()->whereIn('RoleName', ['SuperAdmin', 'Media'])->exists())
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href={{ route('media.index') }}>اضافه صور</a>
                                @endif
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ route('media.pages') }}>عرض صور</a>
                            </div>
                        </div>

                    </div>

                    <!-- Finance Menu -->
                    <div class="px-3 mb-2">
                        @if (Auth::check() &&
                                Auth::user()->role()->whereIn('RoleName', ['SuperAdmin', 'Finance'])->exists())
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">الماليه</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Curricula Menu -->
                    <div class="px-3 mb-2">

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">المناهج</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">

                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ route('CurriculaCategory.index') }}>اضافه اقسام</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href={{ route('curricula.index') }}>اضافه محاضرة</a>

                            </div>
                        </div>

                    </div>


                    <!-- Secretary Menu -->
                    <div class="px-3 mb-2">



                        <div x-data="{ open: false }">
                            @if (Auth::check() &&
                                    Auth::user()->role()->whereIn('RoleName', ['SuperAdmin', 'Secretary'])->exists())
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg
                       hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">

                                    <span class="font-medium">السيكرتارية</span>

                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">

                                    {{-- Existing --}}
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                          hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('secretary.index') }}">
                                        إضافة محضر اجتماع
                                    </a>

                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                          hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('locations.index') }}">
                                        إضافة موقع
                                    </a>

                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                          hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('place.index') }}">
                                        إضافة نوع مكان
                                    </a>



                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                          hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.place_bookings.index') }}">
                                        إدارة طلبات حجز الأماكن
                                    </a>
                            @endif
                            @if (Auth::check())
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                          hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('place_bookings.my') }}">
                                    طلبات حجز الأماكن
                                </a>

                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                          hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('place_bookings.create') }}">
                                    طلب حجز جديد
                                </a>
                            @endif
                        </div>
                    </div>

        </div>


        <!-- Inventory Menu -->
        <div class="px-3 mb-2">
            @if (Auth::check() &&
                    Auth::user()->role()->whereIn('RoleName', ['SuperAdmin', 'Inventory'])->exists())
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                        :class="{ 'bg-emerald-50 text-emerald-600': open }">
                        <span class="font-medium">العهده</span>
                        <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">

                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('inventory.index', ['id' => Auth::id()]) }}">
                            العهده
                        </a>
                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('inventory-issue.index', ['id' => Auth::id()]) }}">
                            طباعه عهده
                        </a>
                        {{-- Admin: Follow custody requests --}}
                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg
                                    hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('admin.custody_requests.index') }}">
                            متابعة عهدة
                        </a>


                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('custody_requests.my', ['id' => Auth::id()]) }}">
                            طلب عهده
                        </a>

                    </div>
                </div>
            @endif
        </div>

        <div class="px-3 mb-2">
            <div x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                    <span class="font-medium">بيانات المخدومين</span>
                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                    @if (Auth::check() && Auth::user()->role()->get()->contains('RoleName', 'SuperAdmin'))
                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('person.ShowPersons', ['id' => Auth::id()]) }}">
                            بيانات كل المخدومين
                        </a>
                    @endif
                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                        href="{{ route('person.index', ['id' => Auth::id()]) }}">
                        بيانات المخدومين
                    </a>
                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                        href="{{ route('attendance.manage', ['id' => Auth::id()]) }}">
                        حضور و انصراف المخدومين
                    </a>
                </div>
            </div>
        </div>

        <div class="px-3 mb-2">
            @if (Auth::check() && Auth::user()->role()->get()->contains('RoleName', 'SuperAdmin'))
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                        :class="{ 'bg-emerald-50 text-emerald-600': open }">
                        <span class="font-medium">إدارة كلمات المرور</span>
                        <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('admin.passwords') }}">عرض و تعديل كلمات المرور</a>

                    </div>
                </div>
            @endif
        </div>

        <!-- Profile Menu -->
        <div class="px-3 mb-2">
            @if (Auth::check())
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                        :class="{ 'bg-emerald-50 text-emerald-600': open }">
                        <span class="font-medium">الملف الشخصي</span>
                        <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                            href="{{ route('profile.show') }}">عرض الملف الشخصي</a>
                    </div>
                </div>
            @endif
        </div>
        </nav>





        <!-- Mobile Logout Footer -->
        <div class="p-4 border-t border-gray-200 lg:hidden">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-3 p-3 text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span class="font-medium">تسجيل الخروج</span>
                </button>
            </form>
        </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 w-full">
            <!-- Header Bar -->
            <header
                class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10">
                <!-- Mobile Menu Button -->
                <button id="sidebarToggle" type="button"
                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg lg:hidden">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo (Always visible) and Page Title (Desktop only) -->

                <div class="flex items-center gap-3">
                    <!-- Page Title - Only on desktop -->
                    @if (isset($pageTitle))
                        <h1 class="hidden lg:block text-lg font-bold text-gray-800 lg:text-xl">{{ $pageTitle }}
                        </h1>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <!-- Logo - Clickable to go to the top index -->
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('img/shamandora.png') }}" alt="Logo"
                            class="h-14 w-auto sm:h-14 lg:h-20">
                    </a>
                </div>

                <!-- Desktop Logout Button -->
                <div class="hidden lg:block">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            <span class="font-medium">تسجيل الخروج</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-4 lg:p-6">
                    @yield('content')
                </div>
            </div>
        </main>

    </div>

    <!-- Footer -->
    <footer class="bg-white shadow-sm border-t border-gray-200 px-4 py-3 text-center">
        <p class="text-sm text-gray-600"> جميع الحقوق محفوظة . شماندورة الكشافة ٢٠٢٥</p>
    </footer>

    </div>

    <!-- JavaScript -->
    <script>
        // Sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarFunc() {
            sidebar.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Event listeners
        sidebarToggle?.addEventListener('click', openSidebar);
        closeSidebar?.addEventListener('click', closeSidebarFunc);
        overlay?.addEventListener('click', closeSidebarFunc);

        // Close sidebar on desktop resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebarFunc();
            }
        });

        // Escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && window.innerWidth < 1024) {
                closeSidebarFunc();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
