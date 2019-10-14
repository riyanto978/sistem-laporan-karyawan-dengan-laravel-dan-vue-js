<?php

namespace App\repositories;

interface  TransferInterface
{
    public function DataTransfer($tipe, $id_pol);

    public function packingTransfer($id_pol);

    public function simpanBelum($request);

    public function simpanSudah($request);

    public function belumPacking($request);

    public function sudahPacking($request);

    public function History($id_pol, $cari);
}
