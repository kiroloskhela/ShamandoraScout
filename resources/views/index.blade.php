@extends('layouts.app', ['pageTitle' => 'لوحه التحكم' ?? ''])



@section('content')
    <!-- PAGE TITLE SECTION -->


    <!-- STATS/CARDS SECTION -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">


        <!-- Card 1 -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">

                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600"> عدد الخدام الحالي </p>
                    <p class="text-2xl font-semibold text-gray-900">223</p>
                </div>

                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>

            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">

                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">اجمالي عدد الملتحقين</p>
                    <p class="text-2xl font-semibold text-gray-900">130</p>
                </div>
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>

            </div>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Chart/Graph -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Analytics Overview</h3>
                <!-- REPLACE THIS DIV WITH YOUR CHART COMPONENT -->
                <div
                    class="h-64 bg-gray-50 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                    <p class="text-gray-500">📊 Your Chart Component Goes Here</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Recent Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                <!-- REPLACE THIS DIV WITH YOUR ACTIVITY COMPONENT -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <p class="text-sm text-gray-700">New user registered</p>
                        <span class="text-xs text-gray-500 ml-auto">2 min ago</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <p class="text-sm text-gray-700">Payment processed</p>
                        <span class="text-xs text-gray-500 ml-auto">5 min ago</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                        <p class="text-sm text-gray-700">Report generated</p>
                        <span class="text-xs text-gray-500 ml-auto">12 min ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FULL WIDTH SECTION -->
    <div class="mt-6 bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Data Table</h3>
            <!-- REPLACE THIS DIV WITH YOUR TABLE COMPONENT -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">John Doe</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jan 15, 2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-emerald-600 hover:text-emerald-900">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
