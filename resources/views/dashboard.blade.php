@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Overview
</h2>

<!-- ================= SUMMARY CARD ================= -->
<div class="grid grid-cols-3 gap-6 mb-10">

    <!-- Total -->
    <div class="bg-white border-2 border-black rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Total Perangkat</p>
        <h3 class="text-4xl font-bold mt-2">52</h3>
    </div>

    <!-- Available -->
    <div class="bg-white border-2 border-green-500 rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Available</p>
        <h3 class="text-4xl font-bold text-green-600 mt-2">45</h3>
    </div>

    <!-- Not Available -->
    <div class="bg-white border-2 border-red-500 rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Not Available</p>
        <h3 class="text-4xl font-bold text-red-600 mt-2">3</h3>
    </div>

</div>

<!-- ================= PROBLEM SEVERITY ================= -->
<h3 class="text-xl font-semibold text-gray-700 mb-4">
    Problems by severity
</h3>

<div class="grid grid-cols-6 overflow-hidden rounded-lg mb-10 shadow-sm">

    <div class="bg-[#E27D74] text-center py-6">
        <p class="text-lg font-semibold">0</p>
        <p>Disaster</p>
    </div>

    <div class="bg-[#E89A6D] text-center py-6">
        <p class="text-lg font-semibold">0</p>
        <p>High</p>
    </div>

    <div class="bg-[#E7BE78] text-center py-6">
        <p class="text-lg font-semibold">2</p>
        <p>Average</p>
    </div>

    <div class="bg-[#E8D38A] text-center py-6">
        <p class="text-lg font-semibold">2</p>
        <p>Warning</p>
    </div>

    <div class="bg-[#8AA3D8] text-center py-6">
        <p class="text-lg font-semibold">0</p>
        <p>Information</p>
    </div>

    <div class="bg-gray-300 text-center py-6">
        <p class="text-lg font-semibold">0</p>
        <p>Not classified</p>
    </div>

</div>

<!-- ================= CURRENT PROBLEMS ================= -->
<h3 class="text-xl font-semibold text-gray-700 mb-4">
    Current problems
</h3>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">

    <!-- SCROLL AREA -->
    <div class="max-h-[500px] overflow-y-auto overflow-x-auto">

        <table class="w-full text-sm">
            <thead class="bg-gray-200 text-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="p-4 text-left">Time</th>
                    <th class="p-4 text-left">Host</th>
                    <th class="p-4 text-left min-w-[420px]">Problem • Severity</th>
                    <th class="p-4 text-left">Duration</th>
                    <th class="p-4 text-left">Update</th>
                    <th class="p-4 text-left">Actions</th>
                    <th class="p-4 text-left">Tags</th>
                </tr>
            </thead>

            <tbody>

                <!-- contoh row -->
                <tr class="border-t">
                    <td class="p-4">12:07:17</td>

                    <td class="p-4 text-blue-600">
                        WIFI MHS B-R <br> BELAJAR lt 2
                    </td>

                    <td class="p-4">
                        <span class="bg-yellow-300 px-3 py-2 rounded inline-block">
                            Ubiquiti AirOS: Interface wifi1ap2()
                            High error rate (&gt;2 for 5m)
                        </span>
                    </td>

                    <td class="p-4">51s</td>
                    <td class="p-4 text-blue-600">Update</td>
                    <td class="p-4">1 →</td>

                    <td class="p-4 text-xs text-gray-600">
                        class: network <br>
                        component: network
                    </td>
                </tr>

            </tbody>
        </table>

    </div>
</div>
@endsection