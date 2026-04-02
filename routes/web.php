<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\AuthController;

define('ZABBIX_URL',  "http://210.57.222.125:8481/zabbix");
define('ZABBIX_USER', "Admin");
define('ZABBIX_PASS', "zabbix");
define('ZABBIX_API',  ZABBIX_URL . "/api_jsonrpc.php");

function getZabbixToken(): ?string
{
    return Cache::remember('zabbix_api_token', 1500, function () {
        try {
            $res = Http::timeout(5)->post(ZABBIX_API, [
                "jsonrpc" => "2.0",
                "method"  => "user.login",
                "params"  => ["username" => ZABBIX_USER, "password" => ZABBIX_PASS],
                "id"      => 1,
            ]);
            return $res->json()['result'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    });
}

function getZabbixSessionCookie(): ?string
{
    return Cache::remember('zabbix_web_cookie', 1500, function () {
        try {
            $jar    = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'cookies'         => $jar,
                'allow_redirects' => true,
                'timeout'         => 5,
                'connect_timeout' => 5,
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

        } catch (\Exception $e) {
            return null;
        }
    });
}

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
// AUTH
// ============================================================
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// ============================================================
// SEMUA ROUTE YANG BUTUH LOGIN
// ============================================================
Route::middleware('auth')->group(function () {

    // ── DASHBOARD ─────────────────────────────────────────────
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

    // ── MONITORING ────────────────────────────────────────────
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

    // ── PROXY GRAPH ───────────────────────────────────────────
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

    // ── PERANGKAT ─────────────────────────────────────────────
    Route::get('/perangkat', function () {

        $auth = getZabbixToken();

        if (!$auth) {
            return view('perangkat', ['devices' => []]);
        }

        $res = Http::post(ZABBIX_API, [
            "jsonrpc" => "2.0",
            "method"  => "host.get",
            "params"  => [
                "output"           => ["hostid", "host"],
                "selectInterfaces" => ["ip", "port", "type", "available"],
                "selectGroups"     => ["name"],
                "selectTags"       => ["tag", "value"],
                "selectInventory"  => "extend",
            ],
            "auth" => $auth,
            "id"   => 20,
        ]);

        $zbxHosts = $res->json()['result'] ?? [];
        $devices  = [];

        foreach ($zbxHosts as $h) {
            $iface  = $h['interfaces'][0] ?? [];
            $avail  = $iface['available'] ?? 0;
            $inv    = is_array($h['inventory']) ? $h['inventory'] : [];
            $groups = collect($h['groups'] ?? [])->pluck('name')->implode(', ');
            $tags   = collect($h['tags'] ?? [])
                ->map(fn($t) => $t['tag'] . ($t['value'] ? ': ' . $t['value'] : ''))
                ->implode(', ');

            $ifaceType = match((int)($iface['type'] ?? 0)) {
                1 => 'ZBX', 2 => 'SNMP', 3 => 'IPMI', 4 => 'JMX', default => '-',
            };

            if ($avail == 1)     $status = 'Online';
            elseif ($avail == 2) $status = 'Offline';
            else                 $status = 'Unknown';

            $devices[] = [
                'hostid'     => $h['hostid'],
                'host'       => $h['host'],
                'ip'         => $iface['ip'] ?? '-',
                'iface_type' => $ifaceType,
                'status'     => $status,
                'groups'     => $groups,
                'tags'       => $tags,
                'type'       => $inv['type'] ?? '',
                'vendor'     => $inv['vendor'] ?? '',
                'model'      => $inv['model'] ?? '',
                'serial'     => $inv['serialno_a'] ?? '',
                'mac'        => $inv['macaddress_a'] ?? '',
                'os'         => $inv['os'] ?? '',
                'location'   => $inv['location'] ?? '',
                'asset_tag'  => $inv['asset_tag'] ?? '',
                'hardware'   => $inv['hardware'] ?? '',
                'notes'      => $inv['notes'] ?? '',
            ];
        }

        return view('perangkat', compact('devices'));

    })->name('perangkat.index');

    // ── ZABBIX OPTIONS (groups & templates untuk dropdown) ────
    Route::get('/zabbix/options', function () {

        $auth = getZabbixToken();
        if (!$auth) return response()->json(['groups' => [], 'templates' => []]);

        try {
            $groups = Http::timeout(5)->post(ZABBIX_API, [
                "jsonrpc" => "2.0",
                "method"  => "hostgroup.get",
                "params"  => ["output" => ["groupid", "name"]],
                "auth"    => $auth,
                "id"      => 31,
            ])->json()['result'] ?? [];

            $templates = Http::timeout(5)->post(ZABBIX_API, [
                "jsonrpc" => "2.0",
                "method"  => "template.get",
                "params"  => ["output" => ["templateid", "name"]],
                "auth"    => $auth,
                "id"      => 32,
            ])->json()['result'] ?? [];

            return response()->json(compact('groups', 'templates'));

        } catch (\Exception $e) {
            return response()->json(['groups' => [], 'templates' => []]);
        }

    })->name('zabbix.options');

    Route::delete('/zabbix/host/{hostid}', function (Request $request, $hostid) {
 
    $deleted = false;
    $errors  = [];
 
    // 1. Hapus dari Zabbix
    $auth = getZabbixToken();
    if ($auth) {
        try {
            $res = Http::timeout(5)->post(ZABBIX_API, [
                "jsonrpc" => "2.0",
                "method"  => "host.delete",
                "params"  => [$hostid],
                "auth"    => $auth,
                "id"      => 40,
            ]);
 
            $result = $res->json();
 
            if (isset($result['error'])) {
                $errors[] = 'Zabbix: ' . $result['error']['data'];
            } else {
                $deleted = true;
                Cache::forget('zabbix_api_token');
            }
 
        } catch (\Exception $e) {
            $errors[] = 'Gagal konek Zabbix: ' . $e->getMessage();
        }
    } else {
        $errors[] = 'Tidak bisa konek ke Zabbix';
    }
 
    // 2. Hapus dari DB lokal (berdasarkan hostid Zabbix atau ip/nama)
    // Sesuaikan nama tabel/kolom dengan struktur DB kamu
    try {
        \DB::table('devices')
            ->where('zabbix_hostid', $hostid)
            ->delete();
    } catch (\Exception $e) {
        // Jika kolom zabbix_hostid tidak ada, coba cara lain atau abaikan
        // $errors[] = 'DB lokal: ' . $e->getMessage();
    }
 
    if (!empty($errors)) {
        return back()->with('error', implode(' | ', $errors));
    }
 
    return back()->with('success', 'Host berhasil dihapus dari Zabbix dan database lokal.');
 
})->name('zabbix.host.destroy');

Route::get('/zabbix/host/{hostid}', function ($hostid) {
 
    $auth = getZabbixToken();
    if (!$auth) return response()->json(['error' => 'Tidak bisa konek ke Zabbix'], 500);
 
    try {
        $res = Http::timeout(5)->post(ZABBIX_API, [
            "jsonrpc" => "2.0",
            "method"  => "host.get",
            "params"  => [
                "hostids" => [$hostid],
                "output"  => ["hostid", "host"],
                "selectInterfaces" => ["interfaceid", "type", "ip", "port", "main"],
                "selectGroups"     => ["groupid", "name"],
                "selectParentTemplates" => ["templateid", "name"],
            ],
            "auth" => $auth,
            "id"   => 50,
        ]);
 
        $host = $res->json()['result'][0] ?? null;
        if (!$host) return response()->json(['error' => 'Host tidak ditemukan'], 404);
 
        return response()->json($host);
 
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
 
})->name('zabbix.host.show');
 
// -------------------------------------------------------
// Update host di Zabbix
// -------------------------------------------------------
Route::put('/zabbix/host/{hostid}', function (Request $request, $hostid) {
 
    $auth = getZabbixToken();
    if (!$auth) return back()->with('error', 'Tidak bisa konek ke Zabbix');
 
    $ifaceType = match($request->iface_type) {
        'SNMP' => 2, 'IPMI' => 3, 'JMX' => 4, default => 1,
    };
 
    // Ambil interfaceid existing dulu
    try {
        $existing = Http::timeout(5)->post(ZABBIX_API, [
            "jsonrpc" => "2.0",
            "method"  => "host.get",
            "params"  => [
                "hostids"          => [$hostid],
                "output"           => ["hostid"],
                "selectInterfaces" => ["interfaceid", "main"],
            ],
            "auth" => $auth,
            "id"   => 51,
        ])->json()['result'][0] ?? null;
 
        $interfaceid = collect($existing['interfaces'] ?? [])
            ->firstWhere('main', '1')['interfaceid'] ?? null;
 
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal ambil data interface: ' . $e->getMessage());
    }
 
    $params = [
        "hostid" => $hostid,
        "host"   => $request->host_name,
        "groups" => [["groupid" => $request->group_id]],
        "templates" => $request->template_ids
            ? array_map(fn($t) => ["templateid" => $t], (array)$request->template_ids)
            : [],
    ];
 
    // Update interface jika ditemukan
    if ($interfaceid) {
        $params["interfaces"] = [[
            "interfaceid" => $interfaceid,
            "type"        => $ifaceType,
            "main"        => 1,
            "useip"       => 1,
            "ip"          => $request->ip_address,
            "dns"         => "",
            "port"        => $request->port ?? "10050",
        ]];
    }
 
    try {
        $res    = Http::timeout(5)->post(ZABBIX_API, [
            "jsonrpc" => "2.0",
            "method"  => "host.update",
            "params"  => $params,
            "auth"    => $auth,
            "id"      => 52,
        ]);
 
        $result = $res->json();
 
        if (isset($result['error'])) {
            return back()->with('error', 'Zabbix error: ' . $result['error']['data']);
        }
 
        Cache::forget('zabbix_api_token');
        return back()->with('success', 'Host ' . $request->host_name . ' berhasil diupdate.');
 
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal konek ke Zabbix: ' . $e->getMessage());
    }
 
})->name('zabbix.host.update');

    // ── LOG ────────────────────────────────────────────────────
    Route::get('/log', [ActivityLogController::class, 'index'])->name('log.index');

    // ── MAINTENANCE ───────────────────────────────────────────
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');

    // ── ADMIN ONLY ────────────────────────────────────────────
    Route::middleware('admin')->group(function () {

        // Tambah host baru ke Zabbix
        Route::post('/zabbix/host', function (Request $request) {

            $auth = getZabbixToken();
            if (!$auth) return back()->with('error', 'Tidak bisa konek ke Zabbix');

            $ifaceType = match($request->iface_type) {
                'SNMP' => 2, 'IPMI' => 3, 'JMX' => 4, default => 1,
            };

            try {
                $res = Http::timeout(5)->post(ZABBIX_API, [
                    "jsonrpc" => "2.0",
                    "method"  => "host.create",
                    "params"  => [
                        "host"       => $request->host_name,
                        "interfaces" => [[
                            "type"  => $ifaceType,
                            "main"  => 1,
                            "useip" => 1,
                            "ip"    => $request->ip_address,
                            "dns"   => "",
                            "port"  => $request->port ?: "10050",
                        ]],
                        "groups"    => [["groupid" => $request->group_id]],
                        "templates" => $request->template_ids
                            ? array_map(fn($t) => ["templateid" => $t], (array)$request->template_ids)
                            : [],
                    ],
                    "auth" => $auth,
                    "id"   => 30,
                ]);

                $result = $res->json();

                if (isset($result['error'])) {
                    return back()->with('error', 'Zabbix: ' . $result['error']['data']);
                }

                Cache::forget('zabbix_api_token');
                return back()->with('success', 'Host ' . $request->host_name . ' berhasil ditambahkan ke Zabbix');

            } catch (\Exception $e) {
                return back()->with('error', 'Gagal konek ke Zabbix');
            }

        })->name('zabbix.host.store');

        // Log
        Route::post('/log', [ActivityLogController::class, 'store'])->name('log.store');

        // Maintenance
        Route::post('/maintenance',           [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::post('/maintenance/{id}/done', [MaintenanceController::class, 'markDone'])->name('maintenance.done');
        Route::delete('/maintenance/{id}',    [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
    });
});