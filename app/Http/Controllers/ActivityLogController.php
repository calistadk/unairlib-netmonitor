<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Device;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // ── Log manual dari activity_logs ──
        $logs = ActivityLog::with(['device', 'user'])->get()
            ->map(fn($log) => [
                'time'        => $log->created_at,
                'device_name' => $log->device->device_id ?? '-',
                'type'        => $log->type,
                'detail'      => $log->detail,
                'user'        => $log->user->name ?? 'System',
            ]);

        // ── Log otomatis dari maintenance_schedules ──
        $maintLogs = MaintenanceSchedule::with('doneBy')
            ->whereNotNull('done_at')
            ->get()
            ->map(fn($m) => [
                'time'        => $m->done_at,
                'device_name' => $m->device_name,
                'type'        => 'Maintenance',
                'detail'      => $m->notes ?? 'Perangkat telah dilakukan maintenance.',
                'user'        => $m->doneBy->name ?? 'System',
            ]);

        // ── Gabungkan & urutkan terbaru di atas ──
        $merged = $logs->concat($maintLogs)->sortByDesc('time')->values();

        // ── Filter search ──
        if ($request->search) {
            $search = strtolower($request->search);
            $merged = $merged->filter(fn($l) =>
                str_contains(strtolower($l['device_name']), $search) ||
                str_contains(strtolower($l['type']), $search) ||
                str_contains(strtolower($l['detail']), $search)
            )->values();
        }

        // ── Filter type ──
        if ($request->type) {
            $merged = $merged->filter(fn($l) => $l['type'] === $request->type)->values();
        }

        // ── Filter date ──
        if ($request->date) {
            $merged = $merged->filter(fn($l) =>
                \Carbon\Carbon::parse($l['time'])->format('d-m-Y') === $request->date
            )->values();
        }

        // ── Dropdown tanggal unik ──
        $dates = $merged->map(fn($l) =>
            \Carbon\Carbon::parse($l['time'])->format('d-m-Y')
        )->unique()->values()->toArray();

        $devices = Device::orderBy('device_id')->get();

        return view('log', compact('merged', 'dates', 'devices'));
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