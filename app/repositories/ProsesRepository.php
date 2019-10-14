<?php

namespace App\repositories;

use App\models\Proses;

class ProsesRepository implements ProsesInterface
{

    public function index()
    {
        $proses = Proses::where('nama_proses', '<>', 'counter')->get();
        return $proses;
    }

    public function ambilproses()
    {
        return Proses::all();
    }

    public function store( $request)
    {
        $proses = new Proses;
        $proses->nama_proses = $request->nama_proses;
        $proses->group_laporan = $request->group_laporan;
        $proses->tipe = $request->tipe;
        $proses->save();

        return $proses;
    }

    public function update( $request, $id)
    {
        $proses =  Proses::find($id);
        $proses->nama_proses = $request->nama_proses;
        $proses->group_laporan = $request->group_laporan;
        $proses->tipe = $request->tipe;
        $proses->update();

        return $proses;
    }

    public function destroy($id)
    {
        $proses = Proses::find($id);
        $proses->delete();
    }
}
