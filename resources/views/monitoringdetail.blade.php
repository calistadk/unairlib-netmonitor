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

    <div class="space-y-6">
        @foreach ($host['graphs'] as $graph)
        <div class="bg-white rounded-xl shadow-sm p-6">

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#243B7C]">
                    {{ $graph['name'] }}
                </h3>
                <span class="flex items-center gap-2 text-xs text-gray-400">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse inline-block"></span>
                    Live
                </span>
            </div>

            <img
                id="graph-img-{{ $graph['graphid'] }}"
                src="/zabbix-graph?graphid={{ $graph['graphid'] }}&width=900&height=200&ts={{ time() }}"
                alt="{{ $graph['name'] }}"
                class="w-full rounded-lg"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <p class="text-gray-400 text-sm hidden">Graph failed to load.</p>

        </div>
        @endforeach
    </div>

@else

    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        No graphs available for this device.
    </div>

@endif

<!-- ================= AUTO REFRESH SCRIPT ================= -->
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