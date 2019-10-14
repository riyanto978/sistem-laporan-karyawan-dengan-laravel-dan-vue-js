<?php

namespace App\repositories;

interface  LaporanInterface
{

    public function inerReguler($id_pol);

    public function inerRegulerke($ke, $id_pol);

    public function allLaporan($id_pol, $proses_ke, $operator, $status, $tanggal);

    public function allTanggal($id_pol, $proses_ke, $operator);

    public function store($request);

    public function update($request, $id);

    public function destroy($id);
}
