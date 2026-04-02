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

<!-- ================= PROGRESS HARI INI ================= -->
<div class="grid grid-cols-3 gap-6 mb-8">

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Today's Target</p>
            <p class="text-2xl font-bold text-[#243B7C]">5 Devices</p>
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
            <p class="text-2xl font-bold text-green-600">{{ $doneToday }} / {{ $totalToday }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Remaining Today</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $totalToday - $doneToday }}</p>
        </div>
    </div>

</div>

<div class="grid grid-cols-3 gap-6">

    <!-- ================= JADWAL HARI INI ================= -->
    <div class="col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-[#243B7C]">
                Today's Schedule
                <span class="text-sm font-normal text-gray-400 ml-2">{{ now()->format('d M Y') }}</span>
            </h3>
            @if(auth()->user()->isAdmin())
            <button onclick="openAddModal()"
                class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                + Add Schedule
            </button>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto max-h-[50vh]">
        <table class="w-full text-sm border border-gray-200">
            <thead class="text-[#243B7C] font-semibold border-b-2 border-gray-300 sticky top-0 z-10 bg-white">
                <tr>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Device</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Next Maintenance</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Done By</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse ($todaySchedules as $s)
                <tr class="hover:bg-gray-50 transition {{ $s->is_done ? 'opacity-60' : '' }}">
                    <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $s->device_name }}</td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs">
                        {{ $s->next_maintenance->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if ($s->is_done)
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                ✓ Done
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                        {{ $s->is_done ? ($s->doneBy->name ?? 'System') : '-' }}
                        @if ($s->done_at)
                            <span class="block text-gray-400">{{ $s->done_at->format('H:i') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if(!$s->is_done && auth()->user()->isAdmin())
                        <button onclick="openDoneModal({{ $s->id }}, '{{ $s->device_name }}')"
                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition">
                            ✓ Mark Done
                        </button>
                        @endif
                        @if(auth()->user()->isAdmin())
                        <form action="{{ route('maintenance.destroy', $s->id) }}" method="POST" class="inline"
                            onsubmit="return confirm('Delete this schedule?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-red-100 text-red-600 text-xs rounded hover:bg-red-200 transition ml-1">
                                Delete
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                        No maintenance scheduled for today.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </div>
    </div>

    <!-- ================= UPCOMING ================= -->
    <div>
        <h3 class="text-lg font-bold text-[#243B7C] mb-4">Upcoming (7 Days)</h3>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-y-auto max-h-[50vh]">
            @forelse ($upcoming as $u)
            <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800 text-sm">{{ $u->device_name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $u->scheduled_date->format('d M Y') }}
                            <span class="ml-1 text-blue-500">
                                ({{ now()->diffInDays($u->scheduled_date) }}d left)
                            </span>
                        </p>
                    </div>
                    <span class="text-xs text-gray-400">Every 3d</span>
                </div>
            </div>
            @empty
            <div class="px-4 py-10 text-center text-gray-400 text-sm">
                No upcoming schedules.
            </div>
            @endforelse
        </div>
        </div>
    </div>

</div>

<!-- ================= MODAL TAMBAH JADWAL ================= -->
<div id="addModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-[#243B7C]">Add Maintenance Schedule</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('maintenance.store') }}" method="POST">
            @csrf

            <div class="space-y-4">

                <div>
                    <label class="block text-sm text-gray-700 mb-1">Device <span class="text-red-500">*</span></label>

                    @if (count($zbxDevices) > 0)
                    {{-- Zabbix tersambung: tampilkan dropdown --}}
                    <select name="device_id" id="deviceSelect" onchange="fillDeviceName()"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                        <option value="">Select device from Zabbix</option>
                        @foreach ($zbxDevices as $zd)
                        <option value="{{ $zd['hostid'] }}" data-name="{{ $zd['host'] }}">
                            {{ $zd['host'] }} — {{ $zd['ip'] }}
                        </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="device_name" id="deviceNameInput">
                    @else
                    {{-- Zabbix tidak tersambung: input manual --}}
                    <input type="text" name="device_name"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
                        placeholder="e.g. Router A, Switch MOVIO..." required>
                    <input type="hidden" name="device_id" value="manual">
                    <p class="text-xs text-yellow-600 mt-1">⚠ Zabbix not connected. Enter device name manually.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm text-gray-700 mb-1">Scheduled Date <span class="text-red-500">*</span></label>
                    <input type="date" name="scheduled_date"
                        min="{{ now()->format('Y-m-d') }}"
                        value="{{ now()->format('Y-m-d') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                </div>

                <p class="text-xs text-gray-400">
                    Next maintenance will be automatically scheduled 3 days after.
                </p>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeAddModal()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800">
                    Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL MARK DONE ================= -->
<div id="doneModal"
     class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-[2px] z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-8">

        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-[#243B7C]">Mark as Done</h3>
            <button onclick="closeDoneModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <p class="text-gray-600 text-sm mb-4">
            Confirm maintenance completed for:
            <span id="doneDeviceName" class="font-semibold text-gray-800"></span>
        </p>

        <form id="doneForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 resize-none"
                    placeholder="e.g. Cleaned fan, updated firmware..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDoneModal()"
                    class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700">
                    ✓ Confirm Done
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
function openAddModal()  { document.getElementById('addModal').classList.remove('hidden'); }
function closeAddModal() { document.getElementById('addModal').classList.add('hidden'); }

function openDoneModal(id, name) {
    document.getElementById('doneDeviceName').textContent = name;
    document.getElementById('doneForm').action = '/maintenance/' + id + '/done';
    document.getElementById('doneModal').classList.remove('hidden');
}
function closeDoneModal() { document.getElementById('doneModal').classList.add('hidden'); }

function fillDeviceName() {
    const select = document.getElementById('deviceSelect');
    if (!select) return;
    const option = select.options[select.selectedIndex];
    const input  = document.getElementById('deviceNameInput');
    if (input) input.value = option.dataset.name || '';
}

document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});
document.getElementById('doneModal').addEventListener('click', function(e) {
    if (e.target === this) closeDoneModal();
});
</script>

@endsection