<?php

use Illuminate\Support\Facades\Route;

Route::view('/login', 'login');
Route::view('/dashboard', 'dashboard');
Route::view('/perangkat', 'perangkat');
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