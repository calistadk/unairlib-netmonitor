@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-6">
    Activity Log History
</h2>

<!-- ================= SUCCESS MESSAGE ================= -->
@if (session('success'))
<div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <input
        type="text"
        id="searchInput"
        placeholder="Search"
        onkeyup="filterLog()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
    />

    <select
        id="filterTanggal"
        onchange="filterLog()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Dates</option>
        @foreach ($dates as $date)
        <option value="{{ $date }}">{{ $date }}</option>
        @endforeach
    </select>

    <select
        id="filterType"
        onchange="filterLog()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Types</option>
        <option value="Perubahan Data">Perubahan Data</option>
        <option value="Maintenance">Maintenance</option>
        <option value="Kerusakan">Kerusakan</option>
        <option value="Perpindahan Lokasi">Perpindahan Lokasi</option>
    </select>

    <button onclick="openModal()"
        class="ml-auto bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-2 rounded-lg transition whitespace-nowrap">
        + Add Log
    </button>

</div>

<!-- ================= LOG TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
<div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
<table class="w-full text-sm border border-gray-200" id="deviceTable">

            <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
                <tr>
                    <th class="px-6 py-4 text-left">Time</th>
                    <th class="px-6 py-4 text-left">Device ID</th>
                    <th class="px-6 py-4 text-left">Types of Activities</th>
                    <th class="px-6 py-4 text-left">Details</th>
                    <th class="px-6 py-4 text-left">User</th>
                </tr>
            </thead>

            <tbody id="logBody" class="divide-y divide-gray-300">

                @php
                    $badgeClass = [
                        'Perubahan Data'     => 'bg-blue-100 text-blue-700',
                        'Maintenance'        => 'bg-yellow-100 text-yellow-700',
                        'Kerusakan'          => 'bg-red-100 text-red-600',
                        'Perpindahan Lokasi' => 'bg-green-100 text-green-700',
                    ];
                @endphp

                @forelse ($logs as $log)
                <tr class="hover:bg-gray-50 transition log-row"
                    data-tanggal="{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y') }}"
                    data-type="{{ $log->type }}"
                    data-search="{{ strtolower(($log->device->device_id ?? '') . ' ' . $log->type . ' ' . $log->detail) }}">

                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i') }}
                    </td>

                    <td class="px-6 py-4 text-gray-800 font-medium">
                        {{ $log->device->device_id ?? '-' }}
                    </td>

                    <td class="px-6 py-4">
                        @php $cls = $badgeClass[$log->type] ?? 'bg-gray-100 text-gray-600'; @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cls }}">
                            {{ $log->type }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        {{ $log->detail }}
                        @if ($log->location_before && $log->location_after)
                            <span class="text-xs text-gray-400 block mt-1">
                                {{ $log->location_before }} → {{ $log->location_after }}
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-gray-700">
                        {{ $log->user->name ?? 'System' }}
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                        No activity logs yet.
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>

        <div id="emptyState" class="hidden py-12 text-center text-gray-400 text-sm">
            No logs match the search.
        </div>

    </div>
</div>

<!-- ================= MODAL TAMBAH LOG ================= -->
<div id="logModal"
     class="fixed inset-0 bg-grey bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8">

        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-[#243B7C]">Add Activity Log</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('log.store') }}" method="POST">
            @csrf

            @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="space-y-4">

                <!-- Device -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Device <span class="text-red-500">*</span></label>
                    <select name="device_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                        <option value="">Select device</option>
                        @foreach ($devices as $device)
                        <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                            {{ $device->device_id }} — {{ $device->brand_model ?? $device->type }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Type of Activity <span class="text-red-500">*</span></label>
                    <select name="type" id="logType"
                        onchange="toggleLocationFields()"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                        <option value="">Select type</option>
                        <option value="Perubahan Data"     {{ old('type') == 'Perubahan Data'     ? 'selected' : '' }}>Perubahan Data</option>
                        <option value="Maintenance"        {{ old('type') == 'Maintenance'        ? 'selected' : '' }}>Maintenance</option>
                        <option value="Kerusakan"          {{ old('type') == 'Kerusakan'          ? 'selected' : '' }}>Kerusakan</option>
                        <option value="Perpindahan Lokasi" {{ old('type') == 'Perpindahan Lokasi' ? 'selected' : '' }}>Perpindahan Lokasi</option>
                    </select>
                </div>

                <!-- Detail -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Detail <span class="text-red-500">*</span></label>
                    <textarea name="detail" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 resize-none"
                        placeholder="Describe the activity..."
                        required>{{ old('detail') }}</textarea>
                </div>

                <!-- Location fields (hanya muncul saat Perpindahan Lokasi) -->
                <div id="locationFields" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Location Before</label>
                        <input type="text" name="location_before" value="{{ old('location_before') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
                            placeholder="e.g. MOVIO">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Location After</label>
                        <input type="text" name="location_after" value="{{ old('location_after') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
                            placeholder="e.g. R.Acaraya">
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800">
                    Save Log
                </button>
            </div>

        </form>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
    function openModal()  { document.getElementById('logModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('logModal').classList.add('hidden'); }

    document.getElementById('logModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    function toggleLocationFields() {
        const type   = document.getElementById('logType').value;
        const fields = document.getElementById('locationFields');
        fields.classList.toggle('hidden', type !== 'Perpindahan Lokasi');
    }

    @if ($errors->any())
        openModal();
        toggleLocationFields();
    @endif

    function filterLog() {
        const search  = document.getElementById('searchInput').value.toLowerCase();
        const tanggal = document.getElementById('filterTanggal').value;
        const type    = document.getElementById('filterType').value;
        const rows    = document.querySelectorAll('.log-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const matchSearch  = row.dataset.search.includes(search);
            const matchTanggal = tanggal === '' || row.dataset.tanggal === tanggal;
            const matchType    = type === '' || row.dataset.type === type;

            if (matchSearch && matchTanggal && matchType) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        document.getElementById('emptyState').classList.toggle('hidden', visibleCount > 0);
    }
</script>

@endsection