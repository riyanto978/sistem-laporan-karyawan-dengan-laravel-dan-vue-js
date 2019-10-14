<?php

namespace App\Http\Controllers;

use App\repositories\LaporanInterface;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected $laporan;

    public function __construct(LaporanInterface $laporan)
    {
        $this->laporan = $laporan;
    }

    public function inerReguler($id_pol)
    {
        $data = $this->laporan->inerReguler($id_pol);
        return response()->json($data);
    }

    public function inerRegulerke($ke, $id_pol)
    {
        $reguler = $this->laporan->inerRegulerke($ke, $id_pol);
        return response()->json($reguler);
    }


    public function allLaporan($id_pol, $proses_ke, $operator, $status, $tanggal)
    {
        $data = $this->laporan->allLaporan($id_pol, $proses_ke, $operator, $status, $tanggal);
        return response()->json($data);
    }

    public function allTanggal($id_pol, $proses_ke, $operator)
    {
        $data = $this->laporan->allTanggal($id_pol, $proses_ke, $operator);
        $row = [];
        foreach ($data as $item) {
            $row[] = $item->tanggal;
        }
       return response()->json($row);
    }

    public function store(Request $request)
    {
        $data = $this->laporan->store($request);
        return response()->json($data[0]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->laporan->update($request, $id);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $this->laporan->destroy($id);
    }
}
