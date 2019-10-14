<?php

namespace App\Http\Controllers;

use App\repositories\InputBongkaranInterface;
use Illuminate\Http\Request;


class InputBongkaranController extends Controller
{

    protected $input;

    public function __construct(InputBongkaranInterface $input)
    {
        $this->input = $input;
    }

    public function sementara($operator)
    {
        $input_bongkaran = $this->input->sementara($operator);
        return response()->json($input_bongkaran);
    }
    
    public function selesai(InputBongkaranInterface $input,$operator, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";        
        $input_bongkaran = $this->input->selesai($awal,$akhir);
        return response()->json($input_bongkaran);
    }

    public function ambilinput_bongkaran()
    {
        return $this->input->ambilinput_bongkaran();
    }
    
    public function store(Request $request)
    {
        $input_bongkaran = $this->input->store($request);
        return response()->json($input_bongkaran);
    }

    public function update(Request $request, $id)
    {
        $input_bongkaran = $this->input->update($request, $id);
        return response()->json($input_bongkaran);
    }

    public function destroy($id)
    {
       $this->input->destroy($id);
    }

    public function DataBongkaran($cari)
    {
        $data = $this->input->DataBongkaran($cari);
        return response()->json($data);
    }
}
