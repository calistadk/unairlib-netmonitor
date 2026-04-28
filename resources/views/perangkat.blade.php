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

<!-- ================= TAB BUTTONS ================= -->
<div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-xl w-fit">
    <button onclick="switchTab('active')" id="tab-active"
        class="px-5 py-2 rounded-lg text-sm font-semibold transition bg-white text-[#243B7C] shadow-sm">
        Active Devices
        <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
            {{ count($perangkat) }}
        </span>
    </button>
    <button onclick="switchTab('broken')" id="tab-broken"
        class="px-5 py-2 rounded-lg text-sm font-semibold transition text-gray-500 hover:text-gray-700">
        Broken Devices
        <span class="ml-2 bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full">
            {{ $brokenDevices->count() }}
        </span>
    </button>
</div>

<!-- ===============================================================
     PANEL: ACTIVE DEVICES
     =============================================================== -->
<div id="panel-active">

    <!-- SEARCH, FILTER, EXPORT, ADD — satu baris -->
    <div class="flex items-center gap-2 mb-6 flex-wrap">

        <input type="text" id="searchDevice" placeholder="Search" onkeyup="filterDevice()"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-56
                   focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"/>

        <select id="filterStatus" onchange="filterDevice()"
            class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
            <option value="">All Status</option>
            <option value="Online">Online</option>
            <option value="Offline">Offline</option>
            <option value="Unknown">Unknown</option>
        </select>

        <select id="filterIface" onchange="filterDevice()"
            class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
            <option value="">All Interface</option>
            <option value="ZBX">ZBX</option>
            <option value="SNMP">SNMP</option>
            <option value="IPMI">IPMI</option>
            <option value="JMX">JMX</option>
        </select>

        <select id="filterGroup" onchange="filterDevice()"
            class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
            <option value="">All Group</option>
            @foreach(collect($perangkat)->pluck('groups')->filter()->unique()->sort() as $group)
                <option value="{{ $group }}">{{ $group }}</option>
            @endforeach
        </select>

        <!-- Export Button -->
        <button onclick="openExportModal()"
            class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm
                   font-semibold rounded-lg hover:bg-green-700 transition whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
        </button>

        @if(auth()->user()->isAdmin())
        <button onclick="openAddZabbix()"
            class="flex items-center gap-2 px-4 py-2 bg-[#243B7C] text-white text-sm
                   font-semibold rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                 viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add
        </button>
        @endif

    </div>

    <!-- TABLE ACTIVE -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
    <table class="w-full text-sm border border-gray-200" id="deviceTable">
        <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
            <tr>
                <th class="px-6 py-4 text-left">No.</th>
                <th class="px-6 py-4 text-left">Host</th>
                <th class="px-6 py-4 text-left">Interface</th>
                <th class="px-6 py-4 text-left">Availability</th>
                <th class="px-6 py-4 text-left">Group</th>
                <th class="px-6 py-4 text-left">Type</th>
                <th class="px-6 py-4 text-left">Vendor & Model</th>
                <th class="px-6 py-4 text-left">Serial</th>
                <th class="px-6 py-4 text-left">OS</th>
                <th class="px-6 py-4 text-left">Location</th>
                @if(auth()->user()->isAdmin())
                <th class="px-6 py-4 text-left">Action</th>
                @endif
            </tr>
        </thead>
        <tbody id="deviceBody" class="divide-y divide-gray-300">
            @forelse ($perangkat as $item)
            <tr class="hover:bg-gray-50 transition device-row"
                data-status="{{ $item['status'] }}"
                data-iface="{{ $item['iface_type'] }}"
                data-group="{{ $item['groups'] ?? '' }}"
                data-search="{{ strtolower($item['host'].' '.$item['ip'].' '.$item['serial'].' '.$item['vendor'].' '.$item['model'].' '.$item['groups']) }}">

                <td class="px-6 py-4 text-gray-500">{{ $loop->iteration }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $item['host'] }}</td>
                <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $item['ip'] }}</td>

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

                <td class="px-6 py-4 text-gray-600 text-xs">{{ $item['groups'] ?: '-' }}</td>
                <td class="px-6 py-4 text-gray-700">{{ $item['type'] ?: '-' }}</td>
                <td class="px-6 py-4 text-gray-700 text-xs">{{ trim($item['vendor'].' '.$item['model']) ?: '-' }}</td>
                <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $item['serial'] ?: '-' }}</td>
                <td class="px-6 py-4 text-xs text-gray-700">{{ $item['os'] ?: '-' }}</td>
                <td class="px-6 py-4 text-xs text-gray-700">{{ $item['location'] ?: '-' }}</td>

                @if(auth()->user()->isAdmin())
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="showDetail({{ json_encode($item) }})"
                            class="bg-[#1a1a2e] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#243B7C] transition">
                            Detail
                        </button>
                        <button onclick="confirmDelete('{{ $item['hostid'] }}', '{{ addslashes($item['host']) }}')"
                            class="text-red-600 border border-red-300 text-xs px-3 py-2 rounded-lg hover:bg-red-50 transition">
                            Hapus
                        </button>
                    </div>
                </td>
                @endif

            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->isAdmin() ? 11 : 10 }}" class="px-6 py-12 text-center text-gray-400">
                    No hosts found from Zabbix.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div id="emptyDevice" class="hidden py-12 text-center text-gray-400 text-sm">No device found.</div>
    </div>
    </div>

</div>{{-- end panel-active --}}


<!-- ===============================================================
     PANEL: BROKEN DEVICES
     =============================================================== -->
<div id="panel-broken" class="hidden">

    <!-- Search broken -->
    <div class="flex items-center gap-2 mb-6 flex-wrap">
    <input type="text" id="searchBroken" placeholder="Search broken device..." onkeyup="filterBroken()"
        class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-64
               focus:outline-none focus:ring-2 focus:ring-red-300 bg-white"/>

    <select id="filterBrokenGroup" onchange="filterBroken()"
        class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:ring-2 focus:ring-red-300 cursor-pointer">
        <option value="">All Group</option>
        @foreach($brokenDevices->pluck('groups')->filter()->unique()->sort() as $group)
            <option value="{{ $group }}">{{ $group }}</option>
        @endforeach
    </select>

    <!-- Export broken -->
    <button onclick="exportBroken()"
        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm
               font-semibold rounded-lg hover:bg-green-700 transition whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </button>

    @if(auth()->user()->isAdmin())
    <button onclick="openAddBrokenModal()"
        class="flex items-center gap-2 px-4 py-2 bg-[#243B7C] text-white text-sm
               font-semibold rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add
    </button>
    @endif
</div>

    <!-- TABLE BROKEN -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
    <table class="w-full text-sm border border-gray-200" id="brokenTable">
        <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
            <tr>
                <th class="px-6 py-4 text-left">No.</th>
                <th class="px-6 py-4 text-left">Device Name</th>
                <th class="px-6 py-4 text-left">IP Address</th>
                <th class="px-6 py-4 text-left">Group</th>
                <th class="px-6 py-4 text-left">Alasan Rusak</th>
                <th class="px-6 py-4 text-left">Tanggal Rusak</th>
            </tr>
        </thead>
        <tbody id="brokenBody" class="divide-y divide-gray-200">
            @forelse($brokenDevices as $i => $b)
            <tr class="hover:bg-red-50 transition broken-row"
                data-search="{{ strtolower($b->host_name . ' ' . $b->ip . ' ' . $b->groups . ' ' . $b->reason) }}"
                data-group="{{ $b->groups ?? '' }}">
                <td class="px-6 py-4 text-gray-500">{{ $i + 1 }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $b->host_name }}</td>
                <td class="px-6 py-4 font-mono text-xs text-gray-700">{{ $b->ip ?: '-' }}</td>
                <td class="px-6 py-4 text-xs text-gray-600">{{ $b->groups ?: '-' }}</td>
                <td class="px-6 py-4 text-xs text-gray-700 max-w-xs">{{ $b->reason }}</td>
                <td class="px-6 py-4 text-xs text-gray-700">{{ $b->broken_date->format('d M Y') }}</td>
                @if(auth()->user()->isAdmin())
                <td class="px-6 py-4">
                    <form action="{{ route('broken.destroy', $b->id) }}" method="POST"
                          onsubmit="return confirm('Pulihkan device {{ addslashes($b->host_name) }} dari daftar rusak?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="text-green-600 border border-green-300 text-xs px-3 py-2
                                   rounded-lg hover:bg-green-50 transition whitespace-nowrap">
                            Restore
                        </button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}"
                    class="px-6 py-12 text-center text-gray-400">
                    Tidak ada device yang tercatat rusak.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div id="emptyBroken" class="hidden py-12 text-center text-gray-400 text-sm">No broken device found.</div>
    </div>
    </div>

</div>{{-- end panel-broken --}}


<!-- ===================================================================
     MODAL TANDAI RUSAK  (admin only)
     =================================================================== -->
@if(auth()->user()->isAdmin())
<div id="brokenModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-[#243B7C]">Tandai Device Rusak</h3>
                <p id="brokenModalSubtitle" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button onclick="closeBrokenModal()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="flex justify-center mb-5">
            <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <form action="{{ route('broken.store') }}" method="POST">
            @csrf
            <input type="hidden" name="hostid"    id="broken_hostid">
            <input type="hidden" name="host_name" id="broken_host_name">
            <input type="hidden" name="ip"        id="broken_ip">
            <input type="hidden" name="groups"    id="broken_groups">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Rusak <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" rows="3" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none"
                    placeholder="e.g. PSU terbakar, tidak dapat diperbaiki..."></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Rusak <span class="text-red-500">*</span>
                </label>
                <input type="date" name="broken_date" required
                    value="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeBrokenModal()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold
                           hover:bg-gray-300 text-sm">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-orange-500 text-white font-semibold
                           hover:bg-orange-600 text-sm transition">
                    Tandai Rusak
                </button>
            </div>
        </form>
    </div>
</div>

<div id="addBrokenModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-[#243B7C]">Tambah Device Rusak</h3>
                <p class="text-xs text-gray-400 mt-0.5">Daftarkan perangkat rusak secara manual</p>
            </div>
            <button onclick="closeAddBrokenModal()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="flex justify-center mb-5">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <form action="{{ route('broken.store') }}" method="POST">
            @csrf
            <input type="hidden" name="hostid" value="">

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Device Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="host_name" required
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300"
                    placeholder="e.g. ROUTER-A">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                <input type="text" name="ip"
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300"
                    placeholder="e.g. 192.168.1.1">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                <input type="text" name="groups"
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300"
                    placeholder="e.g. MIKROTIK">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Rusak <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" rows="2" required
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none"
                    placeholder="e.g. PSU terbakar, tidak dapat diperbaiki..."></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Rusak <span class="text-red-500">*</span>
                </label>
                <input type="date" name="broken_date" required
                    value="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddBrokenModal()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold
                           hover:bg-gray-300 text-sm">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-orange-500 text-white font-semibold
                           hover:bg-orange-600 text-sm transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif


@if(auth()->user()->isAdmin())

<!-- ===================================================================
     MODAL DETAIL
     =================================================================== -->
<div id="detailModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-8 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-5">
            <h3 id="modalTitle" class="text-xl font-bold text-[#243B7C]"></h3>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="divide-y divide-gray-100 text-sm" id="modalContent"></div>

        <div class="flex items-center justify-between mt-6">
            <button onclick="closeDetail()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Close
            </button>
            <button id="btnEditZabbix" onclick="openEditZabbix()"
                class="flex items-center gap-2 px-5 py-2 rounded-lg bg-[#243B7C] text-white
                       font-semibold hover:bg-blue-800 text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-[#243B7C]">Edit Host Zabbix</h3>
                <p id="editZabbixSubtitle" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button onclick="closeEditZabbix()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div id="editZabbixLoading" class="py-10 text-center text-gray-400 text-sm">
            <svg class="animate-spin w-6 h-6 mx-auto mb-2 text-[#243B7C]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Mengambil data dari Zabbix...
        </div>

        <form id="editZabbixForm" action="" method="POST" class="hidden">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Host Name <span class="text-red-500">*</span></label>
                    <input type="text" name="host_name" id="edit_host_name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Address <span class="text-red-500">*</span></label>
                    <input type="text" name="ip_address" id="edit_ip" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interface Type</label>
                        <select name="iface_type" id="edit_iface_type" onchange="updateEditPort(this.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <option value="ZBX">ZBX (Agent)</option>
                            <option value="SNMP">SNMP</option>
                            <option value="IPMI">IPMI</option>
                            <option value="JMX">JMX</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                        <input type="text" name="port" id="edit_port"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Host Group <span class="text-red-500">*</span></label>
                    <select name="group_id" id="edit_group" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">-- Loading... --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Templates <span class="text-gray-400 font-normal">(select one or more)</span>
                    </label>
                    <select name="template_ids[]" id="edit_templates" multiple
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 min-h-[90px]">
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hold Ctrl / Cmd to select more than one.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeEditZabbix()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-[#243B7C] text-white font-semibold hover:bg-blue-800 text-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
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
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7
                             m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 01-1-1V5a1 1 0 011-1h6a1 1 0 011 1v1a1 1 0 01-1 1H9z"/>
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
                class="flex-1 px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Batal
            </button>
            <button type="button" onclick="submitDelete()"
                class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Host Name <span class="text-red-500">*</span></label>
                    <input type="text" name="host_name" id="zbx_host_name" required placeholder="e.g. server-01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Address <span class="text-red-500">*</span></label>
                    <input type="text" name="ip_address" id="zbx_ip" required placeholder="e.g. 192.168.1.10"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interface Type</label>
                        <select name="iface_type" id="zbx_iface_type" onchange="updateDefaultPort(this.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <option value="ZBX">ZBX (Agent)</option>
                            <option value="SNMP">SNMP</option>
                            <option value="IPMI">IPMI</option>
                            <option value="JMX">JMX</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                        <input type="text" name="port" id="zbx_port" value="10050"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Host Group <span class="text-red-500">*</span></label>
                    <select name="group_id" id="zbx_group" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">-- Loading groups... --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Templates <span class="text-gray-400 font-normal">(optional, select one or more)</span>
                    </label>
                    <select name="template_ids[]" id="zbx_templates" multiple
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 min-h-[80px]">
                        <option value="" disabled>-- Loading templates... --</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hold Ctrl / Cmd to select more than one.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeAddZabbix()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-[#243B7C] text-white font-semibold hover:bg-blue-800 text-sm flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Host
                </button>
            </div>
        </form>
    </div>
</div>

@endif {{-- end isAdmin --}}


<!-- ===================================================================
     MODAL EXPORT EXCEL  (active devices)
     =================================================================== -->
<div id="exportModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-7">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-[#243B7C]">Export Excel</h3>
                <p class="text-xs text-gray-400 mt-0.5">Select group to export</p>
            </div>
            <button onclick="closeExportModal()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- All Groups -->
        <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-50 border border-gray-200
                       cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition mb-3">
            <input type="checkbox" id="exportCheckAll" onchange="toggleExportAll(this)"
                class="w-4 h-4 accent-[#243B7C] cursor-pointer">
            <span class="text-sm font-semibold text-gray-700">All Groups</span>
            <span id="exportCountAll" class="ml-auto text-xs text-gray-400"></span>
        </label>

        <div class="h-px bg-gray-200 mb-3"></div>

        <!-- Group list -->
        <div id="exportGroupList" class="space-y-1.5 max-h-48 overflow-y-auto pr-1 mb-3"></div>

        <!-- Separator + Broken option -->
        <div class="h-px bg-gray-200 mb-3"></div>
        <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-orange-200
                       bg-orange-50 cursor-pointer hover:bg-orange-100 transition mb-5">
            <input type="checkbox" id="exportBroken"
                class="w-4 h-4 accent-orange-500 cursor-pointer">
            <span class="text-sm font-semibold text-orange-700">⚠ Broken Devices</span>
            <span class="ml-auto text-xs text-orange-400">{{ $brokenDevices->count() }} devices</span>
        </label>

        <div class="flex justify-end gap-3 mt-2">
            <button onclick="closeExportModal()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Batal
            </button>
            <button onclick="doExport()"
                class="flex items-center gap-2 px-5 py-2 rounded-lg bg-green-600 text-white
                       font-semibold hover:bg-green-700 text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </button>
        </div>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let currentDevice  = null;
let zabbixOptions  = null;

// ── Tab switching ──────────────────────────────────────────
function switchTab(tab) {
    const isActive = tab === 'active';
    document.getElementById('panel-active').classList.toggle('hidden', !isActive);
    document.getElementById('panel-broken').classList.toggle('hidden',  isActive);

    const baseActive = 'px-5 py-2 rounded-lg text-sm font-semibold transition ';
    document.getElementById('tab-active').className = baseActive +
        (isActive ? 'bg-white text-[#243B7C] shadow-sm' : 'text-gray-500 hover:text-gray-700');
    document.getElementById('tab-broken').className = baseActive +
        (!isActive ? 'bg-white text-[#243B7C] shadow-sm' : 'text-gray-500 hover:text-gray-700');
}

// ── Filter active ──────────────────────────────────────────
function filterDevice() {
    const search = document.getElementById('searchDevice').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const iface  = document.getElementById('filterIface').value;
    const group  = document.getElementById('filterGroup').value;
    let visible  = 0;

    document.querySelectorAll('.device-row').forEach(row => {
        const show = row.dataset.search.includes(search)
            && (status === '' || row.dataset.status === status)
            && (iface  === '' || row.dataset.iface  === iface)
            && (group  === '' || row.dataset.group  === group);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    document.getElementById('emptyDevice').classList.toggle('hidden', visible > 0);
}

// ── Filter broken ──────────────────────────────────────────
function filterBroken() {
    const search = document.getElementById('searchBroken').value.toLowerCase();
    const group  = document.getElementById('filterBrokenGroup').value;
    let visible  = 0;

    document.querySelectorAll('.broken-row').forEach(row => {
        const show = row.dataset.search.includes(search)
            && (group === '' || row.dataset.group === group);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    document.getElementById('emptyBroken').classList.toggle('hidden', visible > 0);
}

// ── Export Active (modal) ──────────────────────────────────
function openExportModal() {
    const groupCount = {};
    document.querySelectorAll('.device-row').forEach(row => {
        const g = row.dataset.group;
        if (g && g.trim() !== '') groupCount[g] = (groupCount[g] || 0) + 1;
    });

    const groups   = Object.keys(groupCount).sort();
    const total    = Object.values(groupCount).reduce((a, b) => a + b, 0);
    const list     = document.getElementById('exportGroupList');
    const checkAll = document.getElementById('exportCheckAll');

    checkAll.checked       = false;
    checkAll.indeterminate = false;
    document.getElementById('exportCountAll').textContent = `${total} devices`;
    document.getElementById('exportBroken').checked = false;

    list.innerHTML = groups.map(g => `
        <label class="flex items-center gap-3 px-3 py-2 rounded-lg border border-transparent
                       cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition">
            <input type="checkbox" value="${g}" onchange="syncExportAll()"
                class="export-group-chk w-4 h-4 accent-[#243B7C] cursor-pointer">
            <span class="text-sm text-gray-700 flex-1">${g}</span>
            <span class="text-xs text-gray-400">${groupCount[g]} devices</span>
        </label>
    `).join('');

    document.getElementById('exportModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

document.getElementById('exportModal').addEventListener('click', e => {
    if (e.target === document.getElementById('exportModal')) closeExportModal();
});

function toggleExportAll(cb) {
    document.querySelectorAll('.export-group-chk').forEach(chk => { chk.checked = cb.checked; });
}

function syncExportAll() {
    const all     = document.querySelectorAll('.export-group-chk');
    const checked = document.querySelectorAll('.export-group-chk:checked');
    const cb      = document.getElementById('exportCheckAll');
    if (checked.length === 0)        { cb.checked = false; cb.indeterminate = false; }
    else if (checked.length === all.length) { cb.checked = true;  cb.indeterminate = false; }
    else                             { cb.checked = false; cb.indeterminate = true; }
}

function doExport() {
    const selected  = [...document.querySelectorAll('.export-group-chk:checked')].map(c => c.value);
    const wantBroken = document.getElementById('exportBroken')?.checked;

    if (selected.length === 0 && !wantBroken) {
        alert('Pilih minimal satu group atau centang Broken Devices untuk diekspor.');
        return;
    }

    const colWidths = [
        { wch: 5 }, { wch: 28 }, { wch: 18 }, { wch: 14 }, { wch: 10 },
        { wch: 20 }, { wch: 14 }, { wch: 22 }, { wch: 18 }, { wch: 16 }, { wch: 20 }
    ];

    function buildActiveRows(groupName) {
        const headers = ['No.', 'Host', 'IP Address', 'Interface Type', 'Status',
                         'Group', 'Type', 'Vendor & Model', 'Serial', 'OS', 'Location'];
        const data = [headers];
        let no = 1;
        document.querySelectorAll('.device-row').forEach(row => {
            if (row.dataset.group !== groupName) return;
            const cells = row.querySelectorAll('td');
            data.push([
                no++,
                cells[1]?.innerText?.trim() ?? '',
                cells[2]?.innerText?.trim() ?? '',
                row.dataset.iface  ?? '',
                row.dataset.status ?? '',
                cells[4]?.innerText?.trim() ?? '',
                cells[5]?.innerText?.trim() ?? '',
                cells[6]?.innerText?.trim() ?? '',
                cells[7]?.innerText?.trim() ?? '',
                cells[8]?.innerText?.trim() ?? '',
                cells[9]?.innerText?.trim() ?? '',
            ]);
        });
        return data;
    }

    const wb   = XLSX.utils.book_new();
    const date = new Date().toISOString().slice(0, 10);

    // Sheet per group aktif
    selected.forEach(group => {
        const rows = buildActiveRows(group);
        if (rows.length <= 1) return;
        const ws        = XLSX.utils.aoa_to_sheet(rows);
        ws['!cols']     = colWidths;
        const sheetName = group.replace(/[\\\/\?\*\[\]:]/g, '').substring(0, 31);
        XLSX.utils.book_append_sheet(wb, ws, sheetName);
    });

    // Sheet broken devices
    if (wantBroken) {
        const brokenHeaders = ['No.', 'Device Name', 'IP Address', 'Group',
                               'Alasan Rusak', 'Tanggal Rusak', 'Dilaporkan Oleh'];
        const brokenData = [brokenHeaders];
        let no = 1;
        document.querySelectorAll('.broken-row').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 6) return;
            brokenData.push([
                no++,
                cells[1]?.innerText?.trim() ?? '',
                cells[2]?.innerText?.trim() ?? '',
                cells[3]?.innerText?.trim() ?? '',
                cells[4]?.innerText?.trim() ?? '',
                cells[5]?.innerText?.trim() ?? '',
                cells[6]?.innerText?.trim() ?? '',
            ]);
        });
        if (brokenData.length > 1) {
            const ws    = XLSX.utils.aoa_to_sheet(brokenData);
            ws['!cols'] = [{wch:5},{wch:28},{wch:18},{wch:20},{wch:40},{wch:15},{wch:20}];
            XLSX.utils.book_append_sheet(wb, ws, 'Broken Devices');
        }
    }

    if (!wb.SheetNames.length) {
        alert('Tidak ada data pada pilihan yang dipilih.');
        return;
    }

    const allGroups = [...document.querySelectorAll('.export-group-chk')].map(c => c.value);
    const isAll     = selected.length === allGroups.length;
    let fileName;
    if (wantBroken && selected.length === 0)     fileName = `device-broken-${date}.xlsx`;
    else if (isAll && wantBroken)                fileName = `device-all-with-broken-${date}.xlsx`;
    else if (isAll)                              fileName = `device-all-groups-${date}.xlsx`;
    else if (selected.length === 1 && !wantBroken) {
        const safe = selected[0].replace(/[^a-zA-Z0-9_\-]/g, '_');
        fileName   = `device-${safe}-${date}.xlsx`;
    } else                                       fileName = `device-export-${date}.xlsx`;

    XLSX.writeFile(wb, fileName);
    closeExportModal();
}

// ── Export Broken (langsung download, tanpa modal) ─────────
function exportBroken() {
    const headers  = ['No.', 'Device Name', 'IP Address', 'Group',
                      'Alasan Rusak', 'Tanggal Rusak', 'Dilaporkan Oleh'];
    const data     = [headers];
    let no         = 1;

    document.querySelectorAll('.broken-row').forEach(row => {
        if (row.classList.contains('hidden')) return;
        const cells = row.querySelectorAll('td');
        if (cells.length < 6) return;
        data.push([
            no++,
            cells[1]?.innerText?.trim() ?? '',
            cells[2]?.innerText?.trim() ?? '',
            cells[3]?.innerText?.trim() ?? '',
            cells[4]?.innerText?.trim() ?? '',
            cells[5]?.innerText?.trim() ?? '',
            cells[6]?.innerText?.trim() ?? '',
        ]);
    });

    if (data.length <= 1) {
        alert('Tidak ada data broken device untuk diekspor.');
        return;
    }

    const ws    = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{wch:5},{wch:28},{wch:18},{wch:20},{wch:40},{wch:15},{wch:20}];
    const wb    = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Broken Devices');
    const date  = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, `broken-devices-${date}.xlsx`);
}

// ── Broken device modal ────────────────────────────────────
function openBrokenModal(hostid, hostName, ip, groups) {
    document.getElementById('broken_hostid').value    = hostid;
    document.getElementById('broken_host_name').value = hostName;
    document.getElementById('broken_ip').value        = ip;
    document.getElementById('broken_groups').value    = groups;
    document.getElementById('brokenModalSubtitle').textContent = hostName;
    document.getElementById('brokenModal').classList.remove('hidden');
}

function closeBrokenModal() {
    document.getElementById('brokenModal').classList.add('hidden');
}

document.getElementById('brokenModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeBrokenModal();
});

@if(auth()->user()->isAdmin())
// ── Detail Modal ───────────────────────────────────────────
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

function closeDetail() { document.getElementById('detailModal').classList.add('hidden'); }
document.getElementById('detailModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeDetail();
});

// ── Edit Zabbix Modal ──────────────────────────────────────
async function openEditZabbix() {
    if (!currentDevice) return;
    const hostid = currentDevice.hostid;
    document.getElementById('editZabbixSubtitle').textContent = 'Mengedit: ' + currentDevice.host;
    document.getElementById('editZabbixForm').action = '{{ url("/zabbix/host") }}/' + hostid;
    document.getElementById('editZabbixModal').classList.remove('hidden');
    document.getElementById('editZabbixLoading').classList.remove('hidden');
    document.getElementById('editZabbixForm').classList.add('hidden');

    try {
        const [hostRes, optRes] = await Promise.all([
            fetch('{{ url("/zabbix/host") }}/' + hostid),
            zabbixOptions ? Promise.resolve({ ok: true, _cached: true }) : fetch('{{ route("zabbix.options") }}'),
        ]);
        const hostData = await hostRes.json();
        if (!zabbixOptions) zabbixOptions = await optRes.json();
        populateEditDropdowns(zabbixOptions, hostData);
        document.getElementById('edit_host_name').value = hostData.host ?? '';
        const mainIface = (hostData.interfaces ?? []).find(i => i.main === '1') ?? hostData.interfaces?.[0] ?? {};
        document.getElementById('edit_ip').value   = mainIface.ip   ?? '';
        document.getElementById('edit_port').value = mainIface.port ?? '10050';
        const ifaceMap = { '1': 'ZBX', '2': 'SNMP', '3': 'IPMI', '4': 'JMX' };
        document.getElementById('edit_iface_type').value = ifaceMap[mainIface.type] ?? 'ZBX';
        document.getElementById('editZabbixLoading').classList.add('hidden');
        document.getElementById('editZabbixForm').classList.remove('hidden');
    } catch (err) {
        document.getElementById('editZabbixLoading').innerHTML =
            '<p class="text-red-500">Gagal memuat data: ' + err.message + '</p>';
    }
}

function populateEditDropdowns(options, hostData) {
    const groupSel = document.getElementById('edit_group');
    const activeGroupIds = (hostData.groups ?? []).map(g => g.groupid);
    groupSel.innerHTML = '<option value="">-- Pilih group --</option>';
    (options.groups ?? []).forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.groupid; opt.textContent = g.name;
        if (activeGroupIds.includes(g.groupid)) opt.selected = true;
        groupSel.appendChild(opt);
    });
    const tplSel = document.getElementById('edit_templates');
    const activeTemplateIds = (hostData.parentTemplates ?? []).map(t => t.templateid);
    tplSel.innerHTML = '';
    (options.templates ?? []).forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.templateid; opt.textContent = t.name;
        if (activeTemplateIds.includes(t.templateid)) opt.selected = true;
        tplSel.appendChild(opt);
    });
}

function closeEditZabbix() { document.getElementById('editZabbixModal').classList.add('hidden'); }
document.getElementById('editZabbixModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeEditZabbix();
});
function updateEditPort(ifaceType) {
    const ports = { ZBX: '10050', SNMP: '161', IPMI: '623', JMX: '12345' };
    document.getElementById('edit_port').value = ports[ifaceType] ?? '10050';
}

// ── Delete Modal ───────────────────────────────────────────
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

// ── Add Zabbix Modal ───────────────────────────────────────
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
        const res = await fetch('{{ route("zabbix.options") }}');
        zabbixOptions = await res.json();
        repopulateAddDropdowns();
    } catch (err) {
        document.getElementById('zbx_group').innerHTML = '<option value="">Gagal memuat groups</option>';
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

// ── Add Broken Modal ───────────────────────────────────────
function openAddBrokenModal() {
    document.getElementById('addBrokenModal').classList.remove('hidden');
}
function closeAddBrokenModal() {
    document.getElementById('addBrokenModal').classList.add('hidden');
}
document.getElementById('addBrokenModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAddBrokenModal();
});
@endif

// ── Auto-dismiss flash ─────────────────────────────────────
['flashSuccess', 'flashError'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => el.style.display = 'none', 5000);
});
</script>

@endsection