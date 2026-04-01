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

        @keyframes shamandora-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
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
                        <img src="{{ Auth::user()->avatar_url }}" alt="User Avatar"
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
                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto py-4" style="max-height: calc(100vh - 200px);">

                    @php
                        $auth = Auth::check();
                        $user = $auth ? Auth::user() : null;

                        // IMPORTANT: use exists() not get()->contains() to avoid issues and extra queries
                        $isSuperAdmin = $auth && $user->role()->where('RoleName', 'SuperAdmin')->exists();
                        $isSecretary = $auth && $user->role()->where('RoleName', 'Secretary')->exists();
                        $isMedia = $auth && $user->role()->where('RoleName', 'Media')->exists();
                        $isInventory = $auth && $user->role()->where('RoleName', 'Inventory')->exists();
                        $isFinance = $auth && $user->role()->where('RoleName', 'Finance')->exists();
                    @endphp

                    {{-- ===================== SuperAdmin: System Constants ===================== --}}
                    @if ($isSuperAdmin)
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
                                        href="{{ route('rotab.index') }}">الرتب الكشفية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('blood.index') }}">فصائل الدم</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('marhala.index') }}">المراحل الدراسية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('qetaa.index') }}">القطاعات الكشفية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('sana-marhala.index') }}">السنوات والمراحل الدراسية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('entry-questions.index') }}">أسئلة فورم ادخال بيانات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('district.index') }}">الأحياء السكنية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('manteqa.index') }}">المناطق السكنية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('faculty.index') }}">الكليات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('university.index') }}">الجامعات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('role.index') }}">الأدوار والمهام</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('person-role.index') }}">ربط الأدوار والمهام</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('group-type.index') }}">أنواع المجموعات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('group.index') }}">ربط المجموعات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('event-type.index') }}">أنواع الأحداث والمناسبات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('event.index') }}">الأحداث والمناسبات الكشفية</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('group-person.index') }}">ربط الأشخاص بالمجموعات</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('season.index') }}">إدارة المواسم</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('season-event.index') }}">ربط موسم بفعالية</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Federations (visible to any logged-in user) ===================== --}}
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
                                    href="{{ url('/liveform') }}">فورم التسجيل LIVE</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ url('/new-enrolments') }}">مراجعة طلبات الالتحاق</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ url('/max-limits') }}">الحد الأقصى للطلبات</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ url('/entry-questions') }}">التحكم في أسئلة القطاعات</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ url('/new-enrolments/analytics') }}">احصائيات طلبات الالتحاق</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ url('/new-enrolments/migrations') }}">تحويل الطلبات إلى النظام
                                    الرئيسي</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Media ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">الميديا</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                @if ($isSuperAdmin || $isMedia)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('media.index') }}">اضافه صور</a>
                                @endif
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('media.pages') }}">عرض صور</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Games ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">الألعاب</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">

                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('games.index') }}">اضافة لعبة</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Finance (only SuperAdmin/Finance) ===================== --}}
                    @if ($isSuperAdmin || $isFinance)
                        <div class="px-3 mb-2">
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
                                    @if ($isSuperAdmin || $isFinance)
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('finance.index') }}">إدارة الماليه</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('eventBookingFinance.selector') }}">إدارة الحجوزات
                                            المالية</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('eventWaitingList.selector') }}">قائمة انتظار الحجوزات
                                            المالية</a>
                                        </a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Curricula (visible to any logged-in user) ===================== --}}
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
                                    href="{{ route('CurriculaCategory.index') }}">اضافه اقسام</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('curricula.index') }}">اضافه محاضرة</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Secretary ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">السيكرتارية</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                @if ($isSuperAdmin || $isSecretary)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('secretary.index') }}">إضافة محضر اجتماع</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('locations.index') }}">إضافة موقع</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('place.index') }}">إضافة نوع مكان</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.place_bookings.index') }}">إدارة طلبات حجز الأماكن</a>
                                @endif

                                {{-- Always visible to any logged-in user --}}
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('place_bookings.my') }}">طلبات حجز الأماكن</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Inventory ===================== --}}
                    @if ($isSuperAdmin || $isInventory)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">العهده</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('inventory.index') }}">العُهده</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('inventory-issue.index') }}">طباعه عهده</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.custody_requests.index') }}">متابعة عهدة</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('custody_requests.my') }}">طلب عهده</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Persons Data ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">بيانات المخدومين</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                @if ($isSuperAdmin)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('person.ShowPersons') }}">بيانات كل المخدومين</a>
                                @endif
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('person.index', ['id' => Auth::user()->id]) }}">
                                    بيانات المخدومين
                                </a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('attendance.manage') }}">حضور و انصراف المخدومين</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('personspecialcase.index') }}">الحالات الخاصة للمخدومين</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('personblacklist.index') }}">القائمة السوداء</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Password Management (SuperAdmin) ===================== --}}
                    @if ($isSuperAdmin)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">إدارة كلمات المرور</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.passwords') }}">عرض و تعديل كلمات المرور</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Profile ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">الملف الشخصي</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pr-4 space-y-1">
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('profile.show') }}">عرض الملف الشخصي</a>
                            </div>
                        </div>
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
                <header class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 sticky top-0 z-10"
                    style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;">

                    <!-- Right: Mobile menu button / Page title -->
                    <div class="flex items-center gap-3 justify-start">
                        <button id="sidebarToggle" type="button"
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg lg:hidden">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        @if (isset($pageTitle))
                            <h1 class="hidden lg:block text-lg font-bold text-gray-800 lg:text-xl truncate">
                                {{ $pageTitle }}</h1>
                        @endif
                    </div>

                    <!-- Center: Logo always locked in center -->
                    <div class="flex items-center justify-center">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('img/shamandora.png') }}" alt="Logo"
                                class="h-14 w-auto sm:h-14 lg:h-20">
                        </a>
                    </div>

                    <!-- Left: Logout button -->
                    <div class="flex items-center justify-end">
                        <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">
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

    <!-- Page loading overlay -->
    <!-- Page loading overlay -->
    <div id="pageLoadingOverlay" class="fixed inset-0 z-50 bg-white/70 backdrop-blur-sm"
        style="display: none; align-items: center; justify-content: center;">
        <div class="flex flex-col items-center gap-4">

            {{-- Spinner + Logo --}}
            <div class="relative flex items-center justify-center" style="width: 160px; height: 160px;">

                {{-- Spinning ring --}}
                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 160 160">
                    <circle cx="80" cy="80" r="72" fill="none" stroke="#e5e7eb"
                        stroke-width="4" />
                    <circle cx="80" cy="80" r="72" fill="none" stroke="#1D9E75" stroke-width="4"
                        stroke-linecap="round" stroke-dasharray="110 340"
                        style="transform-origin: 80px 80px; animation: shamandora-spin 1.1s linear infinite;" />
                </svg>

                {{-- Logo circle --}}
                <div class="relative z-10 rounded-full bg-white border border-gray-100 shadow-sm flex items-center justify-center overflow-hidden"
                    style="width: 128px; height: 128px;">
                    <img src="{{ asset('img/shamandora.png') }}" alt="شماندورة"
                        style="width: 108px; height: 108px; object-fit: contain;">
                </div>
            </div>

            {{-- Label --}}
            <p class="text-sm text-gray-500" style="font-family: 'Cairo', sans-serif;">جاري التحميل ...</p>
        </div>
    </div>


    <!-- JavaScript -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const pageLoadingOverlay = document.getElementById('pageLoadingOverlay');

        let loadingTimer = null;

        const showLoading = () => {
            if (pageLoadingOverlay) {
                pageLoadingOverlay.style.display = 'flex';
            }
        };

        const hideLoading = () => {
            if (loadingTimer) {
                clearTimeout(loadingTimer);
                loadingTimer = null;
            }
            if (pageLoadingOverlay) {
                pageLoadingOverlay.style.display = 'none';
            }
        };

        const showLoadingDelayed = () => {
            hideLoading();
            loadingTimer = setTimeout(() => {
                showLoading();
            }, 120); // avoid flashing on fast nav
        };

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

        sidebarToggle?.addEventListener('click', openSidebar);
        closeSidebar?.addEventListener('click', closeSidebarFunc);
        overlay?.addEventListener('click', closeSidebarFunc);

        // Always clear stale loader when page is ready or restored from Back/Forward
        document.addEventListener('DOMContentLoaded', hideLoading);
        window.addEventListener('load', hideLoading);
        window.addEventListener('pageshow', hideLoading);

        document.querySelectorAll('a[href]').forEach((link) => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;

            const url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) return;

            link.addEventListener('click', (event) => {
                if (
                    event.defaultPrevented ||
                    event.button !== 0 ||
                    event.metaKey ||
                    event.ctrlKey ||
                    event.shiftKey ||
                    event.altKey
                ) {
                    return;
                }

                // don't show loader for same-page navigation
                if (url.href === window.location.href) return;

                showLoadingDelayed();
            });
        });

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                showLoadingDelayed();
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebarFunc();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && window.innerWidth < 1024) {
                closeSidebarFunc();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
