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
                    <th class="px-6 py-4 text-left">Name</th>
                    <th class="px-6 py-4 text-left">Interface</th>
                    <th class="px-6 py-4 text-left">Availability</th>
                    <th class="px-6 py-4 text-left">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @foreach ($perangkat as $item)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 text-gray-700">{{ $item['id'] }}</td>

                    <td class="px-6 py-4 text-gray-800 font-medium">{{ $item['nama'] }}</td>

                    <td class="px-6 py-4">
                        <span class="text-gray-700 font-mono text-xs">{{ $item['interface'] }}</span>
                    </td>

                    <td class="px-6 py-4">
                        @php
                            $badgeColor = match($item['status']) {
                                'Online'  => 'bg-green-500',
                                'Offline' => 'bg-red-500',
                                default   => 'bg-yellow-400',
                            };
                        @endphp
                        <span class="text-white text-xs font-bold px-2 py-1 rounded {{ $badgeColor }}">
                            {{ $item['iface_type'] }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        <a href="/monitoring/{{ $item['id'] }}"
                           class="bg-[#1a1a2e] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#243B7C] transition inline-block">
                            Detail
                        </a>
                    </td>

                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>

@endsection