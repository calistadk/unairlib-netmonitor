<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// app/Http/Controllers/BrokenDeviceController.php
use App\Models\BrokenDevice;

class BrokenDeviceController extends Controller
{
    // Simpan device rusak (dari halaman device ATAU maintenance)
    public function store(Request $request)
    {
        $request->validate([
            'hostid'       => 'required|string',
            'host_name'    => 'required|string',
            'reason'       => 'required|string|max:500',
            'broken_date'  => 'required|date',
        ]);

        // Cegah duplikat
        BrokenDevice::updateOrCreate(
            ['hostid' => $request->hostid],
            [
                'host_name'   => $request->host_name,
                'ip'          => $request->ip ?? '',
                'groups'      => $request->groups ?? '',
                'reason'      => $request->reason,
                'broken_date' => $request->broken_date,
            ]
        );

        return back()->with('success', "Device {$request->host_name} ditandai rusak.");
    }

    // Hapus dari daftar rusak (restore)
    public function destroy($id)
    {
        BrokenDevice::findOrFail($id)->delete();
        return back()->with('success', 'Device dipulihkan dari daftar rusak.');
    }
}