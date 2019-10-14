<?php

namespace App\repositories;

interface  LotInterface
{

    public function dataLot($id_pol);

    public function LaporanLot($id_pol);

    public function store( $request);

    public function update( $request, $id);

    public function destroy($id);

}
