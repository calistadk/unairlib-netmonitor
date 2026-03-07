<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Device;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['device', 'user'])->latest();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('detail', 'like', '%'.$search.'%')
                  ->orWhere('type', 'like', '%'.$search.'%')
                  ->orWhereHas('device', function ($d) use ($search) {
                      $d->where('device_id', 'like', '%'.$search.'%');
                  });
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->date) {
            $query->whereDate('created_at', \Carbon\Carbon::createFromFormat('d-m-Y', $request->date));
        }

        $logs = $query->get();

        // Tanggal unik untuk dropdown filter
        $dates = ActivityLog::selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('d-m-Y'))
            ->toArray();

        // Daftar device untuk form tambah log
        $devices = Device::orderBy('device_id')->get();

        return view('log', compact('logs', 'dates', 'devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'type'      => 'required',
            'detail'    => 'required|string|max:1000',
        ]);

        ActivityLog::create([
            'device_id'       => $request->device_id,
            'user_id'         => auth()->id(),
            'type'            => $request->type,
            'detail'          => $request->detail,
            'location_before' => $request->location_before,
            'location_after'  => $request->location_after,
        ]);

        return redirect()->route('log.index')->with('success', 'Log berhasil ditambahkan');
    }
}