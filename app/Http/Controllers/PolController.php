<?php

namespace App\Http\Controllers;

use App\repositories\PolInterface;
use Illuminate\Http\Request;


class PolController extends Controller
{
    protected $pol;

    public function __construct(PolInterface $pol)
    {
        $this->pol = $pol;
    }

    public function index()
    {
        return response()->json($this->pol->index());
    }

    public function ambilpolktp($tipe)
    {
        $pol = $this->pol->ambilpolktp($tipe);
        return response()->json($pol);
    }

    public function dataReguler($tipe, $cari, $status)
    {
        $pol = $this->pol->dataReguler($tipe, $cari, $status);
        return response()->json($pol);
    }

    public function dataTransfer($cari)
    {
        $pol = $this->pol->dataTransfer($cari);
        return response()->json($pol);
    }

    public function ambilpol($tahun, $tipe, $cari, $status)
    {
        $pol = $this->pol->ambilpol($tahun, $tipe, $cari, $status);
        return response()->json($pol);
    }

    public function searchPol($cari)
    {
        $pol = $this->pol->searchPol($cari);
        return response()->json($pol);
    }

    public function store(Request $request)
    {

        $this->pol->store($request);
    }

    public function qc($ipqc)
    {
        $total = $this->pol->qc($ipqc);

        return response()->json([
            "total" => $total
        ]);
    }

    public function ambilDataPol()
    {
        $data = DB::connection('cardtech')->table("si_pd_monitor")->paginate(10);
        dd($data);
    }

    public function update(Request $request, $id)
    {
        $pol = $this->pol->update($request, $id);

        return response()->json($pol);
    }

    public function destroy($id)
    {
        $this->pol->destroy($id);
    }

    public function urgent(Request $request, $id)
    {
        $pol = $this->pol->urgent($request, $id);

        return response()->json($pol);
    }

    public function ambilurgent()
    {
        $pol = $this->pol->ambilurgent();
        return response()->json($pol);
    }
}
