@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Overview
</h2>

<!-- ================= SUMMARY CARD ================= -->
<div class="grid grid-cols-4 gap-6 mb-10">

    <div class="bg-white border-2 border-black rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Total Devices</p>
        <h3 class="text-4xl font-bold mt-2">{{ $total }}</h3>
    </div>

    <div class="bg-white border-2 border-green-500 rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Available</p>
        <h3 class="text-4xl font-bold text-green-600 mt-2">{{ $online }}</h3>
    </div>

    <div class="bg-white border-2 border-red-500 rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Not Available</p>
        <h3 class="text-4xl font-bold text-red-600 mt-2">{{ $offline }}</h3>
    </div>

    <div class="bg-white border-2 border-yellow-400 rounded-xl p-6 shadow-sm">
        <p class="text-gray-600">Unknown</p>
        <h3 class="text-4xl font-bold text-yellow-500 mt-2">{{ $total - $online - $offline }}</h3>
    </div>

</div>

<!-- ================= TRAFFIC GRAPHS ================= -->
<h3 class="text-xl font-semibold text-gray-700 mb-4">
    Network Traffic
</h3>

<div class="grid grid-cols-2 gap-6 mb-10">

    <!-- PERPUS B -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-[#243B7C]">TRAFFIC PERPUS B</h4>
            <div class="flex items-center gap-2">
                <div id="loadingB" class="hidden">
                    <svg class="animate-spin w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
                <select id="periodB" onchange="loadChart('B', this.value)"
                    class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option value="3600">Last 1 hour</option>
                    <option value="14400" selected>Last 4 hours</option>
                    <option value="43200">Last 12 hours</option>
                    <option value="86400">Last 1 day</option>
                    <option value="604800">Last 7 days</option>
                    <option value="2592000">Last 30 days</option>
                    <option value="31536000">Last 1 year</option>
                </select>
            </div>
        </div>
        <div id="errorB" class="hidden py-10 text-center text-gray-400 text-sm">Graph tidak tersedia</div>
        <canvas id="chartB" height="90"></canvas>
    </div>

    <!-- PERPUS C -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-[#243B7C]">TRAFFIC PERPUS C</h4>
            <div class="flex items-center gap-2">
                <div id="loadingC" class="hidden">
                    <svg class="animate-spin w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
                <select id="periodC" onchange="loadChart('C', this.value)"
                    class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option value="3600">Last 1 hour</option>
                    <option value="14400" selected>Last 4 hours</option>
                    <option value="43200">Last 12 hours</option>
                    <option value="86400">Last 1 day</option>
                    <option value="604800">Last 7 days</option>
                    <option value="2592000">Last 30 days</option>
                    <option value="31536000">Last 1 year</option>
                </select>
            </div>
        </div>
        <div id="errorC" class="hidden py-10 text-center text-gray-400 text-sm">Graph tidak tersedia</div>
        <canvas id="chartC" height="90"></canvas>
    </div>

</div>

<!-- ================= MAINTENANCE OVERVIEW ================= -->
<h3 class="text-xl font-semibold text-gray-700 mb-4">
    Maintenance
</h3>

<div class="grid grid-cols-2 gap-6 mb-10">

    {{-- Donut Chart --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-8">
        <div class="relative w-36 h-36 flex-shrink-0">
            <canvas id="maintDonut"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-2xl font-bold text-[#243B7C]">{{ $maintTotal }}</span>
                <span class="text-xs text-gray-400">Total</span>
            </div>
        </div>
        <div class="space-y-3 flex-1">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-green-500 inline-block flex-shrink-0"></span>
                <span class="text-sm text-gray-600">Maintained</span>
                <span class="ml-auto font-bold text-green-600 text-lg">{{ $maintDone }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block flex-shrink-0"></span>
                <span class="text-sm text-gray-600">Need Maintenance</span>
                <span class="ml-auto font-bold text-yellow-500 text-lg">{{ $maintPending }}</span>
            </div>
            <div class="pt-2 border-t border-gray-100">
                <a href="{{ route('maintenance.index') }}"
                   class="text-xs text-blue-600 hover:underline font-medium">
                    View Maintenance Schedule →
                </a>
            </div>
        </div>
    </div>

    {{-- Progress Bar Card --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col justify-center gap-5">
        <div>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600 font-medium">Maintained</span>
                <span class="text-green-600 font-bold">
                    {{ $maintTotal > 0 ? round($maintDone / $maintTotal * 100) : 0 }}%
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="bg-green-500 h-3 rounded-full transition-all duration-500"
                     style="width: {{ $maintTotal > 0 ? round($maintDone / $maintTotal * 100) : 0 }}%">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $maintDone }} dari {{ $maintTotal }} device</p>
        </div>
        <div>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600 font-medium">Need Maintenance</span>
                <span class="text-yellow-500 font-bold">
                    {{ $maintTotal > 0 ? round($maintPending / $maintTotal * 100) : 0 }}%
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="bg-yellow-400 h-3 rounded-full transition-all duration-500"
                     style="width: {{ $maintTotal > 0 ? round($maintPending / $maintTotal * 100) : 0 }}%">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $maintPending }} dari {{ $maintTotal }} device</p>
        </div>
        <p class="text-xs text-gray-400 border-t border-gray-100 pt-3">
            Data diperbarui setiap kali halaman dimuat.
        </p>
    </div>

</div>

<!-- ================= PROBLEM SEVERITY ================= -->
<h3 class="text-xl font-semibold text-gray-700 mb-4">
    Problems by severity
</h3>

@php
    $severityConfig = [
        5 => ['label' => 'Disaster',       'bg' => 'bg-[#E27D74]'],
        4 => ['label' => 'High',           'bg' => 'bg-[#E89A6D]'],
        3 => ['label' => 'Average',        'bg' => 'bg-[#E7BE78]'],
        2 => ['label' => 'Warning',        'bg' => 'bg-[#E8D38A]'],
        1 => ['label' => 'Information',    'bg' => 'bg-[#8AA3D8]'],
        0 => ['label' => 'Not classified', 'bg' => 'bg-gray-300'],
    ];
@endphp

<div class="grid grid-cols-6 overflow-hidden rounded-lg mb-10 shadow-sm">
    @foreach ($severityConfig as $sev => $cfg)
    <div class="{{ $cfg['bg'] }} text-center py-6">
        <p class="text-lg font-semibold">{{ $severity[$sev] ?? 0 }}</p>
        <p class="text-sm">{{ $cfg['label'] }}</p>
    </div>
    @endforeach
</div>

<!-- ================= CURRENT PROBLEMS ================= -->
<h3 class="text-xl font-semibold text-gray-700 mb-4">
    Current problems
</h3>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="max-h-[500px] overflow-y-auto overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-200 text-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="p-4 text-left">Time</th>
                    <th class="p-4 text-left">Host</th>
                    <th class="p-4 text-left min-w-[420px]">Problem • Severity</th>
                    <th class="p-4 text-left">Duration</th>
                    <th class="p-4 text-left">Tags</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($problems as $p)
                <tr class="border-t hover:bg-gray-50 transition">
                    <td class="p-4 whitespace-nowrap text-gray-600">{{ $p['time'] }}</td>
                    <td class="p-4 text-blue-600 font-medium">{{ $p['host'] }}</td>
                    <td class="p-4">
                        <span class="{{ $p['color'] }} px-3 py-2 rounded inline-block text-gray-800">
                            {{ $p['name'] }}
                        </span>
                    </td>
                    <td class="p-4 whitespace-nowrap text-gray-600">{{ $p['duration'] }}</td>
                    <td class="p-4 text-xs text-gray-500 whitespace-nowrap">{{ $p['tags'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400">
                        Tidak ada problem saat ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script>
// ─── Maintenance Donut ────────────────────────────────────────
const maintCtx = document.getElementById('maintDonut').getContext('2d');
const done    = {{ $maintDone }};
const pending = {{ $maintPending }};

new Chart(maintCtx, {
    type: 'doughnut',
    data: {
        labels: ['Maintained', 'Need Maintenance'],
        datasets: [{
            data: done === 0 && pending === 0 ? [1] : [done, pending],
            backgroundColor: done === 0 && pending === 0
                ? ['#e5e7eb'] : ['#22c55e', '#facc15'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '72%',
        plugins: {
            legend: { display: false },
            tooltip: { enabled: done !== 0 || pending !== 0 }
        },
    }
});

// ─── Network Traffic Charts ───────────────────────────────────
const ITEM_IDS = {
    B: { in: '50875', out: '50947' },  // Router B — sfp-sfpplus1-PUBLIK
    C: { in: '51132', out: '51186' },  // Router C — sfp-sfpplus1-PUBLIC BACKBONE UNAIR
};

const trafficCharts = {};

function formatBits(bps) {
    if (bps === null || isNaN(bps)) return '-';
    const abs = Math.abs(bps);
    if (abs >= 1e9) return (bps / 1e9).toFixed(2) + ' Gbps';
    if (abs >= 1e6) return (bps / 1e6).toFixed(2) + ' Mbps';
    if (abs >= 1e3) return (bps / 1e3).toFixed(2) + ' Kbps';
    return bps.toFixed(1) + ' bps';
}

async function fetchMetric(itemid, period) {
    const res = await fetch(`/zabbix-metric?itemid=${itemid}&period=${period}`);
    return res.ok ? await res.json() : [];
}

async function loadChart(type, period = 14400) {
    const ids      = ITEM_IDS[type];
    const loading  = document.getElementById('loading' + type);
    const errorEl  = document.getElementById('error' + type);
    const canvas   = document.getElementById('chart' + type);

    loading.classList.remove('hidden');
    errorEl.classList.add('hidden');
    canvas.style.display = '';

    try {
        const [dataIn, dataOut] = await Promise.all([
            fetchMetric(ids.in,  period),
            fetchMetric(ids.out, period),
        ]);

        if (dataIn.length === 0 && dataOut.length === 0) {
            canvas.style.display = 'none';
            errorEl.textContent  = 'Tidak ada data untuk periode ini.';
            errorEl.classList.remove('hidden');
            loading.classList.add('hidden');
            return;
        }

        if (trafficCharts[type]) trafficCharts[type].destroy();

        const ctx = canvas.getContext('2d');
        trafficCharts[type] = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label           : 'Inbound',
                        data            : dataIn,
                        borderColor     : '#3b82f6',
                        backgroundColor : 'rgba(59,130,246,0.08)',
                        borderWidth     : 1.5,
                        pointRadius     : 0,
                        pointHoverRadius: 4,
                        fill            : true,
                        tension         : 0.3,
                    },
                    {
                        label           : 'Outbound',
                        data            : dataOut,
                        borderColor     : '#10b981',
                        backgroundColor : 'rgba(16,185,129,0.06)',
                        borderWidth     : 1.5,
                        pointRadius     : 0,
                        pointHoverRadius: 4,
                        fill            : true,
                        tension         : 0.3,
                    },
                ]
            },
            options: {
                responsive  : true,
                animation   : false,
                interaction : { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display  : true,
                        position : 'top',
                        labels   : { boxWidth: 12, font: { size: 11 }, color: '#6b7280' },
                    },
                    tooltip: {
                        backgroundColor : 'rgba(15,23,42,0.85)',
                        titleFont       : { size: 11 },
                        bodyFont        : { size: 12, weight: 'bold' },
                        padding         : 10,
                        callbacks: {
                            title: (items) => {
                                const d = new Date(items[0].parsed.x);
                                return d.toLocaleString('id-ID', {
                                    day: '2-digit', month: 'short',
                                    hour: '2-digit', minute: '2-digit'
                                });
                            },
                            label: (item) => ' ' + item.dataset.label + ': ' + formatBits(item.parsed.y),
                        }
                    }
                },
                scales: {
                    x: {
                        type : 'time',
                        time : {
                            tooltipFormat  : 'HH:mm:ss',
                            displayFormats : { minute: 'HH:mm', hour: 'HH:mm' }
                        },
                        ticks : { maxTicksLimit: 8, font: { size: 10 }, color: '#9ca3af' },
                        grid  : { display: false },
                    },
                    y: {
                        beginAtZero : true,
                        ticks: {
                            font     : { size: 10 },
                            color    : '#9ca3af',
                            callback : (val) => formatBits(val),
                        },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                    }
                }
            }
        });

    } catch (err) {
        canvas.style.display = 'none';
        errorEl.textContent  = 'Gagal memuat data grafik: ' + err.message;
        errorEl.classList.remove('hidden');
    } finally {
        loading.classList.add('hidden');
    }
}

// Load saat halaman dibuka
loadChart('B', 14400);
loadChart('C', 14400);

// Auto-refresh tiap 60 detik
setInterval(() => {
    loadChart('B', document.getElementById('periodB').value);
    loadChart('C', document.getElementById('periodC').value);
}, 60000);
</script>

@endsection