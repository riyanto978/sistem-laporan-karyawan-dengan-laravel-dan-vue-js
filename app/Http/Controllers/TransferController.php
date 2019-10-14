<?php

namespace App\Http\Controllers;

use App\repositories\TransferInterface;
use Illuminate\Http\Request;


class TransferController extends Controller
{
    protected $transfer;

    public function __construct(TransferInterface $transfer)
    {
        $this->transfer = $transfer;
    }
    public function DataTransfer($tipe, $id_pol)
    {
        $counter = $this->transfer->DataTransfer($tipe, $id_pol);
        return response()->json($counter);
    }

    public function packingTransfer($id_pol)
    {
        $counter = $this->transfer->packingTransfer($id_pol);
        return response()->json($counter);
    }

    public function simpanBelum(Request $request)
    {
        $data = $this->transfer->simpanBelum($request);
        return response()->json($data, 200);
    }

    public function simpanSudah(Request $request)
    {
        $transfer = $this->transfer->simpanSudah($request);

        return response()->json($transfer, 200);
    }

    public function belumPacking(Request $request)
    {
        $this->transfer->belumPacking($request);
    }

    public function sudahPacking(Request $request)
    {

        $this->transfer->sudahPacking($request);
    }

    public function History($id_pol, $cari)
    {
        $transfer = $this->transfer->History($id_pol, $cari);
        return response()->json(['data' => $transfer['transfer'],'total' => $transfer['total']]);
    }
}
