<?php

namespace App\repositories;

use App\models\Lot;
use DB;

class LotRepository implements LotInterface
{
    public function dataLot($id_pol)
    {
        $lot = Lot::join('users', 'users.id', 'lot.operator')->where('id_pol', $id_pol)->orderBy('id_lot', 'desc')->get();
        return $lot;
    }

    public function LaporanLot($id_pol)
    {
        $lot = DB::table('lot')->leftJoin('reguler', 'reguler.id_lot', 'lot.id_lot')->where('lot.id_pol', $id_pol)
            ->select('*', 'lot.id_lot', 'lot.id_pol', DB::raw("sum(isi) as total"))
            ->groupBy('lot.id_lot')
            ->get();

        $arr = [];
        foreach ($lot as $data) {
            if ($data->total < $data->jumlah) {
                $arr[] = $data;
            }
        }

        return $arr;
    }

    public function store( $request)
    {
        $lot = new Lot();
        $lot->id_pol = $request->id_pol;
        $lot->kd_lot = $request->kd_lot;
        $lot->jumlah = $request->jumlah;
        $lot->reject = $request->reject;
        $lot->good = $request->good;
        $lot->operator =   $request->operator;
        $lot->no_awal = $request->no_awal;
        $lot->save();
        return $lot;
    }

    public function update( $request, $id)
    {
        $lot = Lot::find($id);
        if ($lot->reject > $request->jumlah) {
            return '404';
        } else {
            $lot->kd_lot = $request->kd_lot;
            $lot->jumlah = $request->jumlah;
            $lot->good = $request->jumlah - $lot->reject;
            $lot->operator = $request->operator;
            $lot->no_awal = $request->no_awal;
            $lot->update();
            return $lot;
        }
    }

    public function destroy($id)
    {
        $lot = Lot::find($id);
        $lot->delete();
    }
}
