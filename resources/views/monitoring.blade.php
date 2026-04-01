@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Monitoring
</h2>

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <input
        type="text"
        id="searchMonitoring"
        placeholder="Search"
        onkeyup="filterMonitoring()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
    />

    <select id="filterStatus" onchange="filterMonitoring()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Status</option>
        <option value="Online">Online</option>
        <option value="Offline">Offline</option>
        <option value="Unknown">Unknown</option>
    </select>

    <select id="filterIface" onchange="filterMonitoring()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Interface</option>
        <option value="ZBX">ZBX</option>
        <option value="SNMP">SNMP</option>
        <option value="IPMI">IPMI</option>
        <option value="JMX">JMX</option>
    </select>

</div>

<!-- ================= MONITORING TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
<div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
<table class="w-full text-sm border border-gray-200" id="deviceTable">

            <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
                <tr>
                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">Name</th>
                    <th class="px-6 py-4 text-left">Interface</th>
                    <th class="px-6 py-4 text-left">Availability</th>
                    <th class="px-6 py-4 text-left">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300" id="monitoringBody">

                @foreach ($perangkat as $item)
                <tr class="hover:bg-gray-50 transition monitoring-row"
                    data-status="{{ $item['status'] }}"
                    data-iface="{{ $item['iface_type'] }}"
                    data-search="{{ strtolower($item['id'].' '.$item['nama'].' '.$item['interface'].' '.$item['iface_type']) }}">

                    <td class="px-6 py-4 text-gray-700">{{ $item['id'] }}</td>
                    <td class="px-6 py-4 text-gray-800 font-medium">{{ $item['nama'] }}</td>

                    <td class="px-6 py-4">
                        <span class="text-gray-700 font-mono text-xs">{{ $item['interface'] }}</span>
                    </td>

                    <td class="px-6 py-4">
                        @php
                            $badgeColor = match($item['status']) {
                                'Online'  => 'bg-green-500',
                                'Offline' => 'bg-red-500',
                                default   => 'bg-yellow-400',
                            };
                        @endphp
                        <span class="text-white text-xs font-bold px-2 py-1 rounded {{ $badgeColor }}">
                            {{ $item['iface_type'] }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        <a href="/monitoring/{{ $item['id'] }}"
                           class="bg-[#1a1a2e] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#243B7C] transition inline-block">
                            Detail
                        </a>
                    </td>

                </tr>
                @endforeach

            </tbody>
        </table>

        <div id="emptyMonitoring" class="hidden py-12 text-center text-gray-400 text-sm">
            No device found.
        </div>

    </div>
</div>

<script>
function filterMonitoring() {
    const search = document.getElementById('searchMonitoring').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const iface  = document.getElementById('filterIface').value;
    const rows   = document.querySelectorAll('.monitoring-row');
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

    document.getElementById('emptyMonitoring').classList.toggle('hidden', visible > 0);
}
</script>

@endsection