@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-6">
    Riwayat Log Aktivitas
</h2>

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <!-- Search -->
    <input
        type="text"
        id="searchInput"
        placeholder="Cari perangkat atau aktivitas"
        onkeyup="filterLog()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
    />

    <!-- Filter Tanggal -->
    <select
        id="filterTanggal"
        onchange="filterLog()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">
        <option value="">Semua Tanggal</option>
        <option value="12-02-2026">12-02-2026</option>
        <option value="11-02-2026">11-02-2026</option>
        <option value="10-02-2026">10-02-2026</option>
        <option value="08-02-2026">08-02-2026</option>
    </select>

</div>

<!-- ================= LOG TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="logTable">

            <thead class="text-[#243B7C] font-semibold border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left">Waktu</th>
                    <th class="px-6 py-4 text-left">ID Perangkat</th>
                    <th class="px-6 py-4 text-left">Jenis Aktivitas</th>
                    <th class="px-6 py-4 text-left">Detail</th>
                    <th class="px-6 py-4 text-left">User</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100" id="logBody">

                @php
                    $logs = [
                        [
                            'waktu'    => '12-02-2026 10:15',
                            'id'       => 'RTR-01',
                            'jenis'    => 'Perubahan Data',
                            'detail'   => 'IP diubah dari 192.168.1.1 menjadi 192.168.1.10',
                            'user'     => 'Admin',
                        ],
                        [
                            'waktu'    => '11-02-2026 09:00',
                            'id'       => 'SRV-02',
                            'jenis'    => 'Maintenance',
                            'detail'   => 'Pembersihan hardware & update firmware',
                            'user'     => 'Teknisi',
                        ],
                        [
                            'waktu'    => '10-02-2026 14:20',
                            'id'       => 'SWT-03',
                            'jenis'    => 'Kerusakan',
                            'detail'   => 'Port 3 tidak berfungsi',
                            'user'     => 'Admin',
                        ],
                        [
                            'waktu'    => '08-02-2026 16:00',
                            'id'       => 'RTR-02',
                            'jenis'    => 'Perpindahan Lokasi',
                            'detail'   => 'Dipindahkan dari MOVIO ke R.Acaraya',
                            'user'     => 'Teknisi',
                        ],
                        [
                            'waktu'    => '07-02-2026 11:30',
                            'id'       => 'AP-04',
                            'jenis'    => 'Perubahan Data',
                            'detail'   => 'SSID diubah dari UNAIR-GUEST menjadi UNAIR-LIB',
                            'user'     => 'Admin',
                        ],
                        [
                            'waktu'    => '06-02-2026 08:45',
                            'id'       => 'SWT-05',
                            'jenis'    => 'Maintenance',
                            'detail'   => 'Penggantian kabel port 1-8',
                            'user'     => 'Teknisi',
                        ],
                    ];

                    $badgeClass = [
                        'Perubahan Data'     => 'bg-blue-100 text-blue-700',
                        'Maintenance'        => 'bg-yellow-100 text-yellow-700',
                        'Kerusakan'          => 'bg-red-100 text-red-600',
                        'Perpindahan Lokasi' => 'bg-green-100 text-green-700',
                    ];
                @endphp

                @foreach ($logs as $log)
                    <tr class="hover:bg-gray-50 transition log-row"
                        data-waktu="{{ $log['waktu'] }}"
                        data-search="{{ strtolower($log['id'] . ' ' . $log['jenis'] . ' ' . $log['detail']) }}">

                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                            {{ $log['waktu'] }}
                        </td>

                        <td class="px-6 py-4 text-gray-800 font-medium">
                            {{ $log['id'] }}
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $cls = $badgeClass[$log['jenis']] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cls }}">
                                {{ $log['jenis'] }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $log['detail'] }}
                        </td>

                        <td class="px-6 py-4 text-gray-700">
                            {{ $log['user'] }}
                        </td>

                    </tr>
                @endforeach

            </tbody>
        </table>

        <!-- Empty State -->
        <div id="emptyState" class="hidden py-12 text-center text-gray-400 text-sm">
            Tidak ada log yang sesuai dengan pencarian.
        </div>

    </div>
</div>

<!-- ================= FILTER SCRIPT ================= -->
<script>
    function filterLog() {
        const search  = document.getElementById('searchInput').value.toLowerCase();
        const tanggal = document.getElementById('filterTanggal').value;
        const rows    = document.querySelectorAll('.log-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const matchSearch  = row.dataset.search.includes(search);
            const matchTanggal = tanggal === '' || row.dataset.waktu.startsWith(tanggal);

            if (matchSearch && matchTanggal) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        document.getElementById('emptyState').classList.toggle('hidden', visibleCount > 0);
    }
</script>

@endsection