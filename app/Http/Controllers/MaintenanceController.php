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
            // Login ke Zabbix
            $loginRes = Http::timeout(5)->post('http://210.57.222.125:8481/zabbix/api_jsonrpc.php', [
                "jsonrpc" => "2.0",
                "method"  => "user.login",
                "params"  => ["username" => "Admin", "password" => "zabbix"],
                "id"      => 1,
            ]);

            $auth = $loginRes->json()['result'] ?? null;
            if (!$auth) return [];

            // Ambil semua host
            $res = Http::timeout(5)->post('http://210.57.222.125:8481/zabbix/api_jsonrpc.php', [
                "jsonrpc" => "2.0",
                "method"  => "host.get",
                "params"  => [
                    "output"           => ["hostid", "host"],
                    "selectInterfaces" => ["ip"],
                ],
                "auth" => $auth,
                "id"   => 2,
            ]);

            return collect($res->json()['result'] ?? [])
                ->map(fn($h) => [
                    'hostid' => $h['hostid'],
                    'host'   => $h['host'],
                    'ip'     => $h['interfaces'][0]['ip'] ?? '-',
                ])
                ->sortBy('host')
                ->values()
                ->all();

        } catch (\Exception $e) {
            return [];
        }
    }

    public function index()
    {
        $today = Carbon::today();

        $todaySchedules = MaintenanceSchedule::whereDate('scheduled_date', $today)
            ->orderBy('is_done')
            ->get();

        $upcoming = MaintenanceSchedule::whereDate('scheduled_date', '>', $today)
            ->whereDate('scheduled_date', '<=', $today->copy()->addDays(7))
            ->orderBy('scheduled_date')
            ->get();

        $totalToday = $todaySchedules->count();
        $doneToday  = $todaySchedules->where('is_done', true)->count();
        $zbxDevices = $this->getZabbixDevices();

        return view('maintenance', compact(
            'todaySchedules', 'upcoming', 'totalToday', 'doneToday', 'zbxDevices'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id'      => 'required',
            'device_name'    => 'required',
            'scheduled_date' => 'required|date',
        ]);

        $scheduled = Carbon::parse($request->scheduled_date);

        MaintenanceSchedule::create([
            'device_id'        => $request->device_id,
            'device_name'      => $request->device_name,
            'scheduled_date'   => $scheduled,
            'next_maintenance' => $scheduled->copy()->addDays(3),
            'is_done'          => false,
        ]);

        return back()->with('success', 'Jadwal maintenance berhasil ditambahkan');
    }

    public function markDone(Request $request, $id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);

        $schedule->update([
            'is_done' => true,
            'done_at' => now(),
            'done_by' => auth()->id(),
            'notes'   => $request->notes,
        ]);

        // Buat jadwal berikutnya otomatis +3 hari
        MaintenanceSchedule::create([
            'device_id'        => $schedule->device_id,
            'device_name'      => $schedule->device_name,
            'scheduled_date'   => $schedule->next_maintenance,
            'next_maintenance' => $schedule->next_maintenance->copy()->addDays(3),
            'is_done'          => false,
        ]);

        // Catat ke activity log jika device ada di MySQL
        $device = Device::where('device_id', $schedule->device_id)->first();
        if ($device) {
            ActivityLog::create([
                'device_id' => $device->id,
                'user_id'   => auth()->id(),
                'type'      => 'Maintenance',
                'detail'    => 'Maintenance selesai: ' . $schedule->device_name
                               . ($request->notes ? ' — ' . $request->notes : ''),
            ]);
        }

        return back()->with('success', 'Maintenance ' . $schedule->device_name . ' berhasil dicatat');
    }

    public function destroy($id)
    {
        MaintenanceSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal berhasil dihapus');
    }
}