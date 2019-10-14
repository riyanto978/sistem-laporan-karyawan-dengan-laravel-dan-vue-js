<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kartu_sam;

class KartuSamController extends Controller
{    
 
    public function index()
    { 
        $kartu_sam = Kartu_sam::all();
        return response()->json($kartu_sam);
    }

    public function ambilkartu_sam()
    {
        return response()->json(Kartu_sam::all());
    }
    
    public function store(Request $request)
    {
        $kartu_sam = new Kartu_sam;
        $kartu_sam->nama = $request->nama;
        $kartu_sam->isi = $request->isi;
        $kartu_sam->save();

        return response()->json($kartu_sam);
    }

    public function update(Request $request, $id)
    {
        $kartu_sam =  Kartu_sam::find($id);
        $kartu_sam->nama = $request->nama;
        $kartu_sam->isi = $request->isi;        
        $kartu_sam->update();

        return response()->json($kartu_sam);
    }


    public function destroy($id)
    {
        $kartu_sam = Kartu_sam::find($id);
        $kartu_sam->delete();
    }
}
