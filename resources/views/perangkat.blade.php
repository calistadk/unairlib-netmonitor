@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-6">
    Device Data Management
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

    <input type="text" id="searchDevice" placeholder="Search" onkeyup="filterDevice()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"/>

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

    <button onclick="openAddZabbix()"
        class="ml-auto flex items-center gap-2 px-4 py-2 bg-[#243B7C] text-white text-sm
               font-semibold rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add
    </button>

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
            <th class="px-6 py-4 text-left">Action</th>
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
            <td class="px-6 py-4 text-gray-700 text-xs">{{ trim($d['vendor'].' '.$d['model']) ?: '-' }}</td>
            <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $d['serial'] ?: '-' }}</td>
            <td class="px-6 py-4 text-xs text-gray-700">{{ $d['os'] ?: '-' }}</td>
            <td class="px-6 py-4 text-xs text-gray-700">{{ $d['location'] ?: '-' }}</td>

            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <button onclick="showDetail({{ json_encode($d) }})"
                        class="px-3 py-1 bg-[#243B7C] text-white text-xs rounded hover:bg-blue-800 transition">
                        Detail
                    </button>
                    <button onclick="confirmDelete('{{ $d['hostid'] }}', '{{ addslashes($d['host']) }}')"
                        class="px-3 py-1 bg-red-100 text-red-600 text-xs rounded hover:bg-red-200 transition">
                        Hapus
                    </button>
                </div>
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

<div id="emptyDevice" class="hidden py-12 text-center text-gray-400 text-sm">No device found.</div>
</div>
</div>

<!-- ===================================================================
     MODAL DETAIL  (dengan tombol Edit Zabbix di footer)
     =================================================================== -->
<div id="detailModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-5">
            <h3 id="modalTitle" class="text-xl font-bold text-[#243B7C]"></h3>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="divide-y divide-gray-100 text-sm" id="modalContent"></div>

        <!-- Footer: Close + Edit di Zabbix -->
        <div class="flex items-center justify-between mt-6">
            <button onclick="closeDetail()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Close
            </button>
            <button id="btnEditZabbix" onclick="openEditZabbix()"
                class="flex items-center gap-2 px-5 py-2 rounded-lg bg-[#243B7C] text-white
                       font-semibold hover:bg-blue-800 text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================
     MODAL EDIT ZABBIX
     =================================================================== -->
<div id="editZabbixModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-[60] hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 max-h-[90vh] overflow-y-auto">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-[#243B7C]">Edit Host Zabbix</h3>
                <p id="editZabbixSubtitle" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button onclick="closeEditZabbix()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Loading indicator -->
        <div id="editZabbixLoading" class="py-10 text-center text-gray-400 text-sm">
            <svg class="animate-spin w-6 h-6 mx-auto mb-2 text-[#243B7C]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Mengambil data dari Zabbix...
        </div>

        <!-- Form (hidden sampai data loaded) -->
        <form id="editZabbixForm" action="" method="POST" class="hidden">
            @csrf
            @method('PUT')

            <div class="space-y-4">

                <!-- Host Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Host Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="host_name" id="edit_host_name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <!-- IP Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        IP Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ip_address" id="edit_ip" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <!-- Interface Type & Port -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interface Type</label>
                        <select name="iface_type" id="edit_iface_type"
                            onchange="updateEditPort(this.value)"
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
                        <input type="text" name="port" id="edit_port"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>

                <!-- Host Group -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Host Group <span class="text-red-500">*</span>
                    </label>
                    <select name="group_id" id="edit_group" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">-- Loading... --</option>
                    </select>
                </div>

                <!-- Templates -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Templates
                        <span class="text-gray-400 font-normal">(bisa pilih lebih dari satu)</span>
                    </label>
                    <select name="template_ids[]" id="edit_templates" multiple
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-300 min-h-[90px]">
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Tahan Ctrl / Cmd untuk pilih lebih dari satu.</p>
                </div>

            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeEditZabbix()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold
                           hover:bg-gray-300 text-sm">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-[#243B7C] text-white font-semibold
                           hover:bg-blue-800 text-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================
     MODAL KONFIRMASI HAPUS
     =================================================================== -->
<div id="deleteModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex justify-center mb-4">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7
                             m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 01-1-1V5a1 1 0 011-1h6a1 1 0 011 1v1
                             a1 1 0 01-1 1H9z"/>
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
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================
     MODAL ADD TO ZABBIX
     =================================================================== -->
<div id="addZabbixModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-[#243B7C]">Add Host to Zabbix</h3>
                <p class="text-xs text-gray-400 mt-0.5">Daftarkan perangkat baru ke monitoring Zabbix</p>
            </div>
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

<!-- ================= SCRIPT ================= -->
<script>
// ── State ───────────────────────────────────────────────
let currentDevice      = null;   // data device yang sedang di-detail
let zabbixOptions      = null;   // { groups, templates } cache

// ── Filter ──────────────────────────────────────────────
function filterDevice() {
    const search = document.getElementById('searchDevice').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const iface  = document.getElementById('filterIface').value;
    let visible  = 0;

    document.querySelectorAll('.device-row').forEach(row => {
        const show = row.dataset.search.includes(search)
            && (status === '' || row.dataset.status === status)
            && (iface  === '' || row.dataset.iface  === iface);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    document.getElementById('emptyDevice').classList.toggle('hidden', visible > 0);
}

// ── Detail Modal ─────────────────────────────────────────
function showDetail(d) {
    currentDevice = d;

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
        </div>`).join('');

    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

document.getElementById('detailModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeDetail();
});

// ── Edit Zabbix Modal ────────────────────────────────────
async function openEditZabbix() {
    if (!currentDevice) return;

    const hostid = currentDevice.hostid;

    // Set subtitle
    document.getElementById('editZabbixSubtitle').textContent =
        'Mengedit: ' + currentDevice.host;

    // Set action URL form
    document.getElementById('editZabbixForm').action =
        '{{ url("/zabbix/host") }}/' + hostid;

    // Tampilkan modal, sembunyikan form, tampilkan loading
    document.getElementById('editZabbixModal').classList.remove('hidden');
    document.getElementById('editZabbixLoading').classList.remove('hidden');
    document.getElementById('editZabbixForm').classList.add('hidden');

    try {
        // Fetch data host + options paralel
        const [hostRes, optRes] = await Promise.all([
            fetch('{{ url("/zabbix/host") }}/' + hostid),
            zabbixOptions
                ? Promise.resolve({ ok: true, _cached: true })
                : fetch('{{ route("zabbix.options") }}'),
        ]);

        const hostData = await hostRes.json();

        if (!zabbixOptions) {
            const optData = await optRes.json();
            zabbixOptions = optData;
        }

        // Populate dropdowns
        populateEditDropdowns(zabbixOptions, hostData);

        // Pre-fill fields
        document.getElementById('edit_host_name').value = hostData.host ?? '';

        // Interface (ambil interface utama)
        const mainIface = (hostData.interfaces ?? []).find(i => i.main === '1')
                       ?? hostData.interfaces?.[0]
                       ?? {};

        document.getElementById('edit_ip').value   = mainIface.ip   ?? '';
        document.getElementById('edit_port').value = mainIface.port ?? '10050';

        // Map type number → string
        const ifaceMap = { '1': 'ZBX', '2': 'SNMP', '3': 'IPMI', '4': 'JMX' };
        const ifaceStr = ifaceMap[mainIface.type] ?? 'ZBX';
        document.getElementById('edit_iface_type').value = ifaceStr;

        // Sembunyikan loading, tampilkan form
        document.getElementById('editZabbixLoading').classList.add('hidden');
        document.getElementById('editZabbixForm').classList.remove('hidden');

    } catch (err) {
        document.getElementById('editZabbixLoading').innerHTML =
            '<p class="text-red-500">Gagal memuat data: ' + err.message + '</p>';
    }
}

function populateEditDropdowns(options, hostData) {
    // Groups
    const groupSel = document.getElementById('edit_group');
    const activeGroupIds = (hostData.groups ?? []).map(g => g.groupid);
    groupSel.innerHTML = '<option value="">-- Pilih group --</option>';
    (options.groups ?? []).forEach(g => {
        const opt = document.createElement('option');
        opt.value       = g.groupid;
        opt.textContent = g.name;
        // Pilih group pertama yang dimiliki host
        if (activeGroupIds.includes(g.groupid)) opt.selected = true;
        groupSel.appendChild(opt);
    });

    // Templates
    const tplSel = document.getElementById('edit_templates');
    const activeTemplateIds = (hostData.parentTemplates ?? []).map(t => t.templateid);
    tplSel.innerHTML = '';
    (options.templates ?? []).forEach(t => {
        const opt = document.createElement('option');
        opt.value       = t.templateid;
        opt.textContent = t.name;
        if (activeTemplateIds.includes(t.templateid)) opt.selected = true;
        tplSel.appendChild(opt);
    });
}

function closeEditZabbix() {
    document.getElementById('editZabbixModal').classList.add('hidden');
}

document.getElementById('editZabbixModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeEditZabbix();
});

function updateEditPort(ifaceType) {
    const ports = { ZBX: '10050', SNMP: '161', IPMI: '623', JMX: '12345' };
    document.getElementById('edit_port').value = ports[ifaceType] ?? '10050';
}

// ── Delete Modal ─────────────────────────────────────────
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

// ── Add to Zabbix Modal ───────────────────────────────────
function openAddZabbix(prefillIp = '', prefillHost = '') {
    if (prefillIp)   document.getElementById('zbx_ip').value        = prefillIp;
    if (prefillHost) document.getElementById('zbx_host_name').value = prefillHost;
    document.getElementById('addZabbixModal').classList.remove('hidden');
    if (!zabbixOptions) loadZabbixOptions();
    else repopulateAddDropdowns();
}
function closeAddZabbix() { document.getElementById('addZabbixModal').classList.add('hidden'); }
document.getElementById('addZabbixModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddZabbix();
});

async function loadZabbixOptions() {
    try {
        const res  = await fetch('{{ route("zabbix.options") }}');
        zabbixOptions = await res.json();
        repopulateAddDropdowns();
    } catch (err) {
        document.getElementById('zbx_group').innerHTML =
            '<option value="">Gagal memuat groups</option>';
    }
}

function repopulateAddDropdowns() {
    const groupSel = document.getElementById('zbx_group');
    groupSel.innerHTML = '<option value="">-- Pilih group --</option>';
    (zabbixOptions.groups ?? []).forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.groupid; opt.textContent = g.name;
        groupSel.appendChild(opt);
    });

    const tplSel = document.getElementById('zbx_templates');
    tplSel.innerHTML = '';
    (zabbixOptions.templates ?? []).forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.templateid; opt.textContent = t.name;
        tplSel.appendChild(opt);
    });
}

function updateDefaultPort(ifaceType) {
    const ports = { ZBX: '10050', SNMP: '161', IPMI: '623', JMX: '12345' };
    document.getElementById('zbx_port').value = ports[ifaceType] ?? '10050';
}

// ── Auto-dismiss flash ────────────────────────────────────
['flashSuccess', 'flashError'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => el.style.display = 'none', 5000);
});
</script>

@endsection