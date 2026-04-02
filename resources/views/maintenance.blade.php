@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-6">
    Maintenance Schedule
</h2>

<!-- ================= SUCCESS MESSAGE ================= -->
@if (session('success'))
<div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<!-- ================= STATS ================= -->
<div class="grid grid-cols-3 gap-6 mb-8">

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Devices</p>
            <p class="text-2xl font-bold text-[#243B7C]">{{ count($zbxDevices) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Done Today</p>
            <p class="text-2xl font-bold text-green-600">{{ $doneToday }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Belum Maintenance</p>
            <p class="text-2xl font-bold text-yellow-600">{{ count($zbxDevices) - $doneToday }}</p>
        </div>
    </div>

</div>

<!-- ================= DEVICE LIST ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">

    {{-- Form hanya aktif untuk admin; user hanya melihat struktur yang sama tapi tanpa interaksi --}}
    <form id="maintenanceForm" action="{{ route('maintenance.store') }}" method="POST">
        @csrf

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-bold text-[#243B7C]">Device List</h3>
                @if(auth()->user()->isAdmin())
                    <p class="text-xs text-gray-400 mt-0.5">Centang device yang sudah selesai dimaintenance hari ini</p>
                @else
                    <p class="text-xs text-gray-400 mt-0.5">Daftar perangkat dan status maintenance hari ini</p>
                @endif
            </div>

            {{-- Search --}}
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Cari device..."
                        class="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 w-52">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto overflow-y-auto max-h-[55vh]">
            <table class="w-full text-sm">
                <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-200 sticky top-0 z-10 bg-white">
                    <tr>
                        {{-- Kolom checkbox: admin menampilkan checkbox interaktif, user hanya melihat ikon status --}}
                        <th class="px-6 py-3 text-left w-10">
                            @if(auth()->user()->isAdmin())
                                <input type="checkbox" id="checkAll"
                                    class="w-4 h-4 cursor-pointer accent-blue-700"
                                    title="Pilih semua">
                            @else
                                <span class="text-xs text-gray-400">✓</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Device Name</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">IP Address</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Status</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Last Maintenance</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Done By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="deviceTableBody">
                    @forelse ($zbxDevices as $device)
                    @php
                        $lastMaint = $lastMaintenanceMap[$device['hostid']] ?? null;
                        $isDoneToday = $doneTodayMap[$device['hostid']] ?? false;
                    @endphp
                    <tr class="device-row hover:bg-gray-50 transition {{ $isDoneToday ? 'bg-green-50' : '' }}"
                        data-name="{{ strtolower($device['host']) }}">

                        {{-- Checkbox: interaktif untuk admin, read-only untuk user --}}
                        <td class="px-6 py-3">
                            @if(auth()->user()->isAdmin())
                                <input type="checkbox"
                                    name="devices[]"
                                    value="{{ $device['hostid'] }}"
                                    data-name="{{ $device['host'] }}"
                                    class="device-cb w-4 h-4 cursor-pointer accent-blue-700
                                           {{ $isDoneToday ? 'opacity-40 cursor-not-allowed' : '' }}"
                                    {{ $isDoneToday ? 'disabled' : '' }}>
                            @else
                                {{-- User hanya melihat ikon status, tidak bisa mencentang --}}
                                @if($isDoneToday)
                                    <span class="text-green-500 font-bold text-base">✓</span>
                                @else
                                    <span class="inline-block w-4 h-4 rounded border border-gray-300 bg-gray-50"></span>
                                @endif
                            @endif
                        </td>

                        {{-- Device Name --}}
                        <td class="px-6 py-3 font-medium text-gray-800 whitespace-nowrap">
                            {{ $device['host'] }}
                            @if($isDoneToday)
                                <span class="ml-2 px-1.5 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">
                                    ✓ Done
                                </span>
                            @endif
                        </td>

                        {{-- IP --}}
                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap font-mono text-xs">
                            {{ $device['ip'] }}
                        </td>

                        {{-- Status Zabbix --}}
                        <td class="px-6 py-3 whitespace-nowrap">
                            @if($device['status'] === 'Online')
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                    Online
                                </span>
                            @elseif($device['status'] === 'Offline')
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                    Offline
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
                                    Unknown
                                </span>
                            @endif
                        </td>

                        {{-- Last Maintenance --}}
                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap text-xs">
                            @if($lastMaint)
                                <span>{{ $lastMaint->done_at ? $lastMaint->done_at->format('d M Y, H:i') : $lastMaint->scheduled_date->format('d M Y') }}</span>
                                <span class="block text-gray-400 mt-0.5">
                                    {{ $lastMaint->done_at ? $lastMaint->done_at->diffForHumans() : '-' }}
                                </span>
                            @else
                                <span class="text-gray-400">Belum pernah</span>
                            @endif
                        </td>

                        {{-- Done By --}}
                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap text-xs">
                            @if($isDoneToday && $lastMaint)
                                {{ $lastMaint->doneBy->name ?? 'System' }}
                                <span class="block text-gray-400">{{ $lastMaint->done_at?->format('H:i') }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Tidak ada device. Pastikan Zabbix terhubung.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Action Bar: hanya tampil untuk admin --}}
        @if(auth()->user()->isAdmin())
        <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                Dipilih: <span id="selCount" class="font-semibold text-gray-800">0</span> device
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="clearSelection()"
                    class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                    Clear
                </button>
                <button type="button" id="submitBtn" disabled onclick="openConfirmModal()"
                    class="flex items-center gap-1.5 text-xs px-5 py-2 bg-blue-700 text-white rounded-lg
                           hover:bg-blue-800 transition disabled:opacity-40 disabled:cursor-not-allowed font-semibold">
                    ✓ Catat Maintenance (<span id="markCount">0</span>)
                </button>
            </div>
        </div>
        @endif

    </form>
</div>


<!-- ================= MODAL KONFIRMASI (admin only) ================= -->
@if(auth()->user()->isAdmin())
<div id="confirmModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-[#243B7C]">Konfirmasi Maintenance</h3>
            <button onclick="closeConfirmModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <p class="text-gray-500 text-sm mb-3">Device yang sudah selesai dimaintenance:</p>

        <div id="selectedDevicesList"
             class="bg-gray-50 rounded-lg px-4 py-3 mb-4 text-sm text-gray-700 max-h-40 overflow-y-auto space-y-1.5">
        </div>

        <div class="mb-5">
            <label class="block text-sm text-gray-700 mb-1">Notes (optional)</label>
            <textarea name="notes"
                id="notesInput"
                rows="3"
                form="maintenanceForm"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 resize-none text-sm"
                placeholder="e.g. Cleaned fan, updated firmware..."></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeConfirmModal()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Batal
            </button>
            <button type="submit" form="maintenanceForm"
                class="px-5 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 text-sm">
                ✓ Simpan
            </button>
        </div>
    </div>
</div>
@endif


<!-- ================= SCRIPT ================= -->
<script>
// ─── Search ───────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.device-row').forEach(row => {
        const name = row.dataset.name || '';
        row.style.display = name.includes(q) ? '' : 'none';
    });
});

@if(auth()->user()->isAdmin())
// ─── Checkbox logic (admin only) ──────────────────────────────
function getCheckboxes() {
    return [...document.querySelectorAll('.device-cb:not([disabled])')];
}

function updateUI() {
    const checked = getCheckboxes().filter(c => c.checked);
    const count   = checked.length;

    document.getElementById('selCount').textContent  = count;
    document.getElementById('markCount').textContent = count;
    document.getElementById('submitBtn').disabled    = count === 0;

    // Master checkbox state
    const all      = getCheckboxes();
    const checkAll = document.getElementById('checkAll');
    checkAll.indeterminate = count > 0 && count < all.length;
    checkAll.checked       = all.length > 0 && count === all.length;

    // Highlight rows
    getCheckboxes().forEach(cb => {
        const row = cb.closest('tr');
        if (cb.checked) row.classList.add('bg-blue-50');
        else            row.classList.remove('bg-blue-50');
    });
}

function clearSelection() {
    getCheckboxes().forEach(cb => { cb.checked = false; });
    updateUI();
}

// Master checkbox
document.getElementById('checkAll').addEventListener('change', function () {
    getCheckboxes().forEach(cb => { cb.checked = this.checked; });
    updateUI();
});

// Per-row checkbox
document.querySelectorAll('.device-cb').forEach(cb => {
    cb.addEventListener('change', updateUI);
});

// ─── Confirm modal ────────────────────────────────────────────
function openConfirmModal() {
    const checked = getCheckboxes().filter(c => c.checked);
    if (checked.length === 0) return;

    const list = document.getElementById('selectedDevicesList');
    list.innerHTML = '';
    checked.forEach(cb => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `<span class="text-blue-500 font-bold">✓</span>
                         <span>${cb.dataset.name}</span>`;
        list.appendChild(div);
    });

    document.getElementById('notesInput').value = '';
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('confirmModal').addEventListener('click', function (e) {
    if (e.target === this) closeConfirmModal();
});
@endif
</script>

@endsection