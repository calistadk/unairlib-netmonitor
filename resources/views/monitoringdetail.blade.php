@extends('layouts.app')

@section('content')

<!-- ================= BACK + TITLE ================= -->
<div class="flex items-center gap-4 mb-8">
    <a href="/monitoring"
       class="text-[#243B7C] hover:text-blue-500 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round"
             stroke-linejoin="round" viewBox="0 0 24 24">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
    </a>
    <div>
        <h2 class="text-3xl font-bold text-[#243B7C]">{{ $host['nama'] }}</h2>
        <div class="flex items-center gap-3 mt-1">
            <span class="font-mono text-sm text-gray-500">{{ $host['interface'] }}</span>
            @php
                $badgeColor = match($host['status']) {
                    'Online'  => 'bg-green-500',
                    'Offline' => 'bg-red-500',
                    default   => 'bg-yellow-400',
                };
            @endphp
            <span class="text-white text-xs font-bold px-2 py-1 rounded {{ $badgeColor }}">
                {{ $host['iface_type'] }}
            </span>
        </div>
    </div>
</div>

<!-- ================= GRAPHS ================= -->
@if (count($host['graphs']) > 0)

    @php
    function getGraphInfo(string $graphName): array {
        $name = strtolower($graphName);

        if (str_contains($name, 'cpu utilization') || str_contains($name, 'cpu usage')) {
            return ['title' => 'CPU Utilization', 'color' => 'blue',
                'what' => 'Persentase penggunaan CPU dari waktu ke waktu.',
                'normal' => 'Di bawah 70–80% secara konsisten.',
                'warn' => 'Di atas 90% dalam waktu lama menyebabkan sistem melambat.',
                'tips' => 'Identifikasi proses beban tinggi menggunakan `top` atau `htop`.'];
        }
        if (str_contains($name, 'cpu jumps')) {
            return ['title' => 'CPU Jumps', 'color' => 'yellow',
                'what' => 'Lonjakan tiba-tiba pada penggunaan CPU.',
                'normal' => 'Spike kecil dan singkat masih normal.',
                'warn' => 'Lonjakan sering dan tinggi menandakan proses tidak efisien.',
                'tips' => 'Periksa scheduled task dan log sistem saat spike terjadi.'];
        }
        if (str_contains($name, 'load average')) {
            return ['title' => 'Load Average', 'color' => 'purple',
                'what' => 'Rata-rata jumlah proses yang berjalan atau menunggu CPU.',
                'normal' => 'Idealnya tidak melebihi jumlah core CPU.',
                'warn' => 'Melebihi jumlah CPU core secara konsisten.',
                'tips' => 'Cek jumlah core dengan `nproc`. Kurangi concurrent process.'];
        }
        if (str_contains($name, 'memory utilization') || str_contains($name, 'memory usage')) {
            return ['title' => 'Memory Utilization', 'color' => 'green',
                'what' => 'Penggunaan RAM dari total yang tersedia.',
                'normal' => '60–80% masih wajar. Linux aktif menggunakan sisa memory untuk cache.',
                'warn' => 'Mendekati 95–100% konsisten, terutama jika diikuti peningkatan swap.',
                'tips' => 'Gunakan `free -h` dan `ps aux --sort=-%mem` untuk analisis.'];
        }
        if (str_contains($name, 'network traffic') || str_contains($name, 'network')) {
            return ['title' => 'Network Traffic', 'color' => 'teal',
                'what' => 'Lalu lintas jaringan inbound dan outbound dalam bps/Bps.',
                'normal' => 'Traffic stabil sesuai pola penggunaan normal.',
                'warn' => 'Lonjakan tidak wajar bisa indikasi DDoS atau transfer data besar.',
                'tips' => 'Monitor dengan `iftop` atau `nethogs` untuk cari sumber traffic.'];
        }
        if (str_contains($name, 'disk') && str_contains($name, 'io')) {
            return ['title' => 'Disk I/O', 'color' => 'orange',
                'what' => 'Aktivitas baca (read) dan tulis (write) pada storage.',
                'normal' => 'I/O stabil dan tidak terus-menerus tinggi.',
                'warn' => 'I/O wait tinggi memperlambat seluruh sistem.',
                'tips' => 'Gunakan `iostat -x`. Pertimbangkan upgrade ke SSD.'];
        }
        if (str_contains($name, 'disk space') || str_contains($name, 'disk usage')) {
            return ['title' => 'Disk Space', 'color' => 'red',
                'what' => 'Kapasitas ruang penyimpanan yang terpakai.',
                'normal' => 'Idealnya di bawah 80%.',
                'warn' => 'Di atas 90% sangat berbahaya, sistem bisa tidak bisa menulis.',
                'tips' => 'Gunakan `df -h` dan `du -sh /*` untuk cari folder terbesar.'];
        }
        if (str_contains($name, 'swap')) {
            return ['title' => 'Swap Usage', 'color' => 'pink',
                'what' => 'Ruang disk yang digunakan sebagai memory cadangan.',
                'normal' => 'Idealnya hampir tidak terpakai (0–5%).',
                'warn' => 'Terus meningkat menandakan RAM tidak mencukupi.',
                'tips' => 'Tambah RAM fisik. Identifikasi proses yang memory leak.'];
        }
        if (str_contains($name, 'temperature') || str_contains($name, 'temp')) {
            return ['title' => 'Temperature', 'color' => 'red',
                'what' => 'Suhu komponen hardware seperti CPU atau hard disk.',
                'normal' => 'CPU 40–70°C, HDD di bawah 45°C.',
                'warn' => 'CPU di atas 85°C atau HDD di atas 55°C berbahaya.',
                'tips' => 'Periksa sirkulasi udara, bersihkan debu dari heatsink.'];
        }
        if (str_contains($name, 'icmp') || str_contains($name, 'ping')) {
            return ['title' => 'ICMP / Ping', 'color' => 'cyan',
                'what' => 'Waktu respons ping (latency) ke device.',
                'normal' => 'Di bawah 10ms untuk jaringan lokal.',
                'warn' => 'Latency tinggi atau packet loss menandakan masalah koneksi.',
                'tips' => 'Gunakan `traceroute` untuk menemukan hop bermasalah.'];
        }
        if (str_contains($name, 'uptime') || str_contains($name, 'availability')) {
            return ['title' => 'Uptime / Availability', 'color' => 'green',
                'what' => 'Berapa lama device berjalan tanpa restart.',
                'normal' => 'Uptime tinggi (99%+) menandakan sistem stabil.',
                'warn' => 'Uptime sering reset menandakan masalah stabilitas.',
                'tips' => 'Periksa log sistem untuk mengetahui penyebab restart.'];
        }
        return ['title' => 'Grafik Monitoring', 'color' => 'gray',
            'what' => 'Metrik monitoring real-time dari Zabbix.',
            'normal' => 'Tren stabil biasanya menandakan kondisi sistem sehat.',
            'warn' => 'Lonjakan atau penurunan mendadak bisa jadi indikator awal masalah.',
            'tips' => 'Konfigurasikan trigger di Zabbix untuk notifikasi otomatis.'];
    }

    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',  'head' => 'text-blue-700',  'dot' => 'bg-blue-400',   'btn' => 'bg-blue-600 hover:bg-blue-700'],
        'yellow' => ['bg' => 'bg-yellow-50',  'border' => 'border-yellow-200','head' => 'text-yellow-700','dot' => 'bg-yellow-400', 'btn' => 'bg-yellow-600 hover:bg-yellow-700'],
        'purple' => ['bg' => 'bg-purple-50',  'border' => 'border-purple-200','head' => 'text-purple-700','dot' => 'bg-purple-400', 'btn' => 'bg-purple-600 hover:bg-purple-700'],
        'green'  => ['bg' => 'bg-green-50',   'border' => 'border-green-200', 'head' => 'text-green-700', 'dot' => 'bg-green-400',  'btn' => 'bg-green-600 hover:bg-green-700'],
        'teal'   => ['bg' => 'bg-teal-50',    'border' => 'border-teal-200',  'head' => 'text-teal-700',  'dot' => 'bg-teal-400',   'btn' => 'bg-teal-600 hover:bg-teal-700'],
        'orange' => ['bg' => 'bg-orange-50',  'border' => 'border-orange-200','head' => 'text-orange-700','dot' => 'bg-orange-400', 'btn' => 'bg-orange-600 hover:bg-orange-700'],
        'red'    => ['bg' => 'bg-red-50',     'border' => 'border-red-200',   'head' => 'text-red-700',   'dot' => 'bg-red-400',    'btn' => 'bg-red-600 hover:bg-red-700'],
        'pink'   => ['bg' => 'bg-pink-50',    'border' => 'border-pink-200',  'head' => 'text-pink-700',  'dot' => 'bg-pink-400',   'btn' => 'bg-pink-600 hover:bg-pink-700'],
        'cyan'   => ['bg' => 'bg-cyan-50',    'border' => 'border-cyan-200',  'head' => 'text-cyan-700',  'dot' => 'bg-cyan-400',   'btn' => 'bg-cyan-600 hover:bg-cyan-700'],
        'gray'   => ['bg' => 'bg-gray-50',    'border' => 'border-gray-200',  'head' => 'text-gray-700',  'dot' => 'bg-gray-400',   'btn' => 'bg-gray-600 hover:bg-gray-700'],
    ];

    $GLOBALS['hostName'] = $host['nama'];
    @endphp

    <div class="space-y-6">
        @foreach ($host['graphs'] as $graph)
        @php
            $info   = getGraphInfo($graph['name']);
            $colors = $colorMap[$info['color']] ?? $colorMap['gray'];
            $gid    = $graph['graphid'];
        @endphp

        <div class="bg-white rounded-xl shadow-sm p-6">

            {{-- Graph header --}}
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#243B7C]">{{ $graph['name'] }}</h3>
                <span class="flex items-center gap-2 text-xs text-gray-400">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse inline-block"></span>
                    Live
                </span>
            </div>

            {{-- Graph image --}}
            <img
                id="graph-img-{{ $gid }}"
                src="/zabbix-graph?graphid={{ $gid }}&width=900&height=200&ts={{ time() }}"
                alt="{{ $graph['name'] }}"
                class="w-full rounded-lg"
                crossorigin="anonymous"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <p class="text-gray-400 text-sm hidden">Graph failed to load.</p>

            {{-- ===== PENJELASAN + AI ANALYZE ===== --}}
            <div class="mt-5 rounded-xl border {{ $colors['border'] }} {{ $colors['bg'] }} p-5">

                <div class="flex items-center justify-between mb-4">
                    <span class="font-semibold text-sm {{ $colors['head'] }}">
                        Tentang Grafik: {{ $info['title'] }}
                    </span>

                    {{-- AI Analyze Button --}}
                    <button
                        onclick="analyzeGraph('{{ $gid }}', '{{ addslashes($graph['name']) }}', '{{ $host['nama'] }}')"
                        id="btn-analyze-{{ $gid }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white
                               {{ $colors['btn'] }} rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Analyze with AI
                    </button>
                </div>

                {{-- Static explanation --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $colors['dot'] }} shrink-0"></span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Apa yang diukur</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed pl-3.5">{{ $info['what'] }}</p>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Kondisi Normal</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed pl-3.5">{{ $info['normal'] }}</p>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Yang Perlu Diwaspadai</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed pl-3.5">{{ $info['warn'] }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-yellow-400 shrink-0"></span>
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tips Tindakan</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed pl-3.5">{{ $info['tips'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- AI Analysis Result (hidden by default) --}}
                <div id="ai-result-{{ $gid }}" class="hidden">
                    <div class="border-t {{ $colors['border'] }} pt-4 mt-2">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 {{ $colors['head'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span class="text-xs font-semibold {{ $colors['head'] }} uppercase tracking-wide">AI Analysis</span>
                            <span class="text-xs text-gray-400" id="ai-timestamp-{{ $gid }}"></span>
                        </div>
                        <div id="ai-text-{{ $gid }}"
                             class="text-xs text-gray-700 leading-relaxed whitespace-pre-wrap bg-white bg-opacity-60 rounded-lg p-3">
                        </div>
                    </div>
                </div>

                {{-- AI Loading state --}}
                <div id="ai-loading-{{ $gid }}" class="hidden border-t {{ $colors['border'] }} pt-4 mt-2">
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <svg class="w-4 h-4 animate-spin {{ $colors['head'] }}" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Menganalisis grafik dengan AI...
                    </div>
                </div>

            </div>
            {{-- ===== END ===== --}}

        </div>
        @endforeach
    </div>

@else

    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        No graphs available for this device.
    </div>

@endif

<!-- ================= SCRIPT ================= -->
<script>
const REFRESH_INTERVAL = 60000;

function refreshGraphs() {
    document.querySelectorAll('img[id^="graph-img-"]').forEach(img => {
        const url = new URL(img.src, window.location.origin);
        url.searchParams.set('ts', Date.now());
        img.src = url.toString();
    });
}

setInterval(refreshGraphs, REFRESH_INTERVAL);

// ── AI Analyze ────────────────────────────────────────────────
async function analyzeGraph(graphId, graphName, hostName) {
    const btn       = document.getElementById('btn-analyze-' + graphId);
    const loading   = document.getElementById('ai-loading-' + graphId);
    const result    = document.getElementById('ai-result-' + graphId);
    const textEl    = document.getElementById('ai-text-' + graphId);
    const tsEl      = document.getElementById('ai-timestamp-' + graphId);
    const imgEl     = document.getElementById('graph-img-' + graphId);

    // Show loading
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
    </svg> Analyzing...`;
    loading.classList.remove('hidden');
    result.classList.add('hidden');

    try {
        // Step 1: Fetch image dari proxy → blob → base64
        const imgUrl = `/zabbix-graph?graphid=${graphId}&width=900&height=200&ts=${Date.now()}`;
        const imgRes = await fetch(imgUrl);

        if (!imgRes.ok) throw new Error('Gagal mengambil gambar grafik');

        const blob      = await imgRes.blob();
        const base64    = await blobToBase64(blob);
        const mediaType = blob.type || 'image/png';

        // Step 2: Kirim ke Claude API
        const response = await fetch('https://api.anthropic.com/v1/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model: 'claude-sonnet-4-20250514',
                max_tokens: 1000,
                messages: [{
                    role: 'user',
                    content: [
                        {
                            type: 'image',
                            source: {
                                type: 'base64',
                                media_type: mediaType,
                                data: base64,
                            }
                        },
                        {
                            type: 'text',
                            text: `Ini adalah grafik monitoring Zabbix untuk device "${hostName}", grafik "${graphName}".

Tolong analisis grafik ini dan berikan:
1. **Kondisi saat ini** — apa yang terlihat dari data di grafik?
2. **Tren** — apakah naik, turun, stabil, atau fluktuatif?
3. **Anomali** — adakah spike, drop, atau pola tidak normal yang terlihat?
4. **Kesimpulan** — apakah kondisi ini normal, perlu diperhatikan, atau kritis?
5. **Rekomendasi** — tindakan apa yang disarankan berdasarkan kondisi grafik ini?

Jawab dalam Bahasa Indonesia, ringkas dan langsung ke poin. Gunakan format yang mudah dibaca.`
                        }
                    ]
                }]
            })
        });

        if (!response.ok) {
            const err = await response.json();
            throw new Error(err.error?.message || 'Claude API error');
        }

        const data     = await response.json();
        const analysis = data.content?.[0]?.text || 'Tidak ada hasil analisis.';

        // Show result
        textEl.textContent = analysis;
        tsEl.textContent   = '— ' + new Date().toLocaleTimeString('id-ID');
        loading.classList.add('hidden');
        result.classList.remove('hidden');

    } catch (err) {
        loading.classList.add('hidden');
        result.classList.remove('hidden');
        textEl.textContent = '⚠️ Gagal menganalisis: ' + err.message;
        tsEl.textContent   = '';
    }

    // Reset button
    btn.disabled = false;
    btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
    </svg> Analyze with AI`;
}

function blobToBase64(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result.split(',')[1]);
        reader.onerror   = reject;
        reader.readAsDataURL(blob);
    });
}
</script>

@endsection