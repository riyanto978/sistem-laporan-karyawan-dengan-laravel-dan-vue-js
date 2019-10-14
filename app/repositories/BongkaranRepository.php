<?php

namespace App\repositories;

use App\models\Bongkaran;
use App\models\input_bongkaran;

class BongkaranRepository implements BongkaranInterface
{
    public function sementara($operator)
    {
        $Bongkaran = Bongkaran::where('operator', $operator)->where('status', 1)->get();
        return $Bongkaran;
    }

    public function selesai($operator, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";
        $Bongkaran = Bongkaran::where('status', '>', 1)->where('operator', $operator)->whereBetween('created_at', [$awal, $akhir])->get();
        return $Bongkaran;
    }

    public function ambilBongkaran()
    {
        return Bongkaran::all();
    }

    public function updateInputBongkaran($id, $status){
        $input_bongkaran = input_bongkaran::find($id);
        $input_bongkaran->status = $status;
        $input_bongkaran->update();

    }

    public function store($request)
    {        
        $Bongkaran = new Bongkaran;
        $Bongkaran->id_input = $request->id_input;
        $Bongkaran->pol = $request->pol;
        $Bongkaran->lot = $request->lot;
        $Bongkaran->shift = $request->shift;
        $Bongkaran->jumlah = $request->good;
        $Bongkaran->good = 0;
        $Bongkaran->reject = 0;
        $Bongkaran->operator = $request->operator;
        $Bongkaran->status = 1;
        $Bongkaran->save();
        return $Bongkaran;
    }

    public function update($request, $id)
    {
        $Bongkaran = Bongkaran::find($request->id_bongkaran);
        $Bongkaran->pol = $request->pol;
        $Bongkaran->lot = $request->lot;
        $Bongkaran->shift = $request->shift;
        $Bongkaran->jumlah = $request->jumlah;
        $Bongkaran->good = $request->jumlah - $request->reject;
        $Bongkaran->reject = $request->reject;
        $Bongkaran->operator = $request->operator;
        $Bongkaran->status = 2;
        $Bongkaran->update();
        return $Bongkaran;
    }


    public function destroy($id)
    {
        $Bongkaran = Bongkaran::find($id);
            $this->updateInputBongkaran($Bongkaran->id_input, 1);
        $Bongkaran->delete();
    }    

    public function BongkaranDataPreperso($cari)
    {

        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $data = Bongkaran::with('pol')->where('status', 2)->where('id_Bongkaran', 'like', '%' . $search . '%')->paginate(5);
        return $data;
    }
}
