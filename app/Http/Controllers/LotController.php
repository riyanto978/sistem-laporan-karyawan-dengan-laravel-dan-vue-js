<?php

namespace App\Http\Controllers;

use App\repositories\LotInterface;
use Illuminate\Http\Request;

class LotController extends Controller
{
    protected $lot;

    public function __construct(LotInterface $lot)
    {
        $this->lot = $lot;
    }

    public function dataLot($id_pol)
    {
        $lot = $this->lot->dataLot($id_pol);
        return response()->json($lot);
    }

    public function LaporanLot($id_pol)
    {
        $arr = $this->lot->LaporanLot($id_pol);
        return response()->json($arr);
    }

    public function store(Request $request)
    {
        $lot = $this->lot->store($request);
        return response()->json($lot);
    }

    public function update(Request $request, $id)
    {
        $lot = $this->lot->update($request, $id);
        if ($lot == '404') {
            return response()->status('404');
        }

        return response()->json($lot);
    }

    public function destroy($id)
    {
        $this->lot->destroy($id);
    }
}
