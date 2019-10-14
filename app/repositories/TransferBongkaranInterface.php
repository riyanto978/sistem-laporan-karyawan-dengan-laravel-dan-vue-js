<?php

namespace App\repositories;

interface  TransferBongkaranInterface {

    public function belumTransfer();
    public function SimpanTransfer($request);
    public function SudahTransfer($tanggal);
    public function UpdateTransfer($id);

    
}