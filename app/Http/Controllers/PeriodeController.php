<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Periode;

class PeriodeController extends Controller
{

    public function index()
    {
        $periode = Periode::all();
        return response()->json($periode);
    }


    public function store(Request $request)
    {
        $periode = new Periode;
        $periode->nama_periode = $request->nama_periode;
        $periode->jmlorder = $request->jmlorder;
        $periode->save();

        return response()->json($periode);
    }

    public function update(Request $request, $id)
    {
        $periode =  Periode::find($id);
        $periode->nama_periode = $request->nama_periode;
        $periode->jmlorder = $request->jmlorder;
        $periode->update();

        return response()->json($periode);
    }


    public function destroy($id)
    {
        $periode = Periode::find($id);
        $periode->delete();
    }
}
