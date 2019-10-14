<?php

namespace App\repositories;

use App\models\Counter;
use DB;
use App\models\Pol;

class CounterRepository implements CounterInterface
{
    public function dataCounter($id_pol)
    {
        $counter = Counter::select('*', 'counter.updated_at')->join('pol', 'pol.id_pol', 'counter.id_pol')->where('counter.id_pol', $id_pol)->paginate(5);
        $total = Counter::where('id_pol', $id_pol)->sum('jumlah');
        return [
            'counter' => $counter,
            'total' => $total
        ];
    }

    public function store( $request)
    {
        $total_counter = Counter::where('id_pol', $request->id_pol)->sum('jumlah');
        $sisa = $total_counter % $request->per_iner;
        //dd($sisa);
        if ($sisa == 0) {
            $iner_awal = ceil($total_counter / $request->per_iner) + 1;
        } else {
            $iner_awal = ceil($total_counter / $request->per_iner);
        }
        $iner_akhir = ceil(($total_counter + $request->jumlah) / $request->per_iner);
        $wip = ($total_counter + $request->jumlah) % $request->per_iner;

        $counter = new Counter;
        $counter->id_pol = $request->id_pol;
        $counter->lot = $request->lot;
        $counter->jumlah = $request->jumlah;
        $counter->operator = $request->operator;
        $counter->shift = $request->shift;
        $counter->iner_awal = $iner_awal;
        $counter->iner_akhir = $iner_akhir;
        $counter->reject_plastic = $request->reject_plastic;
        $counter->wip = $wip;
        $counter->save();

        $pol = Pol::find($counter->id_pol);
        $cek = counter::where('id_pol', $counter->id_pol)->sum('jumlah');
        if ($cek >= $pol->jumlah_order) {
            $pol->status = 1;
            $pol->update();
        }
    }

    public function update( $request, $id)
    {
        $counter = Counter::find($id);
        $counter->lot = $request->lot;
        $counter->jumlah = $request->jumlah;
        $counter->operator = $request->operator;
        $counter->reject_plastic = $request->reject_plastic;
        $counter->shift = $request->shift;
        $counter->update();
        $pol = Pol::find($counter->id_pol);
        $cek = counter::where('id_pol', $counter->id_pol)->sum('jumlah');
        if ($cek >= $pol->jumlah_order) {
            $pol->status = 1;
            $pol->update();
        }
        $this->ubahIner($id, $request->id_pol, $request->per_iner);
    }

    public function ubahIner($id, $id_pol, $per_iner, $id_counter = 'null')
    {
        $last = Counter::where('id_pol', $id_pol)->select('id_counter')->orderBy('id_counter', 'desc')->first();

        $cari = Counter::where('id_pol', $id_pol)->whereBetween('id_counter', [$id, $last->id_counter])->get();
        DB::table('counter')->where('id_pol', $id_pol)->whereBetween('id_counter', [$id, $last->id_counter])->delete();
        foreach ($cari as $data) {
            if ($data->id_counter != $id_counter) {
                $total_counter = Counter::where('id_pol', $id_pol)->sum('jumlah');
                $sisa = $total_counter % $per_iner;
                //dd($sisa);
                if ($sisa == 0) {
                    $iner_awal = ($total_counter / $per_iner) + 1;
                } else {
                    $iner_awal = ceil($total_counter / $per_iner);
                }
                $iner_akhir = ceil(($total_counter + $data->jumlah) / $per_iner);
                $wip = ($total_counter + $data->jumlah) % $per_iner;

                $counter = new Counter;
                $counter->id_pol = $id_pol;
                $counter->lot = $data->lot;
                $counter->jumlah = $data->jumlah;
                $counter->operator = $data->operator;
                $counter->shift = $data->shift;
                $counter->iner_awal = $iner_awal;
                $counter->iner_akhir = $iner_akhir;
                $counter->reject_plastic = $data->reject_plastic;
                $counter->wip = $wip;
                $counter->save();
            }
        }
    }

    public function destroy($id)
    {
        $counter = Counter::join('pol', 'pol.id_pol', 'counter.id_pol')->where('id_counter', $id)->first();
        $this->ubahIner($id, $counter->id_pol, $counter->per_iner, $id);
    }
}
