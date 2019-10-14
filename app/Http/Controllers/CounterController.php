<?php

namespace App\Http\Controllers;

use App\repositories\CounterInterface;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    protected $counter;

    public function __construct(CounterInterface $counter)
    {
        $this->counter= $counter;
    }
    public function dataCounter($id_pol)
    {
        $data = $this->counter->dataCounter($id_pol);
        return response()->json([
            'data' =>$data['counter'],
            'total' =>$data['total']
            ]);
    }

    public function store(Request $request)
    {
        $this->counter->store($request);
    }

    public function update(Request $request, $id)
    {
        $this->counter->update($request, $id);
    }

    public function destroy($id)
    {
        $this->counter->destroy($id);
    }
}
