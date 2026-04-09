<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\ActivityLog;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    private function getZabbixDevices(): array
    {
        try {
            $loginRes = Http::timeout(5)->post('http://210.57.222.125:8481/zabbix/api_jsonrpc.php', [
                "jsonrpc" => "2.0",
                "method"  => "user.login",
                "params"  => ["username" => "Admin", "password" => "zabbix"],
                "id"      => 1,
            ]);

            $auth = $loginRes->json()['result'] ?? null;
            if (!$auth) return [];

            $res = Http::timeout(5)->post('http://210.57.222.125:8481/zabbix/api_jsonrpc.php', [
                "jsonrpc" => "2.0",
                "method"  => "host.get",
                "params"  => [
                    "output"           => ["hostid", "host"],
                    "selectInterfaces" => ["ip", "available"],
                    "selectGroups"     => ["name"],
                ],
                "auth" => $auth,
                "id"   => 2,
            ]);

            return collect($res->json()['result'] ?? [])
                ->map(function ($h) {
                    $avail  = $h['interfaces'][0]['available'] ?? 0;
                    $groups = collect($h['groups'] ?? [])->pluck('name')->implode(', ');

                    return [
                        'hostid' => $h['hostid'],
                        'host'   => $h['host'],
                        'ip'     => $h['interfaces'][0]['ip'] ?? '-',
                        'groups' => $groups,
                        'status' => match((int) $avail) {
                            1       => 'Online',
                            2       => 'Offline',
                            default => 'Unknown',
                        },
                    ];
                })
                ->sortBy('host')
                ->values()
                ->all();

        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────
    //  Resolve time range dari preset atau custom
    // ─────────────────────────────────────────────
    private function resolveTimeRange(Request $request): array
    {
        $tz     = config('app.timezone');
        $preset = $request->input('range_preset', 'active');

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
            'today'        => [$now->copy()->startOfDay(),              $now->copy()->endOfDay(),              'Today'],
            'yesterday'    => [$now->copy()->subDay()->startOfDay(),    $now->copy()->subDay()->endOfDay(),    'Yesterday'],
            'last_7'       => [$now->copy()->subDays(7),                $now->copy(),                         'Last 7 days'],
            'last_30'      => [$now->copy()->subDays(30),               $now->copy(),                         'Last 30 days'],
            'last_3months' => [$now->copy()->subMonths(3),              $now->copy(),                         'Last 3 months'],
            'last_6months' => [$now->copy()->subMonths(6),              $now->copy(),                         'Last 6 months'],
            'last_year'    => [$now->copy()->subYear(),                 $now->copy(),                         'Last 1 year'],
            'this_week'    => [$now->copy()->startOfWeek(),             $now->copy()->endOfWeek(),            'This week'],
            'this_month'   => [$now->copy()->startOfMonth(),            $now->copy()->endOfMonth(),           'This month'],
            'prev_week'    => [$now->copy()->subWeek()->startOfWeek(),  $now->copy()->subWeek()->endOfWeek(), 'Previous week'],
            'prev_month'   => [$now->copy()->subMonth()->startOfMonth(),$now->copy()->subMonth()->endOfMonth(),'Previous month'],
            default        => [null, null, 'Active'],
        };

        return ['from' => $from, 'to' => $to, 'preset' => $preset, 'label' => $label];
    }

    // ─────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $tz         = config('app.timezone');
        $today      = Carbon::today($tz);
        $zbxDevices = $this->getZabbixDevices();
        $hostIds    = collect($zbxDevices)->pluck('hostid')->all();

        // ── Resolve time range ────────────────────
        $range = $this->resolveTimeRange($request);

        // ── Ambil record maintenance terbaru per device (all-time) ──
        $lastMaintenanceMap = MaintenanceSchedule::with('doneBy')
            ->whereIn('device_id', $hostIds)
            ->where('is_done', true)
            ->orderBy('done_at', 'desc')
            ->get()
            ->groupBy('device_id')
            ->map(fn($records) => $records->first());

        // ── doneTodayMap: next_maintenance masih di masa depan (logika asli) ──
        $doneTodayMap = $lastMaintenanceMap->filter(
            fn($record) => $record->next_maintenance
                        && Carbon::parse($record->next_maintenance, $tz)->greaterThan($today)
        );

        $doneToday = $doneTodayMap->count();

        // ── doneInRangeMap: filter by range yang dipilih ──────────
        if ($range['preset'] === 'active') {
            // Default: sama dengan doneTodayMap
            $doneInRangeMap = $doneTodayMap;
        } else {
            $rangeQuery = MaintenanceSchedule::with('doneBy')
                ->whereIn('device_id', $hostIds)
                ->where('is_done', true);

            if ($range['from']) $rangeQuery->where('done_at', '>=', $range['from']);
            if ($range['to'])   $rangeQuery->where('done_at', '<=', $range['to']);

            $doneInRangeMap = $rangeQuery->orderBy('done_at', 'desc')
                ->get()
                ->groupBy('device_id')
                ->map(fn($records) => $records->first());
        }

        return view('maintenance', compact(
            'zbxDevices',
            'doneTodayMap',
            'doneInRangeMap',
            'lastMaintenanceMap',
            'doneToday',
            'range',
        ));
    }

    // ─────────────────────────────────────────────
    //  STORE
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'devices'       => 'required|array|min:1',
            'devices.*'     => 'required|string',
            'notes'         => 'nullable|string|max:1000',
            'interval_days' => 'nullable|integer|min:1|max:365',
        ]);

        $zbxDevices   = collect($this->getZabbixDevices())->keyBy('hostid');
        $tz           = config('app.timezone');
        $today        = Carbon::today($tz);
        $doneAt       = Carbon::now($tz);
        $intervalDays = (int) ($request->interval_days ?? 3);
        $nextMaint    = $doneAt->copy()->addDays($intervalDays)->toDateString();
        $count        = 0;

        foreach ($request->devices as $hostId) {
            $zbxDevice  = $zbxDevices->get($hostId);
            $deviceName = $zbxDevice['host'] ?? $hostId;

            $alreadyDone = MaintenanceSchedule::where('device_id', $hostId)
                ->where('is_done', true)
                ->where('next_maintenance', '>', $today->toDateString())
                ->exists();

            if ($alreadyDone) continue;

            MaintenanceSchedule::create([
                'device_id'        => $hostId,
                'device_name'      => $deviceName,
                'scheduled_date'   => $today->toDateString(),
                'next_maintenance' => $nextMaint,
                'interval_days'    => $intervalDays,
                'is_done'          => true,
                'done_by'          => auth()->id(),
                'done_at'          => $doneAt,
                'notes'            => $request->notes,
            ]);

            $device = Device::where('device_id', $hostId)->first();
            if ($device) {
                ActivityLog::create([
                    'device_id' => $device->id,
                    'user_id'   => auth()->id(),
                    'type'      => 'Maintenance',
                    'detail'    => 'Maintenance selesai: ' . $deviceName
                                   . ' (interval: ' . $intervalDays . ' hari)'
                                   . ($request->notes ? ' — ' . $request->notes : ''),
                ]);
            }

            $count++;
        }

        if ($count === 0) {
            return back()->with('success', 'Semua device yang dipilih sudah tercatat maintenance dan belum melewati periodenya.');
        }

        return back()->with('success', $count . ' device berhasil dicatat maintenance. Maintenance berikutnya dalam ' . $intervalDays . ' hari.');
    }

    // ─────────────────────────────────────────────
    //  DESTROY
    // ─────────────────────────────────────────────
    public function destroy($id)
    {
        MaintenanceSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Record berhasil dihapus.');
    }
}