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

<!-- ================= TIME RANGE PICKER BAR ================= -->
<div class="bg-white rounded-xl shadow-sm px-5 py-3 mb-5 flex items-center gap-4 flex-wrap">

    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
         stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>

    <span class="text-sm font-semibold text-[#243B7C]">{{ $range['label'] }}</span>

    <button type="button" id="btnOpenRangePicker"
        class="flex items-center gap-1 px-3 py-1.5 text-xs border border-gray-300 rounded-lg
               text-gray-600 hover:bg-gray-50 transition font-medium">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
        Change Period
    </button>

    @if($range['preset'] !== 'all')
    <span class="flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700
                 text-xs font-semibold rounded-full">
        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
        {{ $range['label'] }}
        <a href="{{ route('log.index') }}"
           class="ml-1 text-blue-400 hover:text-blue-600 leading-none" title="Reset">✕</a>
    </span>
    @endif

    <!-- Total badge -->
    <span class="ml-auto text-xs text-gray-400">
        <span class="font-semibold text-gray-700">{{ $merged->count() }}</span> log ditemukan
    </span>
</div>

<!-- ================= RANGE PICKER PANEL ================= -->
<div id="rangePickerPanel"
     class="hidden fixed inset-0 z-40 flex items-start justify-center pt-28"
     onclick="if(event.target===this) closeRangePicker()">

    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-2xl mx-4 overflow-hidden">
        <div class="flex">

            <!-- Custom date inputs -->
            <div class="w-64 border-r border-gray-100 p-5 flex-shrink-0">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Custom Range</p>

                <form method="GET" action="{{ route('log.index') }}" id="rangeForm">
                    <input type="hidden" name="range_preset" value="custom">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="mb-3">
                        <label class="block text-xs text-gray-500 mb-1">From</label>
                        <input type="date" name="range_from"
                            value="{{ $range['preset'] === 'custom' && $range['from'] ? $range['from']->format('Y-m-d') : '' }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-1">To</label>
                        <input type="date" name="range_to"
                            value="{{ $range['preset'] === 'custom' && $range['to'] ? $range['to']->format('Y-m-d') : '' }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>

                    <button type="submit"
                        class="w-full py-2 bg-[#243B7C] text-white text-sm font-semibold
                               rounded-lg hover:bg-blue-800 transition">
                        Apply
                    </button>
                </form>
            </div>

            <!-- Preset buttons -->
            <div class="flex-1 p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Quick Select</p>

                @php
                $presetCols = [
                    [
                        ['all',          'All Time'],
                        ['last_7',       'Last 7 days'],
                        ['last_30',      'Last 30 days'],
                        ['last_3months', 'Last 3 months'],
                        ['last_6months', 'Last 6 months'],
                        ['last_year',    'Last 1 year'],
                    ],
                    [
                        ['today',       'Today'],
                        ['yesterday',   'Yesterday'],
                        ['this_week',   'This week'],
                        ['prev_week',   'Previous week'],
                        ['this_month',  'This month'],
                        ['prev_month',  'Previous month'],
                    ],
                ];
                @endphp

                <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                    @foreach($presetCols as $col)
                    <div class="space-y-1">
                        @foreach($col as [$p, $lbl])
                        <a href="{{ route('log.index', array_merge(request()->only(['type','search']), ['range_preset' => $p])) }}"
                           class="block px-3 py-2 rounded-lg text-sm transition
                                  {{ $range['preset'] === $p
                                        ? 'bg-[#243B7C] text-white font-semibold'
                                        : 'text-gray-700 hover:bg-gray-100' }}">
                            {{ $lbl }}
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <input
        type="text"
        id="searchInput"
        placeholder="Search"
        onkeyup="filterLog()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
        value="{{ request('search') }}"
    />

    <select
        id="filterType"
        onchange="filterLog()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">All Types</option>
        <option value="Perubahan Data"     {{ request('type') === 'Perubahan Data'     ? 'selected' : '' }}>Perubahan Data</option>
        <option value="Maintenance"        {{ request('type') === 'Maintenance'        ? 'selected' : '' }}>Maintenance</option>
        <option value="Kerusakan"          {{ request('type') === 'Kerusakan'          ? 'selected' : '' }}>Kerusakan</option>
        <option value="Perpindahan Lokasi" {{ request('type') === 'Perpindahan Lokasi' ? 'selected' : '' }}>Perpindahan Lokasi</option>
    </select>

    <!-- ===== EXPORT BUTTON ===== -->
    <button onclick="exportToExcel()"
        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm
               font-semibold rounded-lg hover:bg-green-700 transition whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </button>
    
    @if(auth()->user()->isAdmin())
    <button onclick="openModal()"
        class="flex items-center gap-2 px-4 py-2 bg-[#243B7C] text-white text-sm
               font-semibold rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
        + Add Log
    </button>
    @endif

</div>

<!-- ================= LOG TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
<div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
<table class="w-full text-sm border border-gray-200" id="logTable">

    <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
        <tr>
            <th class="px-6 py-4 text-left">Time</th>
            <th class="px-6 py-4 text-left">Device Name</th>
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

        @forelse ($merged as $log)
        <tr class="hover:bg-gray-50 transition log-row"
            data-type="{{ $log['type'] }}"
            data-time="{{ \Carbon\Carbon::parse($log['time'])->format('d-m-Y H:i') }}"
            data-device="{{ $log['device_name'] }}"
            data-activity="{{ $log['type'] }}"
            data-detail="{{ $log['detail'] }}"
            data-user="{{ $log['user'] }}"
            data-search="{{ strtolower($log['device_name'] . ' ' . $log['type'] . ' ' . $log['detail']) }}">

            <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                {{ \Carbon\Carbon::parse($log['time'])->format('d-m-Y H:i') }}
            </td>

            <td class="px-6 py-4 text-gray-800 font-medium">
                {{ $log['device_name'] }}
            </td>

            <td class="px-6 py-4">
                @php $cls = $badgeClass[$log['type']] ?? 'bg-gray-100 text-gray-600'; @endphp
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cls }}">
                    {{ $log['type'] }}
                </span>
            </td>

            <td class="px-6 py-4 text-gray-600">
                {{ $log['detail'] }}
            </td>

            <td class="px-6 py-4 text-gray-700">
                {{ $log['user'] }}
            </td>

        </tr>
        @empty
        <tr>
            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                No activity logs found
                @if($range['preset'] !== 'all')
                    for <strong>{{ $range['label'] }}</strong>
                @endif
                .
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

                <div>
                    <label class="block text-sm text-gray-700 mb-1">Device <span class="text-red-500">*</span></label>
                    <select name="device_id"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                        <option value="">Select device</option>
                        @foreach ($devices as $device)
                        <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                            {{ $device->device_id }} — {{ $device->brand_model ?? $device->type }}
                        </option>
                        @endforeach
                    </select>
                </div>

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

                <div>
                    <label class="block text-sm text-gray-700 mb-1">Detail <span class="text-red-500">*</span></label>
                    <textarea name="detail" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 resize-none"
                        placeholder="Describe the activity..."
                        required>{{ old('detail') }}</textarea>
                </div>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// ── Range Picker ──────────────────────────────────────────────
document.getElementById('btnOpenRangePicker').addEventListener('click', function () {
    document.getElementById('rangePickerPanel').classList.toggle('hidden');
});

function closeRangePicker() {
    document.getElementById('rangePickerPanel').classList.add('hidden');
}

// ── Filter (client-side: search + type) ──────────────────────
function filterLog() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const type   = document.getElementById('filterType').value;
    const rows   = document.querySelectorAll('.log-row');
    let visible  = 0;

    rows.forEach(row => {
        const matchSearch = row.dataset.search.includes(search);
        const matchType   = type === '' || row.dataset.type === type;

        const show = matchSearch && matchType;
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    document.getElementById('emptyState').classList.toggle('hidden', visible > 0);
}

// ── Export Excel ──────────────────────────────────────────────
function exportToExcel() {
    const headers = ['Time', 'Device Name', 'Type of Activity', 'Detail', 'User'];
    const rows = [headers];

    document.querySelectorAll('.log-row:not(.hidden)').forEach(row => {
        rows.push([
            row.dataset.time     ?? '',
            row.dataset.device   ?? '',
            row.dataset.activity ?? '',
            row.dataset.detail   ?? '',
            row.dataset.user     ?? '',
        ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(rows);
    ws['!cols'] = [
        { wch: 18 }, { wch: 28 }, { wch: 20 }, { wch: 45 }, { wch: 18 }
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Activity Log');

    const date = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, `activity-log-${date}.xlsx`);
}

// ── Modal ─────────────────────────────────────────────────────
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
</script>

@endsection