<?php

namespace App\repositories;

use App\models\input_bongkaran;

class InputBongkaranRepository implements InputBongkaranInterface
{
    public function sementara($operator)
    {
        return input_bongkaran::where('operator', $operator)->where('status', 1)->get();
    }

    public function selesai($awal, $akhir)
    {
        return input_bongkaran::whereBetween('input_bongkaran.created_at', [$awal, $akhir])->get();
     }

    public function ambilinput_bongkaran()
    {
        return response()->json(input_bongkaran::all());
     }

    public function store($request)
    {
        $input_bongkaran = new input_bongkaran;
        $input_bongkaran->pol = $request->pol;
        $input_bongkaran->lot = $request->lot;
        $input_bongkaran->shift = $request->shift;
        $input_bongkaran->jumlah = $request->good + $request->reject;
        $input_bongkaran->good = $request->good;
        $input_bongkaran->reject = $request->reject;
        $input_bongkaran->operator = $request->operator;
        $input_bongkaran->status = 1;
        $input_bongkaran->save();         

        return $input_bongkaran;
    }

    public function update($request, $id)
    {
        //dd($request);
        $input_bongkaran = input_bongkaran::find($request->id_input);
        $input_bongkaran->pol = $request->pol;
        $input_bongkaran->lot = $request->lot;
        $input_bongkaran->shift = $request->shift;
        $input_bongkaran->jumlah = $request->good + $request->reject;
        $input_bongkaran->good = $request->good;
        $input_bongkaran->reject = $request->reject;
        $input_bongkaran->operator = $request->operator;
        $input_bongkaran->status = 1;
        $input_bongkaran->update();

        return $input_bongkaran;
    }

    public function destroy($id)
    {
        $input_bongkaran = input_bongkaran::find($id);
        $input_bongkaran->delete();
    }

    public function DataBongkaran($cari)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $data = input_bongkaran::where('status', 1)->where('lot', 'like', '%' . $search . '%')->paginate(5);
       
        return $data;
    }
}
