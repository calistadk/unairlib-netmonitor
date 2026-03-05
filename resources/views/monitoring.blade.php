@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Monitoring
</h2>

<!-- ================= MONITORING TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            <thead class="text-[#243B7C] font-semibold border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">Nama</th>
                    <th class="px-6 py-4 text-left">Jenis</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @php
                    $perangkat = [
                        ['id' => 'RTR-01', 'nama' => 'Core Router',    'jenis' => 'Router', 'status' => 'Online'],
                        ['id' => 'SWT-02', 'nama' => 'Switch Lt 1',    'jenis' => 'Switch', 'status' => 'Offline'],
                        ['id' => 'SRV-03', 'nama' => 'Server Lt 1',    'jenis' => 'Server', 'status' => 'Online'],
                        ['id' => 'AP-04',  'nama' => 'Access Point 1', 'jenis' => 'AP',     'status' => 'Online'],
                        ['id' => 'SWT-05', 'nama' => 'Switch Lt 2',    'jenis' => 'Switch', 'status' => 'Online'],
                    ];
                @endphp

                @foreach ($perangkat as $index => $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-700">{{ $item['id'] }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $item['nama'] }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item['jenis'] }}</td>

                        <td class="px-6 py-4">
                            @if ($item['status'] === 'Online')
                                <span class="text-green-500 font-medium">Online</span>
                            @else
                                <span class="text-red-500 font-medium">Offline</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <button
                                onclick="toggleDetail('detail-{{ $index }}', '{{ $item['nama'] }}')"
                                class="bg-[#1a1a2e] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#243B7C] transition">
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>

<!-- ================= DETAIL CHARTS (hidden by default) ================= -->
@foreach ($perangkat as $index => $item)
<div id="detail-{{ $index }}" class="hidden space-y-6 mb-6">

    <!-- CPU Usage -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#243B7C] mb-4">
            CPU Usage ({{ $item['nama'] }})
        </h3>
        <canvas id="cpu-chart-{{ $index }}" height="100"></canvas>
    </div>

    <!-- Memory Usage -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#243B7C] mb-4">
            Memory Usage ({{ $item['nama'] }})
        </h3>
        <canvas id="mem-chart-{{ $index }}" height="100"></canvas>
    </div>

    <!-- Network Traffic -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#243B7C] mb-4">
            Network Traffic ({{ $item['nama'] }})
        </h3>
        <canvas id="net-chart-{{ $index }}" height="100"></canvas>
    </div>

</div>
@endforeach

<!-- ================= CHART.JS ================= -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
    const chartColor = '#D4A017';
    const initializedCharts = {};

    const dummyData = {
        cpu: [
            [20, 28, 40, 15, 60, 85, 45],
            [30, 35, 50, 20, 55, 70, 40],
            [15, 22, 38, 18, 65, 80, 50],
            [25, 30, 45, 22, 58, 75, 42],
            [18, 26, 42, 16, 62, 78, 48],
        ],
        mem: [
            [18, 25, 38, 12, 58, 88, 42],
            [28, 32, 48, 18, 52, 68, 38],
            [12, 20, 35, 15, 62, 78, 48],
            [22, 28, 42, 20, 55, 72, 40],
            [16, 24, 40, 14, 60, 76, 46],
        ],
        net: [
            [10, 40, 30, 55, 20, 70, 35],
            [20, 50, 40, 65, 30, 80, 45],
            [15, 45, 35, 60, 25, 75, 40],
            [12, 42, 32, 57, 22, 72, 37],
            [18, 48, 38, 63, 28, 78, 43],
        ],
    };

    function makeChartConfig(label, dataArr) {
        return {
            type: 'line',
            data: {
                labels: ['10:00', '10:02', '10:05', '10:10', '10:15', '10:18', '10:20'],
                datasets: [{
                    label: label,
                    data: dataArr,
                    borderColor: chartColor,
                    backgroundColor: 'rgba(212,160,23,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: chartColor,
                    pointRadius: 4,
                    tension: 0.45,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { stepSize: 20 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        };
    }

    function toggleDetail(id, namaDevice) {
        const el = document.getElementById(id);
        const isHidden = el.classList.contains('hidden');

        // Tutup semua detail dulu
        document.querySelectorAll('[id^="detail-"]').forEach(d => d.classList.add('hidden'));

        if (!isHidden) return; // kalau sudah terbuka, cukup tutup

        el.classList.remove('hidden');

        setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);

        if (initializedCharts[id]) return; // skip jika chart sudah dibuat

        const index = parseInt(id.replace('detail-', ''));

        new Chart(
            document.getElementById('cpu-chart-' + index),
            makeChartConfig('CPU %', dummyData.cpu[index] ?? dummyData.cpu[0])
        );
        new Chart(
            document.getElementById('mem-chart-' + index),
            makeChartConfig('Memory %', dummyData.mem[index] ?? dummyData.mem[0])
        );
        new Chart(
            document.getElementById('net-chart-' + index),
            makeChartConfig('Network Mbps', dummyData.net[index] ?? dummyData.net[0])
        );

        initializedCharts[id] = true;
    }
</script>

@endsection