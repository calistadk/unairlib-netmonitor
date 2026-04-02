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
                ],
                "auth" => $auth,
                "id"   => 2,
            ]);

            return collect($res->json()['result'] ?? [])
                ->map(function ($h) {
                    $avail = $h['interfaces'][0]['available'] ?? 0;
                    return [
                        'hostid' => $h['hostid'],
                        'host'   => $h['host'],
                        'ip'     => $h['interfaces'][0]['ip'] ?? '-',
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
        $today      = Carbon::today();
        $zbxDevices = $this->getZabbixDevices();

        // Ambil semua maintenance yang done hari ini
        $todayDoneRecords = MaintenanceSchedule::with('doneBy')
            ->whereDate('done_at', $today)
            ->where('is_done', true)
            ->get();

        // Map: hostid => record (untuk cek apakah device sudah done hari ini)
        $doneTodayMap = $todayDoneRecords->keyBy('device_id');

        // Map: hostid => record maintenance terakhir (untuk kolom Last Maintenance)
        $hostIds = collect($zbxDevices)->pluck('hostid')->all();

        $lastMaintenanceMap = MaintenanceSchedule::with('doneBy')
            ->whereIn('device_id', $hostIds)
            ->where('is_done', true)
            ->orderBy('done_at', 'desc')
            ->get()
            ->groupBy('device_id')
            ->map(fn($records) => $records->first()); // ambil yang paling baru per device

        $doneToday = $todayDoneRecords->count();

        return view('maintenance', compact(
            'zbxDevices',
            'doneTodayMap',
            'lastMaintenanceMap',
            'doneToday'
        ));
    }

    // ─────────────────────────────────────────────
    //  STORE — catat maintenance dari checklist
    //  Menerima array device[] yang dicentang,
    //  langsung mark done + catat activity log
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'devices'   => 'required|array|min:1',
            'devices.*' => 'required|string',
            'notes'     => 'nullable|string|max:1000',
        ]);

        // Ambil nama device dari Zabbix (supaya ada device_name di DB)
        $zbxDevices = collect($this->getZabbixDevices())->keyBy('hostid');

        $today  = Carbon::today();
        $doneAt = now();
        $count  = 0;

        foreach ($request->devices as $hostId) {
            $zbxDevice  = $zbxDevices->get($hostId);
            $deviceName = $zbxDevice['host'] ?? $hostId;

            // Hindari duplikat: skip jika device ini sudah done hari ini
            $alreadyDone = MaintenanceSchedule::where('device_id', $hostId)
                ->whereDate('done_at', $today)
                ->where('is_done', true)
                ->exists();

            if ($alreadyDone) continue;

            // Buat record maintenance & langsung tandai done
            $schedule = MaintenanceSchedule::create([
                'device_id'        => $hostId,
                'device_name'      => $deviceName,
                'scheduled_date'   => $today,
                'next_maintenance' => $today->copy()->addDays(3),
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
                                   . ($request->notes ? ' — ' . $request->notes : ''),
                ]);
            }

            $count++;
        }

        if ($count === 0) {
            return back()->with('success', 'Semua device yang dipilih sudah tercatat maintenance hari ini.');
        }

        return back()->with('success', $count . ' device berhasil dicatat maintenance.');
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