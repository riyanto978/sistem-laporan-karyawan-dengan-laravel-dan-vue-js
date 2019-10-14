<?php

namespace App\Http\Controllers;

use App\repositories\TransferBongkaranInterface;
use Illuminate\Http\Request;

class TransferBongkaranController extends Controller
{
    protected $transfer;

    public function __construct(TransferBongkaranInterface $transfer)
    {
        $this->transfer = $transfer;
    }

    public function belumTransfer()
    {
        return response()->json($this->transfer->belumTransfer());
    }

    public function SimpanTransfer(Request $request)
    {
        $data = $this->transfer->SimpanTransfer($request);
        return response()->json($data);
    }

    public function SudahTransfer($tanggal)
    {
        $data = $this->transfer->SudahTransfer($tanggal);
        return response()->json($data);
    }

    public function UpdateTransfer($id)
    {   
        $this->transfer->UpdateTransfer($id);
    }

}
