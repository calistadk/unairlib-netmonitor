@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-6">
    Device Data Management
</h2>

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <input
        type="text"
        id="searchDevice"
        placeholder="Search ID, IP, Series, or Brand"
        onkeyup="filterDevice()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
    />

    <select
        id="filterDevice"
        onchange="filterDevice()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Devices</option>
        <option value="Router">Router</option>
        <option value="Server">Server</option>
        <option value="Switch">Switch</option>
        <option value="Wi-Fi">Wi-Fi</option>
        <option value="Desktop">Desktop</option>
        <option value="Laptop">Laptop</option>
    </select>

    <a href="{{ route('perangkat.create') }}"
        class="ml-auto inline-block bg-blue-700 hover:bg-blue-800
        text-white font-semibold px-6 py-2 rounded-lg">
        + Add Devices
    </a>

</div>

<!-- ================= SUCCESS MESSAGE ================= -->
@if (session('success'))
<div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<!-- ================= TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
<div class="overflow-x-auto">

<table class="w-full text-sm" id="deviceTable">

    <thead class="text-[#243B7C] font-semibold border-b border-gray-200">
        <tr>
            <th class="px-6 py-4 text-left">ID</th>
            <th class="px-6 py-4 text-left">Type</th>
            <th class="px-6 py-4 text-left">Brand & Model</th>
            <th class="px-6 py-4 text-left">Series</th>
            <th class="px-6 py-4 text-left">IP</th>
            <th class="px-6 py-4 text-left">MAC</th>
            <th class="px-6 py-4 text-left">Location</th>
            <th class="px-6 py-4 text-left">Status</th>
            <th class="px-6 py-4 text-left">Purchase Date</th>
            <th class="px-6 py-4 text-left">Warranty</th>
            <th class="px-6 py-4 text-left">Action</th>
        </tr>
    </thead>

    <tbody id="deviceBody" class="divide-y divide-gray-100">

        @forelse ($devices as $device)
        <tr class="hover:bg-gray-50 transition device-row"
            data-type="{{ $device->type }}"
            data-search="{{ strtolower($device->device_id.' '.$device->brand_model.' '.$device->serial_number.' '.$device->ip_address) }}">

            <td class="px-6 py-4 font-medium">{{ $device->device_id }}</td>
            <td class="px-6 py-4">{{ $device->type }}</td>
            <td class="px-6 py-4">{{ $device->brand_model ?? '-' }}</td>
            <td class="px-6 py-4">{{ $device->serial_number ?? '-' }}</td>
            <td class="px-6 py-4 font-mono text-xs">{{ $device->ip_address ?? '-' }}</td>
            <td class="px-6 py-4 font-mono text-xs">{{ $device->mac_address ?? '-' }}</td>
            <td class="px-6 py-4">{{ $device->location ?? '-' }}</td>

            <td class="px-6 py-4">
                @php
                    $statusColor = match($device->status) {
                        'Aktif'       => 'bg-green-100 text-green-700',
                        'Rusak'       => 'bg-red-100 text-red-700',
                        'Maintenance' => 'bg-yellow-100 text-yellow-700',
                        'Cadangan'    => 'bg-gray-100 text-gray-600',
                        default       => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                    {{ $device->status }}
                </span>
            </td>

            <td class="px-6 py-4 whitespace-nowrap">
                {{ $device->purchase_date ? \Carbon\Carbon::parse($device->purchase_date)->format('d-m-Y') : '-' }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap">
                {{ $device->warranty_expiry ? \Carbon\Carbon::parse($device->warranty_expiry)->format('d-m-Y') : '-' }}
            </td>

            <td class="px-6 py-4">
                <div class="flex gap-3">

                    <!-- EDIT -->
                    <a href="{{ route('perangkat.edit', $device->id) }}"
                       class="text-gray-600 hover:text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.939a4.5 4.5 0 01-1.897 1.13l-2.685.805.805-2.685a4.5 4.5 0 011.13-1.897L16.862 4.487z"/>
                        </svg>
                    </a>

                    <!-- DELETE -->
                    <form action="{{ route('perangkat.destroy', $device->id) }}" method="POST"
                          onsubmit="return confirm('Delete this device?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3-3h4a1 1 0 011 1v1H9V5a1 1 0 011-1z"/>
                            </svg>
                        </button>
                    </form>

                </div>
            </td>

        </tr>
        @empty
        @endforelse

    </tbody>
</table>

<!-- EMPTY STATE -->
<div id="emptyDevice" class="hidden py-12 text-center text-gray-400 text-sm">
    No device found.
</div>

@if ($devices->isEmpty())
<div class="py-12 text-center text-gray-400 text-sm">
    No devices yet. Click <a href="{{ route('perangkat.create') }}" class="text-blue-600 underline">+ Add Devices</a> to add one.
</div>
@endif

</div>
</div>

<!-- ================= FILTER SCRIPT ================= -->
<script>
function filterDevice() {
    const search = document.getElementById('searchDevice').value.toLowerCase()
    const type   = document.getElementById('filterDevice').value
    const rows   = document.querySelectorAll('.device-row')

    let visible = 0

    rows.forEach(row => {
        const matchSearch = row.dataset.search.includes(search)
        const matchType   = type === '' || row.dataset.type === type

        if (matchSearch && matchType) {
            row.classList.remove('hidden')
            visible++
        } else {
            row.classList.add('hidden')
        }
    })

    document.getElementById('emptyDevice').classList.toggle('hidden', visible > 0)
}
</script>

@endsection