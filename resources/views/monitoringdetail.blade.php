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
    /**
     * Fungsi deteksi jenis grafik berdasarkan nama.
     * Return array berisi:
     *   - title : judul penjelasan
     *   - what  : penjelasan apa yang diukur grafik ini
     *   - normal: kondisi normal yang diharapkan
     *   - warn  : tanda-tanda yang perlu diwaspadai
     *   - tips  : saran tindakan jika ada masalah
     *   - color : warna tema kartu (Tailwind class)
     */
    function getGraphInfo(string $graphName): array {
        $name = strtolower($graphName);

        // ── CPU ──────────────────────────────────────────────
        if (str_contains($name, 'cpu utilization') || str_contains($name, 'cpu usage')) {
            return [
                'title'  => 'CPU Utilization',
                'what'   => 'Grafik ini menampilkan persentase penggunaan CPU dari waktu ke waktu. Nilai 100% berarti processor bekerja pada kapasitas penuh.',
                'normal' => 'Penggunaan CPU yang sehat berada di bawah 70–80% secara konsisten. Lonjakan sesaat masih wajar saat ada proses besar.',
                'warn'   => 'Waspadai jika CPU terus-menerus di atas 90% dalam waktu lama — ini dapat menyebabkan respons sistem melambat atau layanan tidak responsif.',
                'tips'   => 'Identifikasi proses penyebab beban tinggi menggunakan `top` atau `htop`. Pertimbangkan scaling atau optimasi aplikasi jika beban konsisten tinggi.',
                'color'  => 'blue',
            ];
        }

        if (str_contains($name, 'cpu jumps')) {
            return [
                'title'  => 'CPU Jumps',
                'what'   => 'Grafik ini menampilkan lonjakan tiba-tiba pada penggunaan CPU (CPU spikes). Mengukur seberapa sering dan seberapa besar CPU mengalami lonjakan beban.',
                'normal' => 'Lonjakan kecil dan singkat adalah normal, misalnya saat ada cron job atau backup. Grafik seharusnya terlihat datar dengan sesekali spike kecil.',
                'warn'   => 'Lonjakan yang sering dan tinggi secara berulang bisa menandakan adanya proses yang tidak efisien, serangan, atau resource leak.',
                'tips'   => 'Periksa scheduled task dan log sistem saat spike terjadi. Gunakan profiling tool untuk menemukan bottleneck pada aplikasi.',
                'color'  => 'yellow',
            ];
        }

        if (str_contains($name, 'load average')) {
            return [
                'title'  => 'Load Average',
                'what'   => 'Load average mengukur rata-rata jumlah proses yang sedang berjalan atau menunggu CPU dalam rentang waktu 1, 5, dan 15 menit terakhir.',
                'normal' => 'Nilai load average yang sehat idealnya tidak melebihi jumlah core CPU yang tersedia. Misalnya, server 4-core sebaiknya di bawah 4.0.',
                'warn'   => 'Load average yang melebihi jumlah CPU core secara konsisten menandakan antrian proses menumpuk dan sistem mulai kewalahan.',
                'tips'   => 'Cek jumlah core dengan `nproc`. Jika load terus tinggi, kurangi concurrent process atau tambah kapasitas server.',
                'color'  => 'purple',
            ];
        }

        // ── Memory ───────────────────────────────────────────
        if (str_contains($name, 'memory utilization') || str_contains($name, 'memory usage')) {
            return [
                'title'  => 'Memory Utilization',
                'what'   => 'Grafik ini menampilkan penggunaan RAM (memory) dari total yang tersedia. Mencakup memory yang dipakai oleh aplikasi, cache, dan buffer sistem.',
                'normal' => 'Penggunaan memory 60–80% masih dalam batas wajar. Linux secara aktif menggunakan sisa memory untuk cache, sehingga angka tinggi tidak selalu berarti masalah.',
                'warn'   => 'Waspadai jika memory usage mendekati 95–100% secara konsisten, terutama jika diikuti peningkatan swap usage. Ini dapat menyebabkan OOM killer aktif dan proses mati tiba-tiba.',
                'tips'   => 'Gunakan `free -h` untuk melihat detail memory. Cek proses yang memakai memory terbesar dengan `ps aux --sort=-%mem`. Pertimbangkan menambah RAM atau mengoptimasi aplikasi.',
                'color'  => 'green',
            ];
        }

        // ── Network ──────────────────────────────────────────
        if (str_contains($name, 'network traffic') || str_contains($name, 'network')) {
            return [
                'title'  => 'Network Traffic',
                'what'   => 'Grafik ini menampilkan lalu lintas jaringan (inbound dan outbound) yang melewati interface network device. Biasanya diukur dalam bits per second (bps) atau bytes per second.',
                'normal' => 'Traffic yang stabil dan sesuai dengan pola penggunaan normal adalah tanda jaringan sehat. Fluktuasi wajar terjadi di jam-jam sibuk.',
                'warn'   => 'Lonjakan traffic yang tidak wajar bisa mengindikasikan serangan DDoS, transfer data besar yang tidak terduga, atau misconfiguration routing.',
                'tips'   => 'Monitor dengan `iftop` atau `nethogs` untuk melihat sumber traffic. Cek firewall dan access log jika ada anomali mendadak.',
                'color'  => 'teal',
            ];
        }

        // ── Disk ─────────────────────────────────────────────
        if (str_contains($name, 'disk') && str_contains($name, 'io')) {
            return [
                'title'  => 'Disk I/O',
                'what'   => 'Grafik ini mengukur aktivitas baca (read) dan tulis (write) pada storage/disk. Tingginya I/O menunjukkan banyak operasi baca/tulis sedang terjadi.',
                'normal' => 'I/O yang stabil dan tidak terus-menerus tinggi menandakan sistem storage bekerja normal. Read biasanya lebih banyak dari write pada server web.',
                'warn'   => 'I/O yang terus tinggi (I/O wait tinggi) bisa memperlambat seluruh sistem karena CPU harus menunggu operasi disk selesai.',
                'tips'   => 'Gunakan `iostat -x` untuk analisis mendalam. Pertimbangkan upgrade ke SSD atau tambah IOPS jika bottleneck ada di storage.',
                'color'  => 'orange',
            ];
        }

        if (str_contains($name, 'disk space') || str_contains($name, 'disk usage')) {
            return [
                'title'  => 'Disk Space',
                'what'   => 'Grafik ini memantau kapasitas ruang penyimpanan yang terpakai pada filesystem. Menampilkan tren penggunaan disk dari waktu ke waktu.',
                'normal' => 'Idealnya penggunaan disk di bawah 80%. Tren kenaikan yang lambat dan konsisten adalah normal untuk server produksi.',
                'warn'   => 'Disk di atas 90% sangat berbahaya — sistem bisa tidak bisa menulis log, database corrupt, atau aplikasi crash. Disk penuh bisa terjadi tiba-tiba dari log yang menumpuk.',
                'tips'   => 'Gunakan `df -h` untuk cek semua partisi dan `du -sh /*` untuk cari folder terbesar. Bersihkan log lama atau tambah kapasitas storage.',
                'color'  => 'red',
            ];
        }

        // ── Swap ─────────────────────────────────────────────
        if (str_contains($name, 'swap')) {
            return [
                'title'  => 'Swap Usage',
                'what'   => 'Swap adalah ruang disk yang digunakan sebagai "memory cadangan" ketika RAM fisik habis. Penggunaan swap yang tinggi menandakan RAM tidak mencukupi.',
                'normal' => 'Swap yang ideal hampir tidak terpakai (0–5%). Sedikit penggunaan swap sesekali masih bisa diterima.',
                'warn'   => 'Swap usage yang terus meningkat atau tinggi secara konsisten adalah tanda serius bahwa RAM sudah tidak mencukupi. Performa sistem akan sangat menurun.',
                'tips'   => 'Tambah RAM fisik adalah solusi terbaik. Sementara itu, identifikasi dan restart proses yang bocor memory (memory leak).',
                'color'  => 'pink',
            ];
        }

        // ── Temperature ──────────────────────────────────────
        if (str_contains($name, 'temperature') || str_contains($name, 'temp')) {
            return [
                'title'  => 'Temperature',
                'what'   => 'Grafik ini memantau suhu komponen hardware seperti CPU, hard disk, atau sensor lainnya. Suhu tinggi dapat merusak hardware secara permanen.',
                'normal' => 'Suhu CPU normal berkisar 40–70°C saat beban normal. Hard disk sebaiknya di bawah 45°C.',
                'warn'   => 'Suhu CPU di atas 85°C atau hard disk di atas 55°C adalah tanda bahaya. Sistem akan melakukan throttling atau shutdown otomatis untuk mencegah kerusakan.',
                'tips'   => 'Periksa sirkulasi udara di ruang server, bersihkan debu dari heatsink dan kipas, pastikan pendingin ruangan bekerja optimal.',
                'color'  => 'red',
            ];
        }

        // ── Interface / Ping ─────────────────────────────────
        if (str_contains($name, 'icmp') || str_contains($name, 'ping')) {
            return [
                'title'  => 'ICMP / Ping',
                'what'   => 'Grafik ini menampilkan waktu respons ping (latency) ke device. Mengukur seberapa cepat device merespons permintaan ICMP echo.',
                'normal' => 'Latency di bawah 10ms untuk perangkat dalam jaringan lokal adalah normal. Untuk perangkat remote, di bawah 50ms masih dianggap baik.',
                'warn'   => 'Latency tinggi atau packet loss menandakan masalah koneksi jaringan, overload pada device, atau masalah routing.',
                'tips'   => 'Gunakan `traceroute` untuk menemukan hop mana yang menyebabkan latency tinggi. Cek kondisi fisik kabel atau konfigurasi switch.',
                'color'  => 'cyan',
            ];
        }

        // ── Uptime ───────────────────────────────────────────
        if (str_contains($name, 'uptime') || str_contains($name, 'availability')) {
            return [
                'title'  => 'Uptime / Availability',
                'what'   => 'Grafik ini menampilkan berapa lama device telah berjalan tanpa restart, atau persentase waktu device dalam keadaan online.',
                'normal' => 'Uptime yang tinggi (99%+) menandakan sistem stabil. Server produksi idealnya memiliki uptime berbulan-bulan.',
                'warn'   => 'Uptime yang sering reset atau availability di bawah 99% menandakan ada masalah stabilitas — bisa dari hardware, software, atau power.',
                'tips'   => 'Periksa log sistem (`journalctl` atau `/var/log/syslog`) untuk mengetahui penyebab restart. Pastikan UPS berfungsi jika masalah dari power.',
                'color'  => 'green',
            ];
        }

        // ── Fallback / Generic ───────────────────────────────
        return [
            'title'  => 'Grafik Monitoring',
            'what'   => 'Grafik ini menampilkan metrik monitoring untuk device ' . $GLOBALS['hostName'] . '. Data diperbarui secara real-time dari Zabbix setiap menit.',
            'normal' => 'Pantau tren dari waktu ke waktu untuk memahami pola normal device ini. Tren yang stabil biasanya menandakan kondisi sistem yang sehat.',
            'warn'   => 'Perhatikan lonjakan atau penurunan mendadak yang tidak sesuai pola normal — ini bisa menjadi indikator awal masalah.',
            'tips'   => 'Konfigurasikan trigger di Zabbix untuk mendapatkan notifikasi otomatis jika nilai melampaui threshold yang telah ditentukan.',
            'color'  => 'gray',
        ];
    }

    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',  'badge' => 'bg-blue-100 text-blue-800',   'icon' => 'text-blue-500',  'head' => 'text-blue-700',  'dot' => 'bg-blue-400'],
        'yellow' => ['bg' => 'bg-yellow-50',  'border' => 'border-yellow-200','badge' => 'bg-yellow-100 text-yellow-800','icon' => 'text-yellow-500','head' => 'text-yellow-700','dot' => 'bg-yellow-400'],
        'purple' => ['bg' => 'bg-purple-50',  'border' => 'border-purple-200','badge' => 'bg-purple-100 text-purple-800','icon' => 'text-purple-500','head' => 'text-purple-700','dot' => 'bg-purple-400'],
        'green'  => ['bg' => 'bg-green-50',   'border' => 'border-green-200', 'badge' => 'bg-green-100 text-green-800',  'icon' => 'text-green-500', 'head' => 'text-green-700', 'dot' => 'bg-green-400'],
        'teal'   => ['bg' => 'bg-teal-50',    'border' => 'border-teal-200',  'badge' => 'bg-teal-100 text-teal-800',   'icon' => 'text-teal-500',  'head' => 'text-teal-700',  'dot' => 'bg-teal-400'],
        'orange' => ['bg' => 'bg-orange-50',  'border' => 'border-orange-200','badge' => 'bg-orange-100 text-orange-800','icon' => 'text-orange-500','head' => 'text-orange-700','dot' => 'bg-orange-400'],
        'red'    => ['bg' => 'bg-red-50',     'border' => 'border-red-200',   'badge' => 'bg-red-100 text-red-800',     'icon' => 'text-red-500',   'head' => 'text-red-700',   'dot' => 'bg-red-400'],
        'pink'   => ['bg' => 'bg-pink-50',    'border' => 'border-pink-200',  'badge' => 'bg-pink-100 text-pink-800',   'icon' => 'text-pink-500',  'head' => 'text-pink-700',  'dot' => 'bg-pink-400'],
        'cyan'   => ['bg' => 'bg-cyan-50',    'border' => 'border-cyan-200',  'badge' => 'bg-cyan-100 text-cyan-800',   'icon' => 'text-cyan-500',  'head' => 'text-cyan-700',  'dot' => 'bg-cyan-400'],
        'gray'   => ['bg' => 'bg-gray-50',    'border' => 'border-gray-200',  'badge' => 'bg-gray-100 text-gray-700',   'icon' => 'text-gray-400',  'head' => 'text-gray-700',  'dot' => 'bg-gray-400'],
    ];

    // Supaya getGraphInfo() bisa akses nama host di fallback
    $GLOBALS['hostName'] = $host['nama'];
    @endphp

    <div class="space-y-6">
        @foreach ($host['graphs'] as $graph)
        @php
            $info   = getGraphInfo($graph['name']);
            $colors = $colorMap[$info['color']] ?? $colorMap['gray'];
        @endphp

        <div class="bg-white rounded-xl shadow-sm p-6">

            {{-- Graph header --}}
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#243B7C]">
                    {{ $graph['name'] }}
                </h3>
                <span class="flex items-center gap-2 text-xs text-gray-400">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse inline-block"></span>
                    Live
                </span>
            </div>

            {{-- Graph image --}}
            <img
                id="graph-img-{{ $graph['graphid'] }}"
                src="/zabbix-graph?graphid={{ $graph['graphid'] }}&width=900&height=200&ts={{ time() }}"
                alt="{{ $graph['name'] }}"
                class="w-full rounded-lg"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <p class="text-gray-400 text-sm hidden">Graph failed to load.</p>

            {{-- ===== PENJELASAN GRAFIK ===== --}}
            <div class="mt-5 rounded-xl border {{ $colors['border'] }} {{ $colors['bg'] }} p-5">

                {{-- Judul penjelasan --}}
                <div class="flex items-center gap-2 mb-4">
                    <span class="font-semibold text-sm {{ $colors['head'] }}">
                        Tentang Grafik: {{ $info['title'] }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    {{-- Apa yang diukur --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $colors['dot'] }} shrink-0"></span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Apa yang diukur</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed pl-3.5">
                            {{ $info['what'] }}
                        </p>
                    </div>

                    {{-- Kondisi Normal --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Kondisi Normal</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed pl-3.5">
                            {{ $info['normal'] }}
                        </p>
                    </div>

                    {{-- Yang perlu diwaspadai + tips --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Yang Perlu Diwaspadai</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed pl-3.5">
                                {{ $info['warn'] }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-yellow-400 shrink-0"></span>
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tips Tindakan</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed pl-3.5">
                                {{ $info['tips'] }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>
            {{-- ===== END PENJELASAN ===== --}}

        </div>
        @endforeach
    </div>

@else

    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        No graphs available for this device.
    </div>

@endif

<!-- ================= AUTO REFRESH ================= -->
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
</script>

@endsection