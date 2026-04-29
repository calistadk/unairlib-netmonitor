<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BrokenDevice;

class BrokenDeviceController extends Controller
{
    /**
     * Simpan device rusak.
     * Dipanggil dari halaman Maintenance (tombol "Rusak") ATAU
     * dari halaman Perangkat (tombol "Tandai Rusak") ATAU
     * dari modal "Tambah Device Rusak" di tab Broken Devices.
     */
    public function store(Request $request)
    {
        $request->validate([
            'host_name'   => 'required|string|max:255',
            'reason'      => 'required|string|max:500',
            'broken_date' => 'required|date',
        ]);

        // Jika hostid kosong (device manual / tidak dari Zabbix),
        // buat identifier unik agar tidak bentrok dengan Zabbix hostid
        // dan tidak ikut difilter dari daftar Zabbix.
        $hostid = ($request->filled('hostid') && $request->hostid !== '')
            ? $request->hostid
            : 'manual-' . uniqid();

        BrokenDevice::updateOrCreate(
            ['hostid' => $hostid],
            [
                'host_name'   => $request->host_name,
                'ip'          => $request->ip ?? '',
                'groups'      => $request->groups ?? '',
                'reason'      => $request->reason,
                'broken_date' => $request->broken_date,
                'reported_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Device {$request->host_name} ditandai rusak.");
    }

    /**
     * Hapus dari daftar rusak (restore).
     * Setelah dihapus, device Zabbix otomatis muncul kembali
     * di Active Devices dan Maintenance karena filter hanya
     * berdasarkan tabel broken_devices.
     */
    public function destroy($id)
    {
        $broken = BrokenDevice::findOrFail($id);
        $name   = $broken->host_name;
        $broken->delete();

        return back()->with('success', "Device {$name} berhasil dipulihkan.");
    }
}