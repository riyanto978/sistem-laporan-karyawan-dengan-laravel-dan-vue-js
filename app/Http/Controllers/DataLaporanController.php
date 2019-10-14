<?php

namespace App\Http\Controllers;

use App\repositories\DataLaporanInterface;
use Illuminate\Http\Request;
use PDF;
use Excel;

class DataLaporanController extends Controller
{
    protected $datalaporan;

    public function __construct(DataLaporanInterface $data)
    {
        $this->datalaporan = $data;
    }

    public function resumeHome($operator, $tanggal)
    {
        $arr = $this->datalaporan->resumeHome($operator, $tanggal);
        return response()->json($arr);
    }

    public function resumeMonitoring($operator, $tanggal, $id_proses, $id_pol)
    {
       $arr = $this->datalaporan->resumeMonitoring($operator, $tanggal, $id_proses, $id_pol);
        return $arr;
    }

    public function resumeTanggal($id, $tanggal)
    {
        $arr = $this->datalaporan->resumeTanggal($id, $tanggal);
        return response()->json($arr);
    }

    public function resume($shift, $tanggal)
    {
       $data = $this->datalaporan->resume($shift, $tanggal);
        return response()->json([
            'reguler' => $data['arr'],
            'counter' => $data['arr_counter'],
        ]);
    }

    public function monitoring($shift, $tanggal)
    {
       $data = $this->datalaporan->monitoring($shift, $tanggal);

        return response()->json([
            'data' => $data['monitoring'],
            'memo' => $data['memo'],
            'total' => $data['total'],
            'transfer' => $data['transfer'],
            'counter' => $data['counter']
        ]);
    }

    public function resumepol($id)
    {
        $data = $this->datalaporan->resumepol($id);

        return response()->json([
            'pol' => $data['pol'],
            'list_operator' => $data['list_operator'],
            'data' => $data['data'],
            'resume' => $data['resume'],
            'data_grafik' => $data['data_grafik'],
            'lot_count' => $data['lot_count'],
            'lot_jumlah' => $data['lot_jumlah'],
            'lot_reject' => $data['lot_reject'],
            'lot_good' => $data['lot_good']
        ]);
    }

    public function resumecounter($id)
    {
        $data = $this->datalaporan->resumecounter($id);
        return response()->json([
            'pol' => $data['pol'],
            'lot_count' => $data['lot_count'],
            'lot_jumlah' => $data['lot_jumlah'],
            'wip' => $data['wip']
        ]);
    }

    public function pencapaian($tipe, $id_pol)
    {
        $row = $this->datalaporan->pencapaian($tipe, $id_pol);
        return response()->json($row);
    }

    public function coba()
    {
        $pol = Pol::find(1184);
        $data = strjson(1);
        dd(count($data));
    }

    public function cetak($awa, $akhi)
    {
        $cetak = $this->datalaporan->cetak($awa,$akhi);

        $data = $cetak['cetak'];
        $awal = $cetak['awal'];
        $akhir = $cetak['akhir'];

        $pdf = PDF::loadView('pdf', compact('data', 'awal', 'akhir'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream();
    }

    public function export()
    {
        $hari_ini = date("2019-07");
        $tgl_pertama = date('Y-m-01', strtotime($hari_ini));
        $tgl_terakhir = date('Y-m-t', strtotime($hari_ini));
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}


