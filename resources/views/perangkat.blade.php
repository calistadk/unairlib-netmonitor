@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-6">
    Device Data Management
</h2>

<!-- ================= SEARCH & FILTER ================= -->
<div class="flex items-center gap-4 mb-6">

    <!-- Search -->
    <input
        type="text"
        id="searchDevice"
        placeholder="Search ID, IP, Series, or Brand"
        onkeyup="filterDevice()"
        class="w-[480px] px-4 py-2 border border-gray-300 rounded-lg text-sm 
        focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
    />

    <!-- Filter Device -->
    <select
        id="filterDevice"
        onchange="filterDevice()"
        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm
        focus:outline-none focus:ring-2 focus:ring-blue-300 cursor-pointer">

        <option value="">All Devices</option>
        <option value="Router">Router</option>
        <option value="Server">Server</option>
        <option value="Switch">Switch</option>
        <option value="Wi-Fi">Wi-Fi</option>
        <option value="Desktop">Desktop</option>
        <option value="Laptop">Laptop</option>

    </select>

    <!-- Button -->
    <a href="{{ url('/tambah-perangkat') }}"
        class="ml-auto inline-block bg-blue-700 hover:bg-blue-800 
        text-white font-semibold px-6 py-2 rounded-lg">
        + Add Devices
    </a>

</div>

<!-- ================= TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">

<div class="overflow-x-auto">

<table class="w-full text-sm" id="deviceTable">

<thead class="text-[#243B7C] font-semibold border-b border-gray-200">
<tr>

<th class="px-6 py-4 text-left">ID</th>
<th class="px-6 py-4 text-left">Type</th>
<th class="px-6 py-4 text-left">Brand & Model</th>
<th class="px-6 py-4 text-left">Series</th>
<th class="px-6 py-4 text-left">IP</th>
<th class="px-6 py-4 text-left">MAC</th>
<th class="px-6 py-4 text-left">Location</th>
<th class="px-6 py-4 text-left">Purchase Date</th>
<th class="px-6 py-4 text-left">Warranty</th>
<th class="px-6 py-4 text-left">Action</th>

</tr>
</thead>

<tbody id="deviceBody" class="divide-y divide-gray-100">

<!-- ================= DEVICE DATA ================= -->

@php

$devices = [

[
'id'=>'RTR-01',
'type'=>'Router',
'brand'=>'Cisco ISR 4321',
'series'=>'FTX12345',
'ip'=>'192.168.1.1',
'mac'=>'AA:BB:CC',
'location'=>'MOVIO',
'purchase'=>'01-12-2020',
'warranty'=>'01-12-2026'
],

[
'id'=>'SRV-02',
'type'=>'Server',
'brand'=>'HP DL380',
'series'=>'HPX98765',
'ip'=>'192.168.1.10',
'mac'=>'DD:EE:FF',
'location'=>'LIBCAFE',
'purchase'=>'10-05-2019',
'warranty'=>'10-05-2025'
],

[
'id'=>'SWT-03',
'type'=>'Switch',
'brand'=>'Cisco Catalyst',
'series'=>'CATA-222',
'ip'=>'192.168.1.5',
'mac'=>'AA:22:11',
'location'=>'Ruang Server',
'purchase'=>'02-03-2021',
'warranty'=>'02-03-2027'
]

];

@endphp

@foreach ($devices as $device)

<tr class="hover:bg-gray-50 transition device-row"
data-type="{{ $device['type'] }}"
data-search="{{ strtolower($device['id'].' '.$device['brand'].' '.$device['series'].' '.$device['ip']) }}">

<td class="px-6 py-4">{{ $device['id'] }}</td>
<td class="px-6 py-4">{{ $device['type'] }}</td>
<td class="px-6 py-4">{{ $device['brand'] }}</td>
<td class="px-6 py-4">{{ $device['series'] }}</td>
<td class="px-6 py-4">{{ $device['ip'] }}</td>
<td class="px-6 py-4">{{ $device['mac'] }}</td>
<td class="px-6 py-4">{{ $device['location'] }}</td>
<td class="px-6 py-4">{{ $device['purchase'] }}</td>
<td class="px-6 py-4">{{ $device['warranty'] }}</td>

<td class="px-6 py-4 flex gap-3">

<!-- EDIT -->
<button class="text-gray-600 hover:text-gray-800">

<svg xmlns="http://www.w3.org/2000/svg"
class="w-5 h-5"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.939a4.5 4.5 0 01-1.897 1.13l-2.685.805.805-2.685a4.5 4.5 0 011.13-1.897L16.862 4.487z"/>

</svg>

</button>

<!-- DELETE -->

<button class="text-red-600 hover:text-red-800">

<svg xmlns="http://www.w3.org/2000/svg"
class="w-5 h-5"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3-3h4a1 1 0 011 1v1H9V5a1 1 0 011-1z"/>

</svg>

</button>

</td>

</tr>

@endforeach

</tbody>

</table>

<!-- EMPTY STATE -->

<div id="emptyDevice"
class="hidden py-12 text-center text-gray-400 text-sm">

No device found.

</div>

</div>
</div>

<!-- ================= FILTER SCRIPT ================= -->

<script>

function filterDevice(){

const search = document.getElementById('searchDevice').value.toLowerCase()
const type = document.getElementById('filterDevice').value

const rows = document.querySelectorAll('.device-row')

let visible = 0

rows.forEach(row => {

const matchSearch = row.dataset.search.includes(search)
const matchType = type === '' || row.dataset.type === type

if(matchSearch && matchType){

row.classList.remove('hidden')
visible++

}else{

row.classList.add('hidden')

}

})

document.getElementById('emptyDevice')
.classList.toggle('hidden', visible > 0)

}

</script>

@endsection