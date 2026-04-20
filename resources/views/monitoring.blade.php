@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Monitoring
</h2>

<!-- ================= FLASH MESSAGES ================= -->
@if (session('success'))
<div id="flashSuccess"
     class="flex items-center gap-3 mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg text-sm">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
         viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div id="flashError"
     class="flex items-center gap-3 mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
         viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <input type="text" id="searchMonitoring" placeholder="Search" onkeyup="filterMonitoring()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"/>

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

    <select id="filterGroup" onchange="filterMonitoring()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Group</option>
        @foreach(collect($perangkat)->pluck('groups')->filter()->unique()->sort() as $group)
            <option value="{{ $group }}">{{ $group }}</option>
        @endforeach
    </select>

    <select id="filterHealth" onchange="filterMonitoring()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Health</option>
        <option value="Healthy">Healthy</option>
        <option value="Info">Info</option>
        <option value="Warning">Warning</option>
        <option value="Critical">Critical</option>
        <option value="Down">Down</option>
        <option value="Unknown">Unknown</option>
    </select>

    @if(auth()->user()->isAdmin())
    <button onclick="openAddZabbix()"
        class="ml-auto flex items-center gap-2 px-4 py-2 bg-[#243B7C] text-white text-sm
               font-semibold rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add
    </button>
    @endif

</div>

<!-- ================= TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
<div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
<table class="w-full text-sm border border-gray-200" id="deviceTable">

    <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
        <tr>
            <th class="px-6 py-4 text-left">No.</th>
            <th class="px-6 py-4 text-left">ID</th>
            <th class="px-6 py-4 text-left">Name</th>
            <th class="px-6 py-4 text-left">Interface</th>
            <th class="px-6 py-4 text-left">Availability</th>
            <th class="px-6 py-4 text-left">Health</th>
            <th class="px-6 py-4 text-left">Group</th>
            <th class="px-6 py-4 text-left">Action</th>
        </tr>
    </thead>

    <tbody class="divide-y divide-gray-300" id="monitoringBody">

        @foreach ($perangkat as $item)
        <tr class="hover:bg-gray-50 transition monitoring-row"
            data-status="{{ $item['status'] }}"
            data-iface="{{ $item['iface_type'] }}"
            data-group="{{ $item['groups'] ?? '' }}"
            data-health="{{ $item['health']['label'] ?? '' }}"
            data-search="{{ strtolower(($item['id'] ?? '').' '.($item['nama'] ?? '').' '.($item['interface'] ?? '').' '.($item['iface_type'] ?? '').' '.($item['groups'] ?? '')) }}">

            <td class="px-6 py-4 text-gray-500">{{ $loop->iteration }}</td>
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

            {{-- HEALTH --}}
            <td class="px-6 py-4">
                @if(isset($item['health']))
                <span class="text-white text-xs font-bold px-2 py-1 rounded {{ $item['health']['color'] }}">
                    {{ $item['health']['label'] }}
                </span>
                @else
                <span class="text-gray-400 text-xs">-</span>
                @endif
            </td>

            <td class="px-6 py-4 text-gray-600 text-xs">{{ $item['groups'] ?? '-' }}</td>

            {{-- ACTION: Detail untuk semua, Hapus hanya admin --}}
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <a href="/monitoring/{{ $item['id'] }}"
                       class="bg-[#1a1a2e] text-white text-xs px-4 py-2 rounded-lg
                              hover:bg-[#243B7C] transition inline-block">
                        Detail
                    </a>
                    @if(auth()->user()->isAdmin())
                    <button onclick="confirmDelete('{{ $item['id'] }}', '{{ addslashes($item['nama']) }}')"
                        class="text-red-600 border border-red-300 text-xs px-3 py-2 rounded-lg
                               hover:bg-red-50 transition">
                        Hapus
                    </button>
                    @endif
                </div>
            </td>

        </tr>
        @endforeach

    </tbody>
</table>

<div id="emptyMonitoring" class="{{ count($perangkat) > 0 ? 'hidden' : '' }} py-16 text-center text-gray-400 text-sm">
    @if($zabbixDown)
        <svg class="w-10 h-10 mx-auto mb-3 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span class="block text-red-400 font-semibold mb-1">Zabbix Server Unreachable</span>
        <span class="text-xs text-gray-400">Could not connect to Zabbix. Please check the server and try again.</span>
    @else
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span class="block mb-1">No devices found.</span>
        <span class="text-xs text-gray-400">Make sure Zabbix is connected.</span>
    @endif
</div>
</div>
</div>

<!-- ================= MODAL KONFIRMASI HAPUS (admin only) ================= -->
@if(auth()->user()->isAdmin())
<div id="deleteModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex justify-center mb-4">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 01-1-1V5a1 1 0 011-1h6a1 1 0 011 1v1a1 1 0 01-1 1H9z"/>
                </svg>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-800 text-center mb-1">Hapus Host?</h3>
        <p class="text-sm text-gray-500 text-center mb-1">Kamu akan menghapus:</p>
        <p id="deleteHostName" class="text-center font-semibold text-[#243B7C] mb-2"></p>
        <p class="text-xs text-red-500 text-center mb-6">
            Host akan dihapus permanen dari <strong>Zabbix</strong> dan <strong>database lokal</strong>.
            Tindakan ini tidak bisa dibatalkan.
        </p>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
        </form>

        <div class="flex gap-3">
            <button type="button" onclick="closeDelete()"
                class="flex-1 px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold
                       hover:bg-gray-300 text-sm">
                Batal
            </button>
            <button type="button" onclick="submitDelete()"
                class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold
                       hover:bg-red-700 text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/>
                </svg>
                Hapus
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL ADD TO ZABBIX (admin only) ================= -->
<div id="addZabbixModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-[#243B7C]">Add Host to Zabbix</h3>
            <button onclick="closeAddZabbix()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('zabbix.host.store') }}" method="POST">
            @csrf
            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Host Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="host_name" id="zbx_host_name" required
                        placeholder="e.g. server-01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        IP Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ip_address" id="zbx_ip" required
                        placeholder="e.g. 192.168.1.10"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interface Type</label>
                        <select name="iface_type" id="zbx_iface_type" onchange="updateDefaultPort(this.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <option value="ZBX">ZBX (Agent)</option>
                            <option value="SNMP">SNMP</option>
                            <option value="IPMI">IPMI</option>
                            <option value="JMX">JMX</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                        <input type="text" name="port" id="zbx_port" value="10050"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Host Group <span class="text-red-500">*</span>
                    </label>
                    <select name="group_id" id="zbx_group" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">-- Loading groups... --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Templates
                        <span class="text-gray-400 font-normal">(opsional, bisa pilih lebih dari satu)</span>
                    </label>
                    <select name="template_ids[]" id="zbx_templates" multiple
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300 min-h-[80px]">
                        <option value="" disabled>-- Loading templates... --</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Tahan Ctrl / Cmd untuk pilih lebih dari satu.</p>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeAddZabbix()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-[#243B7C] text-white font-semibold hover:bg-blue-800
                           text-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                         viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Host
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ================= SCRIPT ================= -->
<script>
function filterMonitoring() {
    const search = document.getElementById('searchMonitoring').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const iface  = document.getElementById('filterIface').value;
    const group  = document.getElementById('filterGroup').value;
    const health = document.getElementById('filterHealth').value;
    const rows   = document.querySelectorAll('.monitoring-row');
    let visible  = 0;

    rows.forEach(row => {
        const show = row.dataset.search.includes(search)
            && (status === '' || row.dataset.status === status)
            && (iface  === '' || row.dataset.iface  === iface)
            && (group  === '' || row.dataset.group  === group)
            && (health === '' || row.dataset.health === health);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    document.getElementById('emptyMonitoring').classList.toggle('hidden', visible > 0);
}

@if(auth()->user()->isAdmin())
function confirmDelete(hostid, hostName) {
    document.getElementById('deleteHostName').textContent = hostName;
    document.getElementById('deleteForm').action = '{{ url("/zabbix/host") }}/' + hostid;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDelete() { document.getElementById('deleteModal').classList.add('hidden'); }
function submitDelete() { document.getElementById('deleteForm').submit(); }
document.getElementById('deleteModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeDelete();
});

let zabbixOptionsLoaded = false;
function openAddZabbix(prefillIp = '', prefillHost = '') {
    if (prefillIp)   document.getElementById('zbx_ip').value        = prefillIp;
    if (prefillHost) document.getElementById('zbx_host_name').value = prefillHost;
    document.getElementById('addZabbixModal').classList.remove('hidden');
    if (!zabbixOptionsLoaded) loadZabbixOptions();
}
function closeAddZabbix() { document.getElementById('addZabbixModal').classList.add('hidden'); }
document.getElementById('addZabbixModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddZabbix();
});

async function loadZabbixOptions() {
    try {
        const res  = await fetch('{{ route("zabbix.options") }}');
        const data = await res.json();

        const groupSel = document.getElementById('zbx_group');
        groupSel.innerHTML = '<option value="">-- Pilih group --</option>';
        (data.groups || []).forEach(g => {
            const opt = document.createElement('option');
            opt.value = g.groupid; opt.textContent = g.name;
            groupSel.appendChild(opt);
        });

        const tplSel = document.getElementById('zbx_templates');
        tplSel.innerHTML = '';
        (data.templates || []).forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.templateid; opt.textContent = t.name;
            tplSel.appendChild(opt);
        });

        zabbixOptionsLoaded = true;
    } catch (err) {
        document.getElementById('zbx_group').innerHTML =
            '<option value="">Gagal memuat groups</option>';
    }
}

function updateDefaultPort(ifaceType) {
    const ports = { ZBX: '10050', SNMP: '161', IPMI: '623', JMX: '12345' };
    document.getElementById('zbx_port').value = ports[ifaceType] ?? '10050';
}
@endif

['flashSuccess', 'flashError'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => el.style.display = 'none', 5000);
});
</script>

@endsection