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
        placeholder="Search"
        onkeyup="filterDevice()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
    />

    <select id="filterStatus" onchange="filterDevice()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Status</option>
        <option value="Online">Online</option>
        <option value="Offline">Offline</option>
        <option value="Unknown">Unknown</option>
    </select>

    <select id="filterIface" onchange="filterDevice()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Interface</option>
        <option value="ZBX">ZBX</option>
        <option value="SNMP">SNMP</option>
        <option value="IPMI">IPMI</option>
        <option value="JMX">JMX</option>
    </select>

</div>

<!-- ================= TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
<div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
<table class="w-full text-sm border border-gray-200" id="deviceTable">

    <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
        <tr>
            <th class="px-6 py-4 text-left">Host</th>
            <th class="px-6 py-4 text-left">IP</th>
            <th class="px-6 py-4 text-left">Interface</th>
            <th class="px-6 py-4 text-left">Status</th>
            <th class="px-6 py-4 text-left">Group</th>
            <th class="px-6 py-4 text-left">Type</th>
            <th class="px-6 py-4 text-left">Vendor & Model</th>
            <th class="px-6 py-4 text-left">Serial</th>
            <th class="px-6 py-4 text-left">OS</th>
            <th class="px-6 py-4 text-left">Location</th>
            <th class="px-6 py-4 text-left">Detail</th>
        </tr>
    </thead>

    <tbody id="deviceBody" class="divide-y divide-gray-300">

        @forelse ($devices as $d)
        <tr class="hover:bg-gray-50 transition device-row"
            data-status="{{ $d['status'] }}"
            data-iface="{{ $d['iface_type'] }}"
            data-search="{{ strtolower($d['host'].' '.$d['ip'].' '.$d['serial'].' '.$d['vendor'].' '.$d['model'].' '.$d['groups']) }}">

            <td class="px-6 py-4 font-medium text-gray-800">{{ $d['host'] }}</td>
            <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $d['ip'] }}</td>

            <td class="px-6 py-4">
                @php
                    $ibg = match($d['iface_type']) {
                        'ZBX'  => $d['status'] === 'Online'  ? 'bg-green-100 text-green-700'
                                : ($d['status'] === 'Offline' ? 'bg-red-100 text-red-700'
                                :                               'bg-yellow-100 text-yellow-700'),
                        'SNMP' => 'bg-blue-100 text-blue-700',
                        'IPMI' => 'bg-purple-100 text-purple-700',
                        'JMX'  => 'bg-orange-100 text-orange-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $ibg }}">
                    {{ $d['iface_type'] }}
                </span>
            </td>

            <td class="px-6 py-4">
                @php
                    $sbg = match($d['status']) {
                        'Online'  => 'bg-green-100 text-green-700',
                        'Offline' => 'bg-red-100 text-red-700',
                        default   => 'bg-yellow-100 text-yellow-700',
                    };
                @endphp
                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sbg }}">
                    {{ $d['status'] }}
                </span>
            </td>

            <td class="px-6 py-4 text-gray-600 text-xs">{{ $d['groups'] ?: '-' }}</td>
            <td class="px-6 py-4 text-gray-700">{{ $d['type'] ?: '-' }}</td>
            <td class="px-6 py-4 text-gray-700 text-xs">
                {{ trim($d['vendor'].' '.$d['model']) ?: '-' }}
            </td>
            <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $d['serial'] ?: '-' }}</td>
            <td class="px-6 py-4 text-xs text-gray-700">{{ $d['os'] ?: '-' }}</td>
            <td class="px-6 py-4 text-xs text-gray-700">{{ $d['location'] ?: '-' }}</td>

            <td class="px-6 py-4">
                <button onclick="showDetail({{ json_encode($d) }})"
                    class="px-3 py-1 bg-[#243B7C] text-white text-xs rounded hover:bg-blue-800 transition">
                    Detail
                </button>
            </td>

        </tr>
        @empty
        <tr>
            <td colspan="11" class="px-6 py-12 text-center text-gray-400">
                No hosts found from Zabbix.
            </td>
        </tr>
        @endforelse

    </tbody>
</table>

<div id="emptyDevice" class="hidden py-12 text-center text-gray-400 text-sm">
    No device found.
</div>

</div>
</div>

<!-- ================= MODAL DETAIL ================= -->
<div id="detailModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-5">
            <h3 id="modalTitle" class="text-xl font-bold text-[#243B7C]"></h3>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="divide-y divide-gray-100 text-sm" id="modalContent"></div>

        <div class="flex justify-end mt-6">
            <button onclick="closeDetail()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
function filterDevice() {
    const search = document.getElementById('searchDevice').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const iface  = document.getElementById('filterIface').value;
    const rows   = document.querySelectorAll('.device-row');
    let visible  = 0;

    rows.forEach(row => {
        const matchSearch = row.dataset.search.includes(search);
        const matchStatus = status === '' || row.dataset.status === status;
        const matchIface  = iface  === '' || row.dataset.iface  === iface;

        if (matchSearch && matchStatus && matchIface) {
            row.classList.remove('hidden');
            visible++;
        } else {
            row.classList.add('hidden');
        }
    });

    document.getElementById('emptyDevice').classList.toggle('hidden', visible > 0);
}

function showDetail(d) {
    document.getElementById('modalTitle').textContent = d.host;

    const fields = [
        ['IP Address',  d.ip],
        ['Interface',   d.iface_type],
        ['Status',      d.status],
        ['Group',       d.groups    || '-'],
        ['Tags',        d.tags      || '-'],
        ['Type',        d.type      || '-'],
        ['Vendor',      d.vendor    || '-'],
        ['Model',       d.model     || '-'],
        ['Serial No',   d.serial    || '-'],
        ['MAC Address', d.mac       || '-'],
        ['OS',          d.os        || '-'],
        ['Location',    d.location  || '-'],
        ['Asset Tag',   d.asset_tag || '-'],
        ['Hardware',    d.hardware  || '-'],
        ['Notes',       d.notes     || '-'],
    ];

    document.getElementById('modalContent').innerHTML = fields.map(([label, val]) => `
        <div class="flex gap-3 py-2">
            <span class="w-32 text-gray-400 shrink-0 text-xs">${label}</span>
            <span class="text-gray-800 font-medium text-xs">${val}</span>
        </div>
    `).join('');

    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetail();
});
</script>

@endsection