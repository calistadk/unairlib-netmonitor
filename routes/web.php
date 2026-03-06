<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/monitoring', function () {
    return view('monitoring');
});

Route::get('/perangkat', function () {
    return view('perangkat');
});

Route::get('/log', function () {
    return view('log');
});
Route::view('/tambah-perangkat', 'tambahperangkat');
Route::post('/tambah-perangkat', function (Request $request) {
    // sementara belum simpan DB
    // nanti logic insert taruh di sini / controller

    return redirect('/perangkat');
});

Route::view('/edit-perangkat', 'editperangkat');

/*
|--------------------------------------------------------------------------
| HALAMAN PERANGKAT (LIST)
|--------------------------------------------------------------------------
*/
Route::get('/perangkat', function () {

    // data dummy tabel
    $devices = [
        (object)[
            'id' => 1,
            'id_perangkat' => 'RTR-01',
            'jenis' => 'Router',
            'status' => 'Tersedia',
        ],
        (object)[
            'id' => 2,
            'id_perangkat' => 'WIFI-02',
            'jenis' => 'Wi-Fi',
            'status' => 'Maintenance',
        ],
    ];

    return view('perangkat', compact('devices'));

})->name('perangkat.index');


/*
|--------------------------------------------------------------------------
| HALAMAN EDIT PERANGKAT
|--------------------------------------------------------------------------
*/
Route::get('/perangkat/{id}/edit', function ($id) {

    // dummy data (anggap dari DB)
    $device = (object)[
        'id' => $id,
        'id_perangkat' => 'RTR-01',
        'jenis' => 'Router',
        'merek_model' => 'Cisco ISR 4321',
        'serial_number' => 'FTX12345',
        'ip_address' => '192.168.1.1',
        'mac_address' => 'AA:BB:CC',
        'lokasi' => 'MOVIO',
        'status' => 'Tersedia',
        'tanggal_pembelian' => '2020-12-01',
        'masa_garansi' => '2026-12-01',
    ];

    return view('editperangkat', compact('device'));

})->name('perangkat.edit');


/*
|--------------------------------------------------------------------------
| SUBMIT UPDATE (DUMMY)
|--------------------------------------------------------------------------
*/
Route::put('/perangkat/{id}', function (Request $request, $id) {

    // TIDAK update DB
    // hanya simulasi submit

    return redirect('/perangkat')
        ->with('success', 'Perangkat berhasil diperbarui (dummy)');

})->name('perangkat.update');


use Illuminate\Support\Facades\Http;

Route::get('/monitoring', function () {

    $url = "http://210.57.222.125:8481/zabbix/api_jsonrpc.php";

    /*
    ================= LOGIN ZABBIX =================
    */
    $login = Http::post($url, [
        "jsonrpc" => "2.0",
        "method" => "user.login",
        "params" => [
            "username" => "Admin",
            "password" => "zabbix"
        ],
        "id" => 1
    ]);

    $auth = $login->json()['result'] ?? null;

    if (!$auth) {
        return view('monitoring', ['perangkat' => []]);
    }

    /*
    ================= AMBIL HOST + AVAILABILITY =================
    */
    $hosts = Http::post($url, [
        "jsonrpc" => "2.0",
        "method" => "host.get",
        "params" => [
            "output" => ["hostid", "host"],
            "selectInterfaces" => ["ip", "available"]
        ],
        "auth" => $auth,
        "id" => 2
    ]);

    $data = $hosts->json()['result'] ?? [];

    /*
    ================= FORMAT DATA =================
    */
    $perangkat = [];

    foreach ($data as $h) {

        $availability = $h['interfaces'][0]['available'] ?? 0;

        if ($availability == 1) {
            $status = "Online";
        } elseif ($availability == 2) {
            $status = "Offline";
        } else {
            $status = "Unknown";
        }

        $perangkat[] = [
            "id" => $h["hostid"],
            "nama" => $h["host"],
            "jenis" => "Network Device",
            "status" => $status
        ];
    }

    return view('monitoring', compact('perangkat'));

});