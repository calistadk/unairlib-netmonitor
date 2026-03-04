@extends('layouts.app')

@section('content')

<!-- ================= TITLE ================= -->
<h2 class="text-3xl font-bold text-[#243B7C] mb-8">
    Manajemen Data Perangkat
</h2>

<!-- ================= FILTER & ACTION ================= -->
<div class="flex flex-wrap items-center gap-4 mb-8">

    <input
        type="text"
        placeholder="Cari ID, IP, Serial, atau Merek..."
        class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    >

    <select class="px-4 py-2 border border-gray-300 rounded-lg">
        <option>Semua Jenis</option>
        <option>Wi-Fi</option>
        <option>Router</option>
        <option>Hub</option>
        <option>Server</option>
        <option>Desktop</option>
        <option>Laptop</option>
    </select>

    <select class="px-4 py-2 border border-gray-300 rounded-lg">
        <option>Semua Status</option>
        <option>Tersedia</option>
        <option>Tidak Tersedia</option>
        <option>Maintenance</option>
        <option>Cadangan</option>
    </select>

    <a href="{{ url('/tambah-perangkat') }}"
        class="ml-auto inline-block bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-2 rounded-lg">
        + Tambah Perangkat
    </a>
</div>

<!-- ================= TABLE ================= -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-4 text-left">ID</th>
                    <th class="p-4 text-left">Jenis</th>
                    <th class="p-4 text-left">Merek & Model</th>
                    <th class="p-4 text-left">Serial</th>
                    <th class="p-4 text-left">IP</th>
                    <th class="p-4 text-left">MAC</th>
                    <th class="p-4 text-left">Lokasi</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Tanggal Pembelian</th>
                    <th class="p-4 text-left">Garansi</th>
                    <th class="p-4 text-left"></th>
                </tr>
            </thead>

            <tbody>

                <!-- ROW 1 -->
                <tr class="border-t">
                    <td class="p-4">RTR-01</td>
                    <td class="p-4">Router</td>
                    <td class="p-4">Cisco ISR 4321</td>
                    <td class="p-4">FTX12345</td>
                    <td class="p-4">192.168.1.1</td>
                    <td class="p-4">AA:BB:CC</td>
                    <td class="p-4">MOVIO</td>
                    <td class="p-4">
                        <span class="inline-flex items-center bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                            Tersedia
                        </span>
                    </td>
                    <td class="p-4">01-12-2020</td>
                    <td class="p-4">01-12-2026</td>
                    <td class="p-4 flex gap-3 text-gray-600">
                        <div class="flex items-center gap-3">
                            <!-- ================= EDIT ================= -->
                            <a href="{{ url('/edit-perangkat') }}"
                            class="text-gray-600 hover:text-gray-800"
                            title="Edit">

                                <!-- Heroicon: Pencil -->
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5"
                                    fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.939a4.5 4.5 0 01-1.897 1.13l-2.685.805.805-2.685a4.5 4.5 0 011.13-1.897L16.862 4.487z"/>
                                </svg>
                            </a>

                            <!-- ================= DELETE ================= -->
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800"
                                        title="Hapus">
                                    <!-- Heroicon: Trash -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3-3h4a1 1 0 011 1v1H9V5a1 1 0 011-1z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- ROW 2 -->
                <tr class="border-t">
                    <td class="p-4">SRV-02</td>
                    <td class="p-4">Server</td>
                    <td class="p-4">HP DL380</td>
                    <td class="p-4">HPX98765</td>
                    <td class="p-4">192.168.1.10</td>
                    <td class="p-4">DD:EE:FF</td>
                    <td class="p-4">LIBCAFE</td>
                    <td class="p-4">
                        <span class="inline-flex items-center bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                            Tidak Tersedia
                        </span>
                    </td>
                    <td class="p-4">10-05-2019</td>
                    <td class="p-4">10-05-2025</td>
                    <td class="p-4 flex gap-3 text-gray-600">
                        <div class="flex items-center gap-3">
                            <!-- ================= EDIT ================= -->
                                <button type="submit"
                                            class="text-grey-600 hover:text-grey-800"
                                            title="Edit">
                                <!-- Heroicon: Pencil -->
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5"
                                    fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.939a4.5 4.5 0 01-1.897 1.13l-2.685.805.805-2.685a4.5 4.5 0 011.13-1.897L16.862 4.487z"/>
                                </svg>
                                </button>

                            <!-- ================= DELETE ================= -->
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800"
                                        title="Hapus">
                                    <!-- Heroicon: Trash -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3-3h4a1 1 0 011 1v1H9V5a1 1 0 011-1z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection