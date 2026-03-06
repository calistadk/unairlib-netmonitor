@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Edit Device
</h2>

<!-- ================= FORM CONTAINER ================= -->
<div class="bg-gray-300 rounded-3xl p-10">

    <div class="bg-white rounded-2xl p-10 shadow-sm">

        <!-- FORM DUMMY (TANPA BACKEND) -->
        <form action="#" method="POST">

            <!-- ================= FORM GRID ================= -->
            <div class="grid grid-cols-2 gap-x-10 gap-y-6">

                <!-- ID PERANGKAT -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Device ID
                    </label>
                    <input type="text"
                        value="RTR-01"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- IP ADDRESS -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        IP Address
                    </label>
                    <input type="text"
                        value="192.168.1.1"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- JENIS -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Type
                    </label>
                    <select class="w-full border rounded-lg px-4 py-2">
                        <option value="">Select device type</option>
                        <option value="Wi-Fi">Wi-Fi</option>
                        <option value="Router" selected>Router</option>
                        <option value="Hub">Hub</option>
                        <option value="Server">Server</option>
                        <option value="Desktop">Desktop</option>
                        <option value="Laptop">Laptop</option>
                    </select>
                </div>

                <!-- MAC ADDRESS -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        MAC Address
                    </label>
                    <input type="text"
                        value="AA:BB:CC"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- MEREK & MODEL -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Brand & Model
                    </label>
                    <input type="text"
                        value="Cisco ISR 4321"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- LOKASI -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Location (Building, Room, Rack)
                    </label>
                    <input type="text"
                        value="MOVIO"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- SERIAL NUMBER -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Serial Number
                    </label>
                    <input type="text"
                        value="FTX12345"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- TANGGAL PEMBELIAN -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Purchase Date
                    </label>
                    <input type="date"
                        value="2020-12-01"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- MASA GARANSI -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Warranty Expiry
                    </label>
                    <input type="date"
                        value="2026-12-01"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

            </div>

            <!-- ================= ACTION BUTTON ================= -->
            <div class="flex justify-end gap-4 mt-10">
                <a href="/perangkat"
                   class="px-6 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">
                    Cancel
                </a>

                <button type="button"
                        class="px-6 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800">
                    Save Changes
                </button>
            </div>

        </form>

    </div>
</div>

@endsection