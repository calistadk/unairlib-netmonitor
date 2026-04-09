<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Device;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    // ─────────────────────────────────────────────
    //  Resolve time range (sama dengan Maintenance)
    // ─────────────────────────────────────────────
    private function resolveTimeRange(Request $request): array
    {
        $tz     = config('app.timezone');
        $preset = $request->input('range_preset', 'all');

        if ($preset === 'custom') {
            $from = $request->filled('range_from')
                ? Carbon::parse($request->range_from, $tz)->startOfDay()
                : Carbon::now($tz)->subDays(7)->startOfDay();
            $to = $request->filled('range_to')
                ? Carbon::parse($request->range_to, $tz)->endOfDay()
                : Carbon::now($tz)->endOfDay();

            return [
                'from'   => $from,
                'to'     => $to,
                'preset' => 'custom',
                'label'  => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            ];
        }

        $now = Carbon::now($tz);

        [$from, $to, $label] = match($preset) {
            'today'        => [$now->copy()->startOfDay(),               $now->copy()->endOfDay(),              'Today'],
            'yesterday'    => [$now->copy()->subDay()->startOfDay(),     $now->copy()->subDay()->endOfDay(),    'Yesterday'],
            'last_7'       => [$now->copy()->subDays(7),                 $now->copy(),                         'Last 7 days'],
            'last_30'      => [$now->copy()->subDays(30),                $now->copy(),                         'Last 30 days'],
            'last_3months' => [$now->copy()->subMonths(3),               $now->copy(),                         'Last 3 months'],
            'last_6months' => [$now->copy()->subMonths(6),               $now->copy(),                         'Last 6 months'],
            'last_year'    => [$now->copy()->subYear(),                  $now->copy(),                         'Last 1 year'],
            'this_week'    => [$now->copy()->startOfWeek(),              $now->copy()->endOfWeek(),            'This week'],
            'this_month'   => [$now->copy()->startOfMonth(),             $now->copy()->endOfMonth(),           'This month'],
            'prev_week'    => [$now->copy()->subWeek()->startOfWeek(),   $now->copy()->subWeek()->endOfWeek(), 'Previous week'],
            'prev_month'   => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth(), 'Previous month'],
            default        => [null, null, 'All Time'],  // 'all'
        };

        return ['from' => $from, 'to' => $to, 'preset' => $preset, 'label' => $label];
    }

    // ─────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $range = $this->resolveTimeRange($request);

        // ── Log manual dari activity_logs ──
        $logQuery = ActivityLog::with(['device', 'user']);

        if ($range['from']) $logQuery->where('created_at', '>=', $range['from']);
        if ($range['to'])   $logQuery->where('created_at', '<=', $range['to']);

        $logs = $logQuery->get()->map(fn($log) => [
            'time'        => $log->created_at,
            'device_name' => $log->device->device_id ?? '-',
            'type'        => $log->type,
            'detail'      => $log->detail,
            'user'        => $log->user->name ?? 'System',
        ]);

        // ── Log otomatis dari maintenance_schedules ──
        $maintQuery = MaintenanceSchedule::with('doneBy')->whereNotNull('done_at');

        if ($range['from']) $maintQuery->where('done_at', '>=', $range['from']);
        if ($range['to'])   $maintQuery->where('done_at', '<=', $range['to']);

        $maintLogs = $maintQuery->get()->map(fn($m) => [
            'time'        => $m->done_at,
            'device_name' => $m->device_name,
            'type'        => 'Maintenance',
            'detail'      => $m->notes ?? 'Perangkat telah dilakukan maintenance.',
            'user'        => $m->doneBy->name ?? 'System',
        ]);

        // ── Gabungkan & urutkan terbaru di atas ──
        $merged = $logs->concat($maintLogs)->sortByDesc('time')->values();

        // ── Filter type ──
        if ($request->filled('type')) {
            $merged = $merged->filter(fn($l) => $l['type'] === $request->type)->values();
        }

        // ── Filter search ──
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $merged = $merged->filter(fn($l) =>
                str_contains(strtolower($l['device_name']), $search) ||
                str_contains(strtolower($l['type']), $search) ||
                str_contains(strtolower($l['detail']), $search)
            )->values();
        }

        $devices = Device::orderBy('device_id')->get();

        return view('log', compact('merged', 'devices', 'range'));
    }

    // ─────────────────────────────────────────────
    //  STORE
    // ─────────────────────────────────────────────
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