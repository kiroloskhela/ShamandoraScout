@extends('layouts.app', ['pageTitle' => 'لوحه التحكم' ?? ''])



@section('content')
    <!-- PAGE TITLE SECTION -->


    <!-- STATS/CARDS SECTION -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1 -->

        <div>
            <!-- All cards stacked under each other + zoom on hover -->
            <div class="grid grid-cols-1 gap-4">

                <x-card-stat href="{{ route('person.index', ['id' => Auth::id()]) }}" title="عدد المخدومين الحالي"
                    :count="$personsCount ?? 0" color="blue">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="#" title="الفعاليات" :count="is_countable($events ?? []) ? count($events) : 0" color="emerald">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="{{ route('attendance.manage', ['id' => Auth::id()]) }}" title="حضور المخدومين"
                    color="indigo">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="{{ route('custody_requests.my', ['id' => Auth::id()]) }}" title="طلبات حجز عهده"
                    color="yellow">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V7a2 2 0 00-2-2h-3l-2-2-2 2H8a2 2 0 00-2 2v6m14 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="{{ route('place_bookings.my', ['id' => Auth::id()]) }}" title="طلبات حجز الأماكن"
                    color="pink">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10l9-6 9 6v8a2 2 0 01-2 2h-2a2 2 0 01-2-2v-3H9v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-8z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="profile" title="صفحتي الشخصية" color="rose">
                    <x-slot:icon>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A4 4 0 017 16h10a4 4 0 011.879.496M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>



            </div>

        </div>

        <!-- Card 2 -->
        <div>
            <x-calendar :events="$events" />
        </div>

    </div>
@endsection
