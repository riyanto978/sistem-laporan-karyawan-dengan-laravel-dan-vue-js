<?php

namespace App\repositories;

interface  BongkaranInterface {

    public function sementara($operator);

    public function selesai($operator, $tanggal);

    public function ambilBongkaran();

    public function store( $request);

    public function update( $request, $id);

    public function updateInputBongkaran($id, $status);

    public function destroy($id);

    public function BongkaranDataPreperso($cari);
}