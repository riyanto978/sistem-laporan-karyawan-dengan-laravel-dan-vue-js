<?php

namespace App\repositories;

use DB;
use App\models\Transfer;
use App\models\Pol;

class TransferRepository implements TransferInterface
{
    public function DataTransfer($tipe, $id_pol)
    {
        $belum = $this->belumTransfer($tipe, $id_pol);
        $sudah = $this->sudahTransfer($id_pol);
        return ['belum' => $belum, 'sudah' => $sudah];
    }

    public function belumTransfer($tipe, $id_pol)
    {
        $pol = Pol::find($id_pol);
        $counter = [];

        if ($tipe == 'counter') {

            $jumlah = DB::table($tipe)->where('id_pol', $id_pol)->sum('jumlah');
            $total = floor($jumlah / $pol->per_iner);
            for ($i = 1; $i <= $total; $i++) {
                $sum = DB::table('transfer')->where('id_pol', $id_pol)->where('iner', $i)->sum('isi');
                if ($sum < $pol->per_iner) {
                    $row = [];
                    $row['iner'] = $i.' '. $this->search_urut($id_pol, $i);
                    $row['isi'] = $pol->per_iner-$sum;
                    $counter[] = $row;
                }
            }
        } else {
            $reguler = DB::table('reguler')->where('id_pol', $id_pol)->where('proses_ke', $pol->jml_proses)->where('status', '>', 1)->get();
            foreach ($reguler as $data) {
                $sum = DB::table('transfer')->where('id_pol', $id_pol)->where('iner', $data->iner)->sum('isi');
                if ($sum < $pol->per_iner) {
                    $row = [];
                    $row['iner'] = $data->iner.' '. $this->search_urut($id_pol, $data->iner);
                    $row['isi'] = $data->isi-$sum;
                    $counter[] = $row;
                }
            }
        }
        return $counter;
    }

    public function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['id_transfer'] === $id) {
                return $key;
            }
        }
        return null;
    }

    public function search_urut($id_pol, $i)
    {
        $urut = DB::table('transfer')->where('id_pol', $id_pol)->where('iner', $i)->count();
        if ($urut == 0) {
            $alphabet = '';
        } else {
            $alphabet = $this->range($urut);
        }

        return $alphabet;
    }

    public function search_urut_sudah($id_pol, $id_transfer,$iner)
    {
        $data = Transfer::where('id_pol', $id_pol)->where('iner', $iner)->get();
        $urut = $this->searchForId($id_transfer, $data->toArray());
        if (count($data) == 0) {
            $alphabet = '';
        } else {
            $alphabet = $this->range($urut);
        }

        return $alphabet;
    }

    public function sudahTransfer($id_pol)
    {
        $counter = [];
        $transfer = Transfer::where('id_pol', $id_pol)->where('status', 1)->get();
        foreach ($transfer as $data) {
            $row = [];
            $row['id_transfer'] = $data->id_transfer;
            $row['iner'] = $data->iner.' '.$this->search_urut_sudah($id_pol, $data->id_transfer,$data->iner);
            $row['isi'] = $data->isi;
            $counter[] = $row;
        }
        return $counter;
    }

    public function range($range)
    {
        $data = range('A','Z');
        return $data[$range];
    }

    public function packingTransfer($id_pol)
    {
        //tipe counter
        $pol = Pol::find($id_pol);
        $counter = [];

        $transfer = Transfer::where('id_pol', $id_pol)->where('status', 1)->get();
        foreach ($transfer as $data) {
            $row = [];
            $row['id_transfer'] = $data->id_transfer;
            $row['iner'] = $data->iner . ' ' . $this->search_urut_sudah($id_pol, $data->id_transfer, $data->iner);
            $row['isi'] = $data->isi;
            $row['status'] = $data->status;
            $row['saved'] = true;
            $counter[] = $row;
        }
        $transfer = Transfer::where('id_pol', $id_pol)->where('status', 2)->get();
        foreach ($transfer as $data) {
            $row = [];
            $row['id_transfer'] = $data->id_transfer;
            $row['iner'] = $data->iner . ' ' . $this->search_urut_sudah($id_pol, $data->id_transfer, $data->iner);
            $row['isi'] = $data->isi;
            $row['status'] = $data->status;
            $row['saved'] = true;
            $counter[] = $row;
        }
        return $counter;
    }

    public function simpanBelum($request)
    {
        $transfer = Transfer::find($request->id_transfer);
        $transfer->delete();

        $pol = Pol::find($request->id_pol);

        return $this->belumTransfer($pol->tipe, $pol->id_pol);
    }

    public function simpanSudah($request)
    {
        $sum = DB::table('transfer')->where('id_pol', $request->id_pol)->where('iner', $request->iner)->sum('isi');
        $pol = Pol::find($request->id_pol);

        if($request->isi > ($pol->per_iner - $sum)){
            $isi = $pol->per_iner -$sum;
        }else{
            $isi = $request->isi;
        }

        $transfer = new Transfer;
        $transfer->id_pol = $request->id_pol;
        $transfer->iner = $request->iner;
        $transfer->shift = $request->shift;
        $transfer->isi = $isi;
        $transfer->operator = $request->operator;
        $transfer->status = 1;
        $transfer->save();

        $row = [];
        $row['id_transfer'] = $transfer->id_transfer;
        $row['iner'] = $transfer->iner;
        $row['isi'] = $transfer->isi;
        return $row;
    }

    public function belumPacking($request)
    {
        $belum = strjson($request->belum);
        foreach ($belum as $item) {
            DB::table('transfer')
                ->where('id_transfer', $item['id_transfer'])
                ->update([
                    'status' => 1,
                    'penerima' => $request->operator
                ]);
        }
    }

    public function sudahPacking($request)
    {

        $sudah = strjson($request->sudah);
        foreach ($sudah as $item) {
            $transfer = Transfer::find($item['id_transfer']);
            $transfer->status = 2;
            $transfer->penerima = $request->operator;
            $transfer->update();
        }
    }

    public function History($id_pol, $cari)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $data = Transfer::where('id_pol', $id_pol)->where('iner', 'like', '%' . $search . '%')->with('pol')->paginate(5);
        $transfer = [];
        foreach ($data as $value) {
            $row = [];
            $row['id_transfer'] = $value->id_transfer;
            $row['nama_pol'] = $value->pol->nama_pol;
            $row['kode_pol'] = $value->pol->kode_pol;
            $row['iner'] = $value->iner . ' ' . $this->search_urut_sudah($id_pol, $value->id_transfer, $value->iner);
            $row['operator'] = $value->operator;
            $row['status'] = $value->status;
            $row['penerima'] = $value->penerima;
            $row['created_at'] = date($value->created_at);
            $row['updated_at'] = date($value->updated_at);
            $transfer[] = $row;

        }

        return ['transfer'=> $transfer,'total'=> $data];
    }
}
