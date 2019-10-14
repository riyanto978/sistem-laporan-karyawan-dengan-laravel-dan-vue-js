<?php

namespace App\Http\Controllers;

use App\repositories\BongkaranInterface;
use Illuminate\Http\Request;


class bongkaranController extends Controller
{   
    protected $Bongkaran;

    public function __construct(BongkaranInterface $Bongkaran)
    {
        $this->Bongkaran = $Bongkaran;
    }

    public function sementara($operator)
    {
        $Bongkaran = $this->Bongkaran->sementara($operator);
        return response()->json($Bongkaran);
    }

    public function selesai($operator, $tanggal)
    {        
        
        $Bongkaran = $this->Bongkaran->selesai($operator,$tanggal);
        return response()->json($Bongkaran);
    }

    public function ambilBongkaran()
    {

        return response()->json($this->Bongkaran->ambilBongkaran());
    }

    public function store(Request $request)
    {
        $this->Bongkaran->updateInputBongkaran($request->id_input, 2);
        $Bongkaran = $this->Bongkaran->store($request);
        return response()->json($Bongkaran);
    }

    public function update(Request $request, $id)
    {
        $Bongkaran = $this->Bongkaran->update($request, $id);
        return response()->json($Bongkaran);
    }


    public function destroy($id)
    {
        $Bongkaran = $this->Bongkaran->destroy($id);
    }

    public function BongkaranDataPreperso($cari)
    {        
        $data = $this->Bongkaran->BongkaranDataPreperso($cari);
        return response()->json($data);
    }
}
