<?php

namespace App\repositories;

interface  ProsesInterface
{

    public function index();

    public function ambilproses();

    public function store( $request);

    public function update( $request, $id);

    public function destroy($id);
}
