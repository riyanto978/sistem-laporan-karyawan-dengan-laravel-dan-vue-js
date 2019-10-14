<?php

namespace App\repositories;

interface  CounterInterface
{
    public function dataCounter($id_pol);

    public function store( $request);

    public function update( $request, $id);

    public function ubahIner($id, $id_pol, $per_iner, $id_counter = 'null');

    public function destroy($id);
}
