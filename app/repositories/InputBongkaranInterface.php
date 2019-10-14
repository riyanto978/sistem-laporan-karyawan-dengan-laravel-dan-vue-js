<?php

namespace App\repositories;

interface  InputBongkaranInterface {

    public function sementara($operator);
    public function selesai($operator, $tanggal);
    public function ambilinput_bongkaran();
    public function store($request);
    public function update($request, $id);
    public function destroy($id);
    public function DataBongkaran($cari);

}