<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ActivityLogController;

define('ZABBIX_URL',  "http://210.57.222.125:8481/zabbix");
define('ZABBIX_USER', "Admin");
define('ZABBIX_PASS', "zabbix");
define('ZABBIX_API',  ZABBIX_URL . "/api_jsonrpc.php");

function getZabbixToken(): ?string
{
    return Cache::remember('zabbix_api_token', 1500, function () {
        $res = Http::post(ZABBIX_API, [
            "jsonrpc" => "2.0",
            "method"  => "user.login",
            "params"  => ["username" => ZABBIX_USER, "password" => ZABBIX_PASS],
            "id"      => 1,
        ]);
        return $res->json()['result'] ?? null;
    });
}

function getZabbixSessionCookie(): ?string
{
    return Cache::remember('zabbix_web_cookie', 1500, function () {
        $jar    = new \GuzzleHttp\Cookie\CookieJar();
        $client = new \GuzzleHttp\Client([
            'cookies'         => $jar,
            'allow_redirects' => true,
        ]);

        $client->post(ZABBIX_URL . "/index.php", [
            'form_params' => [
                'name'     => ZABBIX_USER,
                'password' => ZABBIX_PASS,
                'enter'    => 'Sign in',
            ],
        ]);

        foreach ($jar->toArray() as $cookie) {
            if ($cookie['Name'] === 'zbx_session') {
                return 'zbx_session=' . $cookie['Value'];
            }
        }

        return null;
    });
}

// ============================================================
// HELPER — ambil data 1 host by hostid
// ============================================================
function getHostData(string $hostid, string $auth): ?array
{
    $keywords = [
        'network traffic', 'cpu usage', 'cpu utilization',
        'cpu jumps', 'memory utilization', 'memory usage', 'load average',
    ];

    $hostRes = Http::post(ZABBIX_API, [
        "jsonrpc" => "2.0",
        "method"  => "host.get",
        "params"  => [
            "output"           => ["hostid", "host"],
            "hostids"          => [$hostid],
            "selectInterfaces" => ["available", "ip", "port", "type"],
        ],
        "auth" => $auth,
        "id"   => 10,
    ]);

    $h = $hostRes->json()['result'][0] ?? null;
    if (!$h) return null;

    $iface        = $h['interfaces'][0] ?? [];
    $availability = $iface['available'] ?? 0;

    if ($availability == 1)      $status = "Online";
    elseif ($availability == 2)  $status = "Offline";
    else                         $status = "Unknown";

    $ifaceType = match((int)($iface['type'] ?? 0)) {
        1       => 'ZBX',
        2       => 'SNMP',
        3       => 'IPMI',
        4       => 'JMX',
        default => '-',
    };

    $graphRes = Http::post(ZABBIX_API, [
        "jsonrpc" => "2.0",
        "method"  => "graph.get",
        "params"  => [
            "output"      => ["graphid", "name"],
            "hostids"     => [$hostid],
            "selectHosts" => ["hostid"],
        ],
        "auth" => $auth,
        "id"   => 11,
    ]);

    $graphs = collect($graphRes->json()['result'] ?? [])
        ->filter(function ($graph) use ($keywords) {
            $name = strtolower($graph['name']);
            return collect($keywords)->contains(fn($k) => str_contains($name, $k));
        })
        ->map(fn($g) => ['graphid' => $g['graphid'], 'name' => $g['name']])
        ->values()
        ->all();

    return [
        'id'         => $h['hostid'],
        'nama'       => $h['host'],
        'interface'  => ($iface['ip'] ?? '-') . ':' . ($iface['port'] ?? ''),
        'iface_type' => $ifaceType,
        'status'     => $status,
        'graphs'     => $graphs,
    ];
}

// ============================================================
// LOGIN
// ============================================================
Route::get('/login', function () {
    return view('login');
})->name('login');

// ============================================================
// DASHBOARD
// ============================================================
Route::get('/dashboard', function () {

    $auth = getZabbixToken();

    if (!$auth) {
        return view('dashboard', [
            'total'    => 0,
            'online'   => 0,
            'offline'  => 0,
            'severity' => [5=>0,4=>0,3=>0,2=>0,1=>0,0=>0],
            'problems' => [],
        ]);
    }

    $hosts = Http::post(ZABBIX_API, [
        "jsonrpc" => "2.0",
        "method"  => "host.get",
        "params"  => [
            "output"           => ["hostid", "host"],
            "selectInterfaces" => ["available"],
        ],
        "auth" => $auth,
        "id"   => 2,
    ]);

    $hostData = $hosts->json()['result'] ?? [];
    $total    = count($hostData);
    $online   = 0;
    $offline  = 0;

    foreach ($hostData as $h) {
        $avail = $h['interfaces'][0]['available'] ?? 0;
        if ($avail == 1)      $online++;
        elseif ($avail == 2)  $offline++;
    }

    $triggersRes = Http::post(ZABBIX_API, [
        "jsonrpc" => "2.0",
        "method"  => "trigger.get",
        "params"  => [
            "output"      => ["triggerid", "description", "priority", "lastchange"],
            "selectHosts" => ["hostid", "host"],
            "selectTags"  => ["tag", "value"],
            "filter"      => ["value" => 1],
            "sortfield"   => "lastchange",
            "sortorder"   => "DESC",
            "only_true"   => true,
            "monitored"   => true,
        ],
        "auth" => $auth,
        "id"   => 3,
    ]);

    $triggers      = $triggersRes->json()['result'] ?? [];
    $severityColor = [
        5 => 'bg-[#E27D74]', 4 => 'bg-[#E89A6D]',
        3 => 'bg-[#E7BE78]', 2 => 'bg-[#E8D38A]',
        1 => 'bg-[#8AA3D8]', 0 => 'bg-gray-300',
    ];

    $severity = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0, 0=>0];
    foreach ($triggers as $t) {
        $p = (int) $t['priority'];
        if (isset($severity[$p])) $severity[$p]++;
    }

    $problems = [];
    foreach ($triggers as $t) {
        $sev     = (int) $t['priority'];
        $elapsed = time() - (int) $t['lastchange'];

        if ($elapsed < 60)       $duration = $elapsed . 's';
        elseif ($elapsed < 3600) $duration = floor($elapsed/60) . 'm ' . ($elapsed%60) . 's';
        else                     $duration = floor($elapsed/3600) . 'h ' . floor(($elapsed%3600)/60) . 'm';

        $tags = collect($t['tags'] ?? [])
            ->map(fn($tag) => $tag['tag'] . ($tag['value'] ? ': ' . $tag['value'] : ''))
            ->implode(', ');

        $problems[] = [
            'time'     => date('H:i:s', (int) $t['lastchange']),
            'host'     => $t['hosts'][0]['host'] ?? 'Unknown',
            'name'     => $t['description'],
            'severity' => $sev,
            'color'    => $severityColor[$sev] ?? 'bg-gray-200',
            'duration' => $duration,
            'tags'     => $tags,
        ];
    }

    return view('dashboard', compact('total', 'online', 'offline', 'severity', 'problems'));
});

// ============================================================
// MONITORING 
// ============================================================
Route::get('/monitoring', function () {

    $auth = getZabbixToken();

    if (!$auth) {
        return view('monitoring', ['perangkat' => []]);
    }

    $hosts = Http::post(ZABBIX_API, [
        "jsonrpc" => "2.0",
        "method"  => "host.get",
        "params"  => [
            "output"           => ["hostid", "host"],
            "selectInterfaces" => ["available", "ip", "port", "type"],
        ],
        "auth" => $auth,
        "id"   => 2,
    ]);

    $data = $hosts->json()['result'] ?? [];

    if (empty($data)) {
        return view('monitoring', ['perangkat' => []]);
    }

    $perangkat = [];

    foreach ($data as $h) {
        $iface        = $h['interfaces'][0] ?? [];
        $availability = $iface['available'] ?? 0;

        if ($availability == 1)      $status = "Online";
        elseif ($availability == 2)  $status = "Offline";
        else                         $status = "Unknown";

        $ifaceType = match((int)($iface['type'] ?? 0)) {
            1       => 'ZBX',
            2       => 'SNMP',
            3       => 'IPMI',
            4       => 'JMX',
            default => '-',
        };

        $perangkat[] = [
            "id"         => $h["hostid"],
            "nama"       => $h["host"],
            "interface"  => ($iface['ip'] ?? '-') . ':' . ($iface['port'] ?? ''),
            "iface_type" => $ifaceType,
            "status"     => $status,
        ];
    }

    return view('monitoring', compact('perangkat'));
});

// ============================================================
// MONITORING DETAIL 
// ============================================================
Route::get('/monitoring/{hostid}', function (string $hostid) {

    $auth = getZabbixToken();

    if (!$auth) {
        return redirect('/monitoring');
    }

    $host = getHostData($hostid, $auth);

    if (!$host) {
        abort(404, 'Host not found');
    }

    return view('monitoringdetail', compact('host'));
});

// ============================================================
// PROXY GRAPH
// ============================================================
Route::get('/zabbix-graph', function (Request $request) {

    $graphid = $request->query('graphid');
    $width   = $request->query('width', 900);
    $height  = $request->query('height', 200);
    $period  = $request->query('period', 3600);

    if (!$graphid) return response('graphid required', 400);

    $cookie = getZabbixSessionCookie();
    if (!$cookie) return response('Gagal mendapatkan session Zabbix', 503);

    try {
        $client = new \GuzzleHttp\Client();
        $query  = compact('graphid', 'width', 'height', 'period');

        $response    = $client->get(ZABBIX_URL . "/chart2.php", [
            'query'   => $query,
            'headers' => ['Cookie' => $cookie],
        ]);
        $contentType = $response->getHeaderLine('Content-Type');

        if (!str_contains($contentType, 'image')) {
            Cache::forget('zabbix_web_cookie');
            $cookie   = getZabbixSessionCookie();
            $response = $client->get(ZABBIX_URL . "/chart2.php", [
                'query'   => $query,
                'headers' => ['Cookie' => $cookie],
            ]);
            $contentType = $response->getHeaderLine('Content-Type');
        }

        if (str_contains($contentType, 'image')) {
            return response($response->getBody()->getContents(), 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        return response('Graph tidak tersedia', 404);

    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
});

// ============================================================
// DEVICES 
// ============================================================
Route::get('/perangkat',              [DeviceController::class, 'index'])->name('perangkat.index');
Route::get('/tambah-perangkat',       [DeviceController::class, 'create'])->name('perangkat.create');
Route::post('/tambah-perangkat',      [DeviceController::class, 'store'])->name('perangkat.store');
Route::get('/perangkat/{id}/edit',    [DeviceController::class, 'edit'])->name('perangkat.edit');
Route::put('/perangkat/{id}',         [DeviceController::class, 'update'])->name('perangkat.update');
Route::delete('/perangkat/{id}',      [DeviceController::class, 'destroy'])->name('perangkat.destroy');

// ============================================================
// LOG AKTIVITAS
// ============================================================
Route::get('/log',  [ActivityLogController::class, 'index'])->name('log.index');
Route::post('/log', [ActivityLogController::class, 'store'])->name('log.store');