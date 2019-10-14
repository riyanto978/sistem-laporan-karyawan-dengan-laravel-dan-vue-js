<?php

namespace App\repositories;

use App\models\transfer_bongkaran;
use App\models\Bongkaran;
use Illuminate\Support\Facades\DB;

class TransferBongkaranRepository implements TransferBongkaranInterface
{
    public function belumTransfer()
    {
        return Bongkaran::with('user')->where('status', 2)->get();
    }

    public function SimpanTransfer($request)
    {
        $data = strjson($request->data);
        
        foreach ($data as $item) {
            $transfer_bongkaran = transfer_bongkaran::find($item['id_bongkaran']);
            $transfer_bongkaran->status = 3;
            $transfer_bongkaran->update();

            // DB::table('bongkaran')
            //     ->where('id_bongkaran', $item['id_bongkaran'])
            //     ->update(['status' => 3]);
        }
    }

    public function SudahTransfer($tanggal)
    {   
        $awal = $tanggal. ' 00:00:00';
        $akhir = $tanggal . ' 23:59:59';
        $transfer_bongkaran = transfer_bongkaran::with('user')->where('status', 3)->whereBetween('waktu_transfer', [$awal, $akhir])->get();
        return $transfer_bongkaran;
    }

    public function UpdateTransfer($id)
    {
        $transfer_bongkaran = transfer_bongkaran::find($id);
        $transfer_bongkaran->status = 2;
        $transfer_bongkaran->update();
    }
}
