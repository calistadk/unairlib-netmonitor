@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Overview
</h2>

<!-- ================= SUMMARY CARD ================= -->
<div class="grid grid-cols-3 gap-6 mb-10">

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

                    <td class="p-4 whitespace-nowrap text-gray-600">
                        {{ $p['time'] }}
                    </td>

                    <td class="p-4 text-blue-600 font-medium">
                        {{ $p['host'] }}
                    </td>

                    <td class="p-4">
                        <span class="{{ $p['color'] }} px-3 py-2 rounded inline-block text-gray-800">
                            {{ $p['name'] }}
                        </span>
                    </td>

                    <td class="p-4 whitespace-nowrap text-gray-600">
                        {{ $p['duration'] }}
                    </td>

                    <td class="p-4 text-xs text-gray-500 whitespace-nowrap">
                        {{ $p['tags'] }}
                    </td>

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

@endsection