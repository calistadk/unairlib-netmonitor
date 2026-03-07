<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    // Tampilkan semua device
    public function index(Request $request)
    {
        $query = Device::query();

        if ($request->search) {
            $query->where('device_id', 'like', '%'.$request->search.'%')
                  ->orWhere('ip_address', 'like', '%'.$request->search.'%')
                  ->orWhere('serial_number', 'like', '%'.$request->search.'%')
                  ->orWhere('brand_model', 'like', '%'.$request->search.'%');
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $devices = $query->latest()->get();

        return view('perangkat', compact('devices'));
    }

    // Form tambah device
    public function create()
    {
        return view('tambahperangkat');
    }

    // Simpan device baru
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|unique:devices,device_id',
            'type'      => 'required',
        ]);

        $device = Device::create($request->all());

        // Catat ke activity log
        ActivityLog::create([
            'device_id' => $device->id,
            'user_id'   => auth()->id(),
            'type'      => 'Perubahan Data',
            'detail'    => 'Perangkat baru ditambahkan: ' . $device->device_id,
        ]);

        return redirect('/perangkat')->with('success', 'Perangkat berhasil ditambahkan');
    }

    // Form edit device
    public function edit($id)
    {
        $device = Device::findOrFail($id);
        return view('editperangkat', compact('device'));
    }

    // Update device
    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $old    = $device->toArray();

        $device->update($request->all());

        // Catat perubahan lokasi
        if ($old['location'] !== $request->location) {
            ActivityLog::create([
                'device_id'       => $device->id,
                'user_id'         => auth()->id(),
                'type'            => 'Perpindahan Lokasi',
                'detail'          => 'Lokasi perangkat ' . $device->device_id . ' dipindahkan',
                'location_before' => $old['location'],
                'location_after'  => $request->location,
            ]);
        } else {
            ActivityLog::create([
                'device_id' => $device->id,
                'user_id'   => auth()->id(),
                'type'      => 'Perubahan Data',
                'detail'    => 'Data perangkat ' . $device->device_id . ' diperbarui',
            ]);
        }

        return redirect('/perangkat')->with('success', 'Perangkat berhasil diperbarui');
    }

    // Hapus device
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return redirect('/perangkat')->with('success', 'Perangkat berhasil dihapus');
    }
}