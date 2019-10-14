<?php

use Illuminate\Http\Request;

Route::get('coba', 'LaporanController@coba');
Route::get('pdf', 'LaporanController@pdf');
Route::get('export', 'LaporanController@export');
Route::get('excel','exportController@excel');
Route::get('ambilDataPol', 'PolController@ambilDataPol');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');


    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::get('datatransfer/{tipe}/{id_pol}', 'TransferController@DataTransfer');
    Route::get('packingransfer/{id_pol}', 'TransferController@packingTransfer');
    Route::post('transfer/belum','TransferController@simpanBelum');
    Route::post('transfer/sudah', 'TransferController@simpanSudah');

    Route::post('packing/belum', 'TransferController@belumPacking');
    Route::post('packing/sudah', 'TransferController@sudahPacking');

    Route::get('transfer/history/{id_pol}/{cari}','TransferController@History');

    Route::get('userAll/{cari}', 'UserController@all');
    Route::delete('user/{id}', 'UserController@destroy');
    Route::put('user/{id}', 'UserController@update');
    Route::post('user', 'UserController@store');

    Route::get('qc/{ipqc}', 'PolController@qc');

    Route::resource('/proses', 'ProsesController');
    Route::get('ambilproses', 'ProsesController@ambilproses');

    Route::resource('pol', 'PolController');
    Route::get('ambilpencapaian/{tipe}/{id_pol}','DataLaporanController@pencapaian');
    Route::put('pol/urgent/{id}','PolController@urgent');
    Route::get('data/pol/{tipe}/{cari}/{status}', 'PolController@dataReguler');
    Route::get('data/poltransfer/{cari}','PolController@dataTransfer');
    Route::get('pol/resume/{id_pol}', 'PolController@resume');
    Route::get('ambilpol/{tahun}/{tipe}/{cari}/{status}', 'PolController@ambilpol');
    Route::get('ambilpol/urgent', 'PolController@ambilurgent');
    Route::get('pol/{tipe}/Polambilpolktp', 'PolController@ambilpolktp');
    Route::get('pol/search/{cari}', 'PolController@searchPol');

    Route::get('lot/data/{id_pol}', 'LotController@dataLot');
    Route::get('lot/laporan/{id_pol}', 'LotController@LaporanLot');
    Route::resource('lot', 'LotController');

    Route::get('counter/data/{id_pol}', 'CounterController@dataCounter');
    Route::resource('counter', 'CounterController');

    Route::resource('/periode', 'PeriodeController');
    Route::resource('/kartu_sam', 'KartuSamController');
    Route::get('/applet/sementara/{operator}', 'AppletController@sementara');
    Route::get('/applet/selesai/{operator}/{tanggal}', 'AppletController@selesai');
    Route::get('/applet/{cari}/AppletDataPreperso', 'AppletController@AppletDataPreperso');
    Route::resource('/applet', 'AppletController');

    Route::get('/preperso/sementara/{operator}', 'PrepersoController@sementara');
    Route::get('/preperso/selesai/{operator}/{tanggal}', 'PrepersoController@selesai');
    Route::resource('/preperso', 'PrepersoController');
    //Laporan

    Route::get('/bongkaran/sementara/{operator}', 'bongkaranController@sementara');
    Route::get('/bongkaran/selesai/{operator}/{tanggal}', 'bongkaranController@selesai');
    Route::resource('/bongkaran', 'bongkaranController');

    Route::get('/input_bongkaran/selesai/{operator}/{tanggal}', 'InputBongkaranController@selesai');
    Route::resource('/input_bongkaran', 'InputBongkaranController');
    Route::get('/input_bongkaran/{cari}/databongkaran', 'InputBongkaranController@DataBongkaran');

    Route::get('/transfer_bongkaran/belumTransfer', 'TransferBongkaranController@belumTransfer');
    Route::get('/transfer_bongkaran/SudahTransfer/{tanggal}', 'TransferBongkaranController@SudahTransfer');
    Route::post('/transfer_bongkaran/SimpanTransfer', 'TransferBongkaranController@SimpanTransfer');
    Route::put('/transfer_bongkaran/UpdateTransfer/{id}', 'TransferBongkaranController@UpdateTransfer');

    Route::get('reguler/cetak/{awal}/{akhir}', 'LaporanController@cetak');
    Route::resource('reguler', 'LaporanController');

    Route::get('laporan/iner/reguler/pertama/{id_pol}', 'LaporanController@inerReguler');
    Route::get('laporan/iner/reguler/{ke}/{id_pol}', 'LaporanController@inerRegulerke');
    Route::get('laporan/reguler/{id_pol}/{proses_ke}/{operator}/{status}/{tanggal}', 'LaporanController@allLaporan');
    Route::get('laporan/tanggal/{id_pol}/{proses_ke}/{operator}', 'LaporanController@allTanggal');

    Route::get('laporan/{operator}/{tanggal}/resumeHome', 'DataLaporanController@resumeHome');
    Route::get('laporan/{operator}/{tanggal}/{id_proses}/{id_pol}/resumeMonitoring', 'DataLaporanController@resumeMonitoring');
    Route::get('laporan/resume/{id}/{tanggal}', 'DataLaporanController@resumeTanggal');
    Route::get('laporan/{shift}/{tanggal}/resume', 'DataLaporanController@resume');
    Route::get('laporan/{shift}/{tanggal}/monitoring', 'DataLaporanController@monitoring');

    Route::get('laporan/resumepol/{id}', 'DataLaporanController@resumepol');
    Route::get('laporan/resumecounter/{id}', 'DataLaporanController@resumecounter');

    Route::patch('settings/profile', 'Settings\UpdateProfile');
    Route::patch('settings/password', 'Settings\UpdatePassword');
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});
