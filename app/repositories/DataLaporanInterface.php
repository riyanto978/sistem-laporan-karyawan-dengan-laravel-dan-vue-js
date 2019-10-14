<?php

namespace App\repositories;

interface  DataLaporanInterface
{
    public function resumeHome($operator, $tanggal);

    public function resumeMonitoring($operator, $tanggal, $id_proses, $id_pol);

    public function resumeTanggal($id, $tanggal);

    public function resume($shift, $tanggal);

    public function monitoring($shift, $tanggal);

    public function resumepol($id);

    public function resumecounter($id);

    public function pencapaian($tipe, $id_pol);

    public function coba();

    public function cetak($awal, $akhi);

    public function export();
}
