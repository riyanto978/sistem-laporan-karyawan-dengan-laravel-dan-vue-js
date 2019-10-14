<?php

namespace App\repositories;

interface  PolInterface
{

    public function index();

    public function ambilpolktp($tipe);

    public function dataReguler($tipe, $cari, $status);

    public function dataTransfer($cari);

    public function ambilpol($tahun, $tipe, $cari, $status);

    public function searchPol($cari);

    public function store($request);

    public function qc($ipqc);

    public function ambilDataPol();

    public function update( $request, $id);

    public function destroy($id);

    public function urgent( $request, $id);

    public function ambilurgent();
}
