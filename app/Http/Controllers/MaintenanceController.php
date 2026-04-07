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
    //  INDEX
    // ─────────────────────────────────────────────
    public function index()
    {
        // FIX: gunakan timezone aplikasi agar konsisten di semua user
        $today      = Carbon::today(config('app.timezone'));
        $zbxDevices = $this->getZabbixDevices();
        $hostIds    = collect($zbxDevices)->pluck('hostid')->all();

        // Ambil record maintenance TERAKHIR per device (yang sudah done)
        $lastMaintenanceMap = MaintenanceSchedule::with('doneBy')
            ->whereIn('device_id', $hostIds)
            ->where('is_done', true)
            ->orderBy('done_at', 'desc')
            ->get()
            ->groupBy('device_id')
            ->map(fn($records) => $records->first());

        // Device dianggap "sudah maintenance" kalau next_maintenance masih di masa depan
        $doneTodayMap = $lastMaintenanceMap->filter(
            fn($record) => $record->next_maintenance
                        && Carbon::parse($record->next_maintenance, config('app.timezone'))
                                 ->greaterThan($today)
        );

        $doneToday = $doneTodayMap->count();

        return view('maintenance', compact(
            'zbxDevices',
            'doneTodayMap',
            'lastMaintenanceMap',
            'doneToday'
        ));
    }

    // ─────────────────────────────────────────────
    //  STORE — catat maintenance dari checklist
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'devices'        => 'required|array|min:1',
            'devices.*'      => 'required|string',
            'notes'          => 'nullable|string|max:1000',
            // interval_days: minimal 1 hari, maksimal 365 hari, default 3
            'interval_days'  => 'nullable|integer|min:1|max:365',
        ]);

        $zbxDevices   = collect($this->getZabbixDevices())->keyBy('hostid');

        // FIX: gunakan timezone aplikasi agar konsisten
        $tz     = config('app.timezone');
        $today  = Carbon::today($tz);
        $doneAt = Carbon::now($tz);

        // Ambil interval dari form, default 3 hari jika tidak diisi
        $intervalDays = (int) ($request->interval_days ?? 3);
        $nextMaint    = $doneAt->copy()->addDays($intervalDays)->toDateString();

        $count = 0;

        foreach ($request->devices as $hostId) {
            $zbxDevice  = $zbxDevices->get($hostId);
            $deviceName = $zbxDevice['host'] ?? $hostId;

            // Hindari duplikat: skip jika next_maintenance device ini masih di masa depan
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

            // Catat ke activity log jika device ada di tabel devices MySQL
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
    //  DESTROY — hapus record maintenance
    // ─────────────────────────────────────────────
    public function destroy($id)
    {
        MaintenanceSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Record berhasil dihapus.');
    }
}