@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Add New Device
</h2>

<!-- ================= FORM CONTAINER ================= -->
<div class="bg-gray-300 rounded-3xl p-10">
    <div class="bg-white rounded-2xl p-10 shadow-sm">

        <form action="{{ route('perangkat.store') }}" method="POST">
            @csrf

            @if ($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- ================= FORM GRID ================= -->
            <div class="grid grid-cols-2 gap-x-10 gap-y-6">

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Device ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="device_id" value="{{ old('device_id') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        IP Address
                    </label>
                    <input type="text" name="ip_address" value="{{ old('ip_address') }}"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select device type</option>
                        @foreach (['Wi-Fi','Router','Switch','Hub','Server','Desktop','Laptop'] as $t)
                        <option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        MAC Address
                    </label>
                    <input type="text" name="mac_address" value="{{ old('mac_address') }}"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Brand & Model
                    </label>
                    <input type="text" name="brand_model" value="{{ old('brand_model') }}"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Location (Building, Room, Rack)
                    </label>
                    <input type="text" name="location" value="{{ old('location') }}"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Serial Number
                    </label>
                    <input type="text" name="serial_number" value="{{ old('serial_number') }}"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Status
                    </label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg">
                        @foreach (['Aktif','Rusak','Maintenance','Cadangan'] as $s)
                        <option value="{{ $s }}" {{ old('status', 'Aktif') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Purchase Date
                    </label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                        class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        Warranty Expiry
                    </label>
                    <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}"
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