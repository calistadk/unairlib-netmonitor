@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Add New Device
</h2>

<!-- ================= FORM CONTAINER ================= -->
<div class="bg-gray-300 rounded-3xl p-10">

    <div class="bg-white rounded-2xl p-10 shadow-sm">

        <form action="{{ url('/tambah-perangkat') }}" method="POST">
            @csrf

            <!-- ================= FORM GRID ================= -->
            <div class="grid grid-cols-2 gap-x-10 gap-y-6">

                <!-- ID PERANGKAT -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Device ID
                    </label>
                    <input type="text" name="id_perangkat"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <!-- IP ADDRESS -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        IP Address
                    </label>
                    <input type="text" name="ip_address"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- JENIS -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Type
                    </label>
                    <select name="jenis"
                        class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Select device type</option>
                        <option value="Wi-Fi">Wi-Fi</option>
                        <option value="Router">Router</option>
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
                    <input type="text" name="mac_address"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- MEREK & MODEL -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Brand & Model
                    </label>
                    <input type="text" name="merek_model"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- LOKASI -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Location (Building, Room, Rack)
                    </label>
                    <input type="text" name="lokasi"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- SERIAL NUMBER -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Serial Number
                    </label>
                    <input type="text" name="serial_number"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- TANGGAL PEMBELIAN -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Purchase Date
                    </label>
                    <input type="date" name="tanggal_pembelian"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <!-- MASA GARANSI -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Warranty Expiry
                    </label>
                    <input type="date" name="masa_garansi"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

            </div>

            <!-- ================= ACTION BUTTON ================= -->
            <div class="flex justify-end gap-4 mt-10">
                <a href="{{ url('/perangkat') }}"
                    class="px-6 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">
                    Cancel
                </a>

                <button type="submit"
                        class="px-6 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800">
                    Save Device
                </button>
            </div>

        </form>

    </div>
</div>

@endsection