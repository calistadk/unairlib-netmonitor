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
@if (session('error'))
<div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
    {{ session('error') }}
</div>
@endif

<!-- ================= TIME RANGE PICKER BAR ================= -->
<div class="bg-white rounded-xl shadow-sm px-5 py-3 mb-6 flex items-center gap-4 flex-wrap relative">

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

    @if($range['preset'] !== 'active')
    <span class="ml-auto flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700
                 text-xs font-semibold rounded-full">
        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
        {{ $range['label'] }}
        <a href="{{ route('maintenance.index') }}"
           class="ml-1 text-blue-400 hover:text-blue-600 leading-none" title="Reset">✕</a>
    </span>
    @endif

    <!-- Export Button -->
    <button onclick="openExportModal()"
        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm
               font-semibold rounded-lg hover:bg-green-700 transition whitespace-nowrap {{ $range['preset'] === 'active' ? 'ml-auto' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </button>
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
                <form method="GET" action="{{ route('maintenance.index') }}" id="rangeForm">
                    <input type="hidden" name="range_preset" value="custom">
                    <div class="mb-3">
                        <label class="block text-xs text-gray-500 mb-1">From</label>
                        <input type="date" name="range_from"
                            value="{{ $range['preset'] === 'custom' && $range['from'] ? $range['from']->format('Y-m-d') : '' }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-1">To</label>
                        <input type="date" name="range_to"
                            value="{{ $range['preset'] === 'custom' && $range['to'] ? $range['to']->format('Y-m-d') : '' }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                    <button type="submit"
                        class="w-full py-2 bg-[#243B7C] text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
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
                        ['active',       'Active (default)'],
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
                        <a href="{{ route('maintenance.index', ['range_preset' => $p]) }}"
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
            <p class="text-sm text-gray-500">
                Maintained
                @if($range['preset'] !== 'active')
                    <span class="text-xs text-blue-500 block">{{ $range['label'] }}</span>
                @endif
            </p>
            <p class="text-2xl font-bold text-green-600">{{ $doneInRangeMap->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Need Maintenance</p>
            <p class="text-2xl font-bold text-yellow-600">{{ count($zbxDevices) - $doneToday }}</p>
        </div>
    </div>

</div>

<!-- ================= DEVICE LIST ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">

    <form id="maintenanceForm" action="{{ route('maintenance.store') }}" method="POST">
        @csrf

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-bold text-[#243B7C]">Device List</h3>
                @if(auth()->user()->isAdmin())
                    <p class="text-xs text-gray-400 mt-0.5">Check devices that have been maintained. Interval reset will follow your selected schedule.</p>
                @else
                    <p class="text-xs text-gray-400 mt-0.5">List of devices and their maintenance status. Resets automatically per configured interval.</p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search device..."
                        class="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-lg
                               focus:outline-none focus:ring-2 focus:ring-blue-400 w-48">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <select id="filterGroup" onchange="applyFilters()"
                    class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400 cursor-pointer">
                    <option value="">All Group</option>
                    @foreach(collect($zbxDevices)->pluck('groups')->filter()->unique()->sort() as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>

                <select id="filterMaintStatus" onchange="applyFilters()"
                    class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400 cursor-pointer">
                    <option value="">All Status</option>
                    <option value="done">Done</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto overflow-y-auto max-h-[55vh]">
            <table class="w-full text-sm" id="maintenanceTable">
                <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-200 sticky top-0 z-10 bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left w-10">
                            @if(auth()->user()->isAdmin())
                                <input type="checkbox" id="checkAll"
                                    class="w-4 h-4 cursor-pointer accent-blue-700" title="Select all">
                            @else
                                <span class="text-xs text-gray-400">✓</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Device Name</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">IP Address</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Group</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Availability</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">
                            Maintenance Status
                            @if($range['preset'] !== 'active')
                                <span class="text-xs text-blue-400 font-normal">({{ $range['label'] }})</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Last Maintenance</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Next Maintenance</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Interval</th>
                        <th class="px-6 py-3 text-left whitespace-nowrap">Done By</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-6 py-3 text-left whitespace-nowrap">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="deviceTableBody">
                    @forelse ($zbxDevices as $device)
                    @php
                        $lastMaint     = $lastMaintenanceMap[$device['hostid']] ?? null;
                        $isDoneToday   = isset($doneTodayMap[$device['hostid']]);
                        $isDoneInRange = isset($doneInRangeMap[$device['hostid']]);
                        $rangeRecord   = $doneInRangeMap[$device['hostid']] ?? null;
                        $nextMaint     = $lastMaint?->next_maintenance;
                        $intervalDays  = $lastMaint?->interval_days ?? 3;
                    @endphp
                    <tr class="device-row hover:bg-gray-50 transition"
                        data-name="{{ strtolower($device['host']) }}"
                        data-group="{{ $device['groups'] ?? '' }}"
                        data-maint-status="{{ $isDoneInRange ? 'done' : 'pending' }}"
                        data-device="{{ $device['host'] }}"
                        data-ip="{{ $device['ip'] }}"
                        data-group-val="{{ $device['groups'] ?? '-' }}"
                        data-availability="{{ $device['status'] }}"
                        data-maint-status-label="{{ $isDoneInRange ? 'Done' : 'Pending' }}"
                        data-last-maint="{{ $lastMaint ? ($lastMaint->done_at ? $lastMaint->done_at->format('d M Y, H:i') : $lastMaint->scheduled_date->format('d M Y')) : 'Never' }}"
                        data-next-maint="{{ $nextMaint ? \Carbon\Carbon::parse($nextMaint)->format('d M Y') : '-' }}"
                        data-interval="{{ $lastMaint ? $intervalDays . 'd' : '-' }}"
                        data-done-by="{{ ($isDoneInRange && $rangeRecord) ? ($rangeRecord->doneBy->name ?? 'System') : (($isDoneToday && $lastMaint) ? ($lastMaint->doneBy->name ?? 'System') : '-') }}">

                        <!-- Checkbox -->
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
                                @if($isDoneInRange)
                                    <span class="text-green-500 font-bold text-base">✓</span>
                                @else
                                    <span class="inline-block w-4 h-4 rounded border border-gray-300 bg-gray-50"></span>
                                @endif
                            @endif
                        </td>

                        <!-- Device Name -->
                        <td class="px-6 py-3 font-medium text-gray-800 whitespace-nowrap">
                            {{ $device['host'] }}
                        </td>

                        <!-- IP -->
                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap font-mono text-xs">
                            {{ $device['ip'] }}
                        </td>

                        <!-- Group -->
                        <td class="px-6 py-3 text-gray-600 text-xs whitespace-nowrap">
                            {{ $device['groups'] ?? '-' }}
                        </td>

                        <!-- Availability -->
                        <td class="px-6 py-3 whitespace-nowrap">
                            @php
                                $availBadge = match($device['status']) {
                                    'Online'  => 'bg-green-500',
                                    'Offline' => 'bg-red-500',
                                    default   => 'bg-yellow-400',
                                };
                            @endphp
                            <span class="text-white text-xs font-bold px-2 py-1 rounded {{ $availBadge }}">
                                {{ $device['status'] }}
                            </span>
                        </td>

                        <!-- Maintenance Status -->
                        <td class="px-6 py-3 whitespace-nowrap">
                            @if($isDoneInRange)
                                <span class="text-white text-xs font-bold px-2 py-1 rounded bg-green-500">Done</span>
                                @if($rangeRecord?->done_at && $range['preset'] !== 'active')
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $rangeRecord->done_at->format('d M Y') }}
                                    </div>
                                @endif
                            @else
                                <span class="text-white text-xs font-bold px-2 py-1 rounded bg-yellow-500">Pending</span>
                            @endif
                        </td>

                        <!-- Last Maintenance -->
                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap text-xs">
                            @if($lastMaint)
                                <span>{{ $lastMaint->done_at ? $lastMaint->done_at->format('d M Y, H:i') : $lastMaint->scheduled_date->format('d M Y') }}</span>
                                <span class="block text-gray-400 mt-0.5">
                                    {{ $lastMaint->done_at ? $lastMaint->done_at->diffForHumans() : '-' }}
                                </span>
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif
                        </td>

                        <!-- Next Maintenance -->
                        <td class="px-6 py-3 whitespace-nowrap text-xs">
                            @if($nextMaint)
                                @php $next = \Carbon\Carbon::parse($nextMaint); @endphp
                                @if($isDoneToday)
                                    <span class="text-blue-600 font-semibold">{{ $next->format('d M Y') }}</span>
                                    <span class="block text-gray-400">{{ $next->diffForHumans() }}</span>
                                @else
                                    <span class="text-red-500 font-semibold">{{ $next->format('d M Y') }}</span>
                                    <span class="block text-red-400">Overdue</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <!-- Interval -->
                        <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500">
                            @if($lastMaint)
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded font-medium">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    {{ $intervalDays }}d
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <!-- Done By -->
                        <td class="px-6 py-3 text-gray-600 whitespace-nowrap text-xs">
                            @if($isDoneInRange && $rangeRecord)
                                {{ $rangeRecord->doneBy->name ?? 'System' }}
                                <span class="block text-gray-400">{{ $rangeRecord->done_at?->format('H:i') }}</span>
                            @elseif($isDoneToday && $lastMaint)
                                {{ $lastMaint->doneBy->name ?? 'System' }}
                                <span class="block text-gray-400">{{ $lastMaint->done_at?->format('H:i') }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <!-- Action: Tandai Rusak (admin only) -->
                        @if(auth()->user()->isAdmin())
                        <td class="px-6 py-3 whitespace-nowrap">
                            <button type="button"
                                onclick="openBrokenModal(
                                    '{{ $device['hostid'] }}',
                                    '{{ addslashes($device['host']) }}',
                                    '{{ $device['ip'] }}',
                                    '{{ addslashes($device['groups'] ?? '') }}')"
                                class="text-orange-500 border border-orange-300 text-xs px-3 py-2
                                       rounded-lg hover:bg-orange-50 transition whitespace-nowrap">
                                Rusak
                            </button>
                        </td>
                        @endif

                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 11 : 10 }}"
                            class="px-6 py-16 text-center text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            No devices found. Make sure Zabbix is connected.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div id="emptyFilter" class="hidden py-12 text-center text-gray-400 text-sm">
                No devices match the selected filters.
            </div>
        </div>

        <!-- Action Bar: admin only -->
        @if(auth()->user()->isAdmin())
        <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                Selected: <span id="selCount" class="font-semibold text-gray-800">0</span> device(s)
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="clearSelection()"
                    class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                    Clear
                </button>
                <button type="button" id="submitBtn" disabled onclick="openConfirmModal()"
                    class="flex items-center gap-1.5 text-xs px-5 py-2 bg-blue-700 text-white rounded-lg
                           hover:bg-blue-800 transition disabled:opacity-40 disabled:cursor-not-allowed font-semibold">
                    ✓ Mark as Maintained (<span id="markCount">0</span>)
                </button>
            </div>
        </div>
        @endif

    </form>
</div>


<!-- ===================================================================
     MODAL TANDAI RUSAK  (admin only)
     =================================================================== -->
@if(auth()->user()->isAdmin())
<div id="brokenModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex items-center justify-between mb-4">
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
@endif


<!-- ================= MODAL CONFIRM MAINTENANCE (admin only) ================= -->
@if(auth()->user()->isAdmin())
<div id="confirmModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-[#243B7C]">Confirm Maintenance</h3>
            <button onclick="closeConfirmModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <p class="text-gray-500 text-sm mb-1">The following devices will be marked as maintained:</p>

        <div id="selectedDevicesList"
             class="bg-gray-50 rounded-lg px-4 py-3 mb-4 text-sm text-gray-700 max-h-40 overflow-y-auto space-y-1.5">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Next Maintenance Interval</label>
            <div class="flex gap-2 mb-3" id="intervalPresets">
                <button type="button" data-days="1"
                    class="interval-preset flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-200
                           text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition">
                    1 Day
                </button>
                <button type="button" data-days="3"
                    class="interval-preset flex-1 py-1.5 text-xs font-semibold rounded-lg border border-blue-500
                           bg-blue-50 text-blue-700 transition">
                    3 Days
                </button>
                <button type="button" data-days="7"
                    class="interval-preset flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-200
                           text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition">
                    7 Days
                </button>
                <button type="button" data-days="14"
                    class="interval-preset flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-200
                           text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition">
                    14 Days
                </button>
                <button type="button" data-days="30"
                    class="interval-preset flex-1 py-1.5 text-xs font-semibold rounded-lg border border-gray-200
                           text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition">
                    30 Days
                </button>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="number"
                        id="intervalCustomInput"
                        name="interval_days"
                        form="maintenanceForm"
                        min="1" max="365"
                        value="3"
                        class="w-full pl-3 pr-12 py-2 border border-gray-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <span class="absolute right-3 top-2 text-xs text-gray-400">days</span>
                </div>
                <p id="nextMaintPreview" class="text-xs text-blue-600 font-medium whitespace-nowrap">Next: —</p>
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-sm text-gray-700 mb-1">Notes (optional)</label>
            <textarea name="notes" id="notesInput" rows="3" form="maintenanceForm"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2
                       focus:ring-blue-400 resize-none text-sm"
                placeholder="e.g. Cleaned fan, updated firmware..."></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeConfirmModal()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Cancel
            </button>
            <button type="submit" form="maintenanceForm"
                class="px-5 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 text-sm">
                ✓ Save
            </button>
        </div>
    </div>
</div>
@endif


<!-- ================= MODAL EXPORT EXCEL ================= -->
<div id="exportModal"
     class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-7">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-[#243B7C]">Export Excel</h3>
                <p class="text-xs text-gray-400 mt-0.5">Select the period you want to export</p>
            </div>
            <button onclick="closeExportModal()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="space-y-1.5 mb-5 max-h-60 overflow-y-auto pr-1" id="exportPeriodList">
            @php
            $exportPresets = [
                ['today',        'Today',        'Maintenance records from today'],
                ['yesterday',    'Yesterday',    'Maintenance records from yesterday'],
                ['last_7',       'Last 7 Days',  'Past 7 days including today'],
                ['last_30',      'Last 30 Days', 'Past 30 days including today'],
                ['last_6months', 'Last 6 Months','Past 6 months including today'],
                ['last_year',    'Last 1 Year',  'Past 12 months including today'],
            ];
            @endphp
            @foreach($exportPresets as [$key, $label, $desc])
            <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200
                           cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition group">
                <input type="radio" name="exportPeriod" value="{{ $key }}"
                    class="w-4 h-4 accent-[#243B7C] cursor-pointer"
                    {{ $loop->first ? 'checked' : '' }}>
                <div class="flex-1">
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-[#243B7C]">{{ $label }}</span>
                    <span class="text-xs text-gray-400 block leading-tight">{{ $desc }}</span>
                </div>
            </label>
            @endforeach
        </div>

        <div class="flex justify-end gap-3 mt-2">
            <button onclick="closeExportModal()"
                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 text-sm">
                Cancel
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

// ── Range Picker ──────────────────────────────────────────
document.getElementById('btnOpenRangePicker').addEventListener('click', function () {
    document.getElementById('rangePickerPanel').classList.toggle('hidden');
});
function closeRangePicker() {
    document.getElementById('rangePickerPanel').classList.add('hidden');
}

// ── Filter Table ──────────────────────────────────────────
function applyFilters() {
    const search      = document.getElementById('searchInput').value.toLowerCase().trim();
    const group       = document.getElementById('filterGroup').value;
    const maintStatus = document.getElementById('filterMaintStatus').value;
    const rows        = document.querySelectorAll('.device-row');
    let visible       = 0;

    rows.forEach(row => {
        const nameMatch   = (row.dataset.name  || '').includes(search);
        const groupMatch  = group       === '' || row.dataset.group       === group;
        const statusMatch = maintStatus === '' || row.dataset.maintStatus === maintStatus;
        const show        = nameMatch && groupMatch && statusMatch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('emptyFilter').classList.toggle('hidden', visible > 0);
}
document.getElementById('searchInput').addEventListener('input', applyFilters);

// ── Export Modal ──────────────────────────────────────────
function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
}
function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}
document.getElementById('exportModal').addEventListener('click', e => {
    if (e.target === document.getElementById('exportModal')) closeExportModal();
});

function getDateRange(preset) {
    const now   = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const endOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59, 999);
    switch (preset) {
        case 'today':      return { from: today, to: endOfDay(today), label: 'Today' };
        case 'yesterday': {
            const yest = new Date(today); yest.setDate(yest.getDate() - 1);
            return { from: yest, to: endOfDay(yest), label: 'Yesterday' };
        }
        case 'last_7': {
            const f = new Date(today); f.setDate(f.getDate() - 6);
            return { from: f, to: endOfDay(today), label: 'Last 7 Days' };
        }
        case 'last_30': {
            const f = new Date(today); f.setDate(f.getDate() - 29);
            return { from: f, to: endOfDay(today), label: 'Last 30 Days' };
        }
        case 'last_6months': {
            const f = new Date(today); f.setMonth(f.getMonth() - 6);
            return { from: f, to: endOfDay(today), label: 'Last 6 Months' };
        }
        case 'last_year': {
            const f = new Date(today); f.setFullYear(f.getFullYear() - 1);
            return { from: f, to: endOfDay(today), label: 'Last 1 Year' };
        }
        default: return { from: null, to: null, label: 'All' };
    }
}

function parseDataDate(str) {
    if (!str || str === 'Never' || str === '-') return null;
    const clean = str.replace(/,.*$/, '').trim();
    const d = new Date(clean);
    return isNaN(d) ? null : d;
}

function doExport() {
    const preset = document.querySelector('input[name="exportPeriod"]:checked')?.value;
    if (!preset) { alert('Please select a period first.'); return; }

    const { from, to, label } = getDateRange(preset);
    const headers = [
        'No.', 'Device Name', 'IP Address', 'Group', 'Availability',
        'Maintenance Status', 'Last Maintenance', 'Next Maintenance', 'Interval', 'Done By'
    ];
    const rows = [headers];
    let no = 1;

    document.querySelectorAll('.device-row').forEach(row => {
        const lastMaintStr  = row.dataset.lastMaint ?? '';
        const lastMaintDate = parseDataDate(lastMaintStr);
        let include = false;
        if (from && to) {
            include = lastMaintDate !== null && lastMaintDate >= from && lastMaintDate <= to;
        } else {
            include = true;
        }
        if (!include) return;
        rows.push([
            no++,
            row.dataset.device           ?? '',
            row.dataset.ip               ?? '',
            row.dataset.groupVal         ?? '',
            row.dataset.availability     ?? '',
            row.dataset.maintStatusLabel ?? '',
            lastMaintStr,
            row.dataset.nextMaint        ?? '',
            row.dataset.interval         ?? '',
            row.dataset.doneBy           ?? '',
        ]);
    });

    if (rows.length <= 1) {
        alert(`No maintenance data found for the "${label}" period.`);
        return;
    }

    const ws = XLSX.utils.aoa_to_sheet(rows);
    ws['!cols'] = [
        { wch: 5 }, { wch: 28 }, { wch: 18 }, { wch: 20 }, { wch: 12 },
        { wch: 18 }, { wch: 22 }, { wch: 18 }, { wch: 10 }, { wch: 18 }
    ];

    const wb       = XLSX.utils.book_new();
    const sheet    = label.replace(/[\\/\?\*\[\]:]/g, '').substring(0, 31);
    XLSX.utils.book_append_sheet(wb, ws, sheet);

    const date     = new Date().toISOString().slice(0, 10);
    const safeName = label.replace(/\s+/g, '-').toLowerCase();
    XLSX.writeFile(wb, `maintenance-${safeName}-${date}.xlsx`);
    closeExportModal();
}

// ── Broken Device Modal ───────────────────────────────────
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
// ── Checkbox logic ────────────────────────────────────────
function getCheckboxes() {
    return [...document.querySelectorAll('.device-cb:not([disabled])')];
}

function updateUI() {
    const checked = getCheckboxes().filter(c => c.checked);
    const count   = checked.length;
    document.getElementById('selCount').textContent  = count;
    document.getElementById('markCount').textContent = count;
    document.getElementById('submitBtn').disabled    = count === 0;
    const all      = getCheckboxes();
    const checkAll = document.getElementById('checkAll');
    checkAll.indeterminate = count > 0 && count < all.length;
    checkAll.checked       = all.length > 0 && count === all.length;
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

document.getElementById('checkAll').addEventListener('change', function () {
    getCheckboxes().forEach(cb => { cb.checked = this.checked; });
    updateUI();
});

document.querySelectorAll('.device-cb').forEach(cb => {
    cb.addEventListener('change', updateUI);
});

// ── Interval picker ───────────────────────────────────────
const intervalInput    = document.getElementById('intervalCustomInput');
const nextMaintPreview = document.getElementById('nextMaintPreview');
const presetBtns       = document.querySelectorAll('.interval-preset');

function setIntervalValue(days) {
    intervalInput.value = days;
    updateNextMaintPreview(days);
    presetBtns.forEach(btn => {
        const isActive = parseInt(btn.dataset.days) === parseInt(days);
        btn.classList.toggle('border-blue-500',  isActive);
        btn.classList.toggle('bg-blue-50',        isActive);
        btn.classList.toggle('text-blue-700',     isActive);
        btn.classList.toggle('border-gray-200',  !isActive);
        btn.classList.toggle('text-gray-600',    !isActive);
    });
}

function updateNextMaintPreview(days) {
    const d = parseInt(days);
    if (!d || d < 1) { nextMaintPreview.textContent = 'Next: —'; return; }
    const next = new Date();
    next.setDate(next.getDate() + d);
    nextMaintPreview.textContent = 'Next: ' + next.toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
}

presetBtns.forEach(btn => {
    btn.addEventListener('click', () => setIntervalValue(btn.dataset.days));
});

intervalInput.addEventListener('input', function () {
    const val = parseInt(this.value);
    updateNextMaintPreview(val);
    presetBtns.forEach(btn => {
        const isActive = parseInt(btn.dataset.days) === val;
        btn.classList.toggle('border-blue-500',  isActive);
        btn.classList.toggle('bg-blue-50',        isActive);
        btn.classList.toggle('text-blue-700',     isActive);
        btn.classList.toggle('border-gray-200',  !isActive);
        btn.classList.toggle('text-gray-600',    !isActive);
    });
});

// ── Confirm modal ─────────────────────────────────────────
function openConfirmModal() {
    const checked = getCheckboxes().filter(c => c.checked);
    if (checked.length === 0) return;
    const list = document.getElementById('selectedDevicesList');
    list.innerHTML = '';
    checked.forEach(cb => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `<span class="text-blue-500 font-bold">✓</span><span>${cb.dataset.name}</span>`;
        list.appendChild(div);
    });
    setIntervalValue(3);
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