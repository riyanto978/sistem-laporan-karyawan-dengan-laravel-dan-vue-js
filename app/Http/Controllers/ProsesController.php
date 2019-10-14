<?php

namespace App\Http\Controllers;

use App\repositories\ProsesInterface;
use Illuminate\Http\Request;


class ProsesController extends Controller
{
    protected $proses;

    public function __construct(ProsesInterface $proses)
    {
        $this->proses = $proses;
    }
    public function index()
    {
        $proses = $this->proses->index();
        return response()->json($proses);
    }

    public function ambilproses()
    {
        return response()->json($this->proses->ambilproses());

    }

    public function store(Request $request)
    {
        $proses = $this->proses->store($request);

        return response()->json($proses);
    }

    public function update(Request $request, $id)
    {
        $proses =  $this->proses->update($request, $id);
        return response()->json($proses);
    }


    public function destroy($id)
    {
        $this->proses->destroy($id);
    }
}
