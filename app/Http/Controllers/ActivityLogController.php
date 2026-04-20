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
        // ── Resolve range preset ──────────────────────────────────
        $preset = $request->range_preset ?? 'all';
        $now    = \Carbon\Carbon::now();
        $from   = null;
        $to     = null;

        switch ($preset) {
            case 'today':
                $from  = $now->copy()->startOfDay();
                $to    = $now->copy()->endOfDay();
                $label = 'Today';
                break;
            case 'yesterday':
                $from  = $now->copy()->subDay()->startOfDay();
                $to    = $now->copy()->subDay()->endOfDay();
                $label = 'Yesterday';
                break;
            case 'this_week':
                $from  = $now->copy()->startOfWeek();
                $to    = $now->copy()->endOfWeek();
                $label = 'This Week';
                break;
            case 'prev_week':
                $from  = $now->copy()->subWeek()->startOfWeek();
                $to    = $now->copy()->subWeek()->endOfWeek();
                $label = 'Previous Week';
                break;
            case 'this_month':
                $from  = $now->copy()->startOfMonth();
                $to    = $now->copy()->endOfMonth();
                $label = 'This Month';
                break;
            case 'prev_month':
                $from  = $now->copy()->subMonth()->startOfMonth();
                $to    = $now->copy()->subMonth()->endOfMonth();
                $label = 'Previous Month';
                break;
            case 'last_7':
                $from  = $now->copy()->subDays(6)->startOfDay();
                $to    = $now->copy()->endOfDay();
                $label = 'Last 7 Days';
                break;
            case 'last_30':
                $from  = $now->copy()->subDays(29)->startOfDay();
                $to    = $now->copy()->endOfDay();
                $label = 'Last 30 Days';
                break;
            case 'last_3months':
                $from  = $now->copy()->subMonths(3)->startOfDay();
                $to    = $now->copy()->endOfDay();
                $label = 'Last 3 Months';
                break;
            case 'last_6months':
                $from  = $now->copy()->subMonths(6)->startOfDay();
                $to    = $now->copy()->endOfDay();
                $label = 'Last 6 Months';
                break;
            case 'last_year':
                $from  = $now->copy()->subYear()->startOfDay();
                $to    = $now->copy()->endOfDay();
                $label = 'Last 1 Year';
                break;
            case 'custom':
                $from  = $request->range_from
                    ? \Carbon\Carbon::parse($request->range_from)->startOfDay()
                    : null;
                $to    = $request->range_to
                    ? \Carbon\Carbon::parse($request->range_to)->endOfDay()
                    : null;
                $label = ($from && $to)
                    ? $from->format('d M Y') . ' – ' . $to->format('d M Y')
                    : 'Custom Range';
                break;
            default:
                $preset = 'all';
                $label  = 'All Time';
        }

        $range = compact('preset', 'label', 'from', 'to');

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

        // ── Filter range tanggal ──
        if ($from || $to) {
            $merged = $merged->filter(function ($l) use ($from, $to) {
                $time = \Carbon\Carbon::parse($l['time']);
                if ($from && $time->lt($from)) return false;
                if ($to   && $time->gt($to))   return false;
                return true;
            })->values();
        }

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

        $devices = Device::orderBy('device_id')->get();

        return view('log', compact('merged', 'range', 'devices'));
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