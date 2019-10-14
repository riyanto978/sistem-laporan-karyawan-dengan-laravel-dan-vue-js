<?php

namespace App\repositories;

use Illuminate\Http\Request;
use App\models\Pol;
use App\repositories\PolInterface;
use DB;

class PolRepository implements PolInterface
{

    public function index()
    {
        return Proses::all();
    }

    public function ambilpolktp($tipe)
    {
        $pol = Pol::join('periode', 'pol.id_periode', 'periode.id_periode')->where('tipe', 'ktp')->where('proses', $tipe)->get();
        return $pol;
    }

    public function dataReguler($tipe, $cari, $status)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $pol = Pol::where('tipe', $tipe)
            ->Where(function ($query) use ($search) {
                $query->where('kode_pol', 'like', '%' . $search . '%')
                    ->orWhere('nama_pol', 'like', '%' . $search . '%');
            })
            ->where('status', $status)
            ->orderBy('updated_at', 'desc')->paginate(5);
        return $pol;
    }

    public function dataTransfer($cari)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $pol = Pol::Where(
            function ($query) {
                $query->where('tipe', 'counter')
                    ->orWhere('tipe', 'reguler')
                    ->orWhere('tipe', 'memo');
            }
        )
            ->Where(function ($query) use ($search) {
                $query->where('kode_pol', 'like', '%' . $search . '%')
                    ->orWhere('nama_pol', 'like', '%' . $search . '%');
            })
            ->orderBy('updated_at', 'desc')->paginate(5);
        return $pol;
    }

    public function ambilpol($tahun, $tipe, $cari, $status)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }

        $pol = Pol::where('tahun', $tahun)->where('tipe', $tipe)
            ->Where(function ($query) use ($search) {
                $query->where('kode_pol', 'like', '%' . $search . '%')
                    ->orWhere('nama_pol', 'like', '%' . $search . '%');
            })
            ->where('status', $status)
            ->orderBy('updated_at', 'desc')->paginate(10);
        return $pol;
    }

    public function searchPol($cari)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }

        $pol = Pol::Where(function ($query) use ($search) {
                $query->where('kode_pol', 'like', '%' . $search . '%')
                    ->orWhere('nama_pol', 'like', '%' . $search . '%');
            })
            ->orderBy('updated_at', 'desc')->limit(10)->get();
        return $pol;
    }

    public function store( $request)
    {

        $data = strjson($request->proses);
        if (is_array($data)) {
            $jml = count($data);
        } else {
            $jml = 0;
        }

        $pol = new Pol();
        $pol->kode_pol = $request->kode_pol;
        $pol->tahun = $request->tahun;
        $pol->nama_pol = $request->nama_pol;
        $pol->jumlah_order = $request->jumlah_order;
        $pol->per_iner = $request->per_iner;
        $pol->tipe =   $request->tipe;
        $pol->proses = $request->proses;
        $pol->jml_proses = $jml;
        $pol->status = $request->status;
        $pol->save();
    }

    public function qc($ipqc)
    {

        $cari = json_decode(file_get_contents('http://' . $ipqc . '/qc/data_pol.php'), true);
        $count_awal = Pol::all()->count();
        foreach ($cari as $data) {

            if (Pol::where('kode_pol', '=', $data['kode_pol'])->where('tahun', $data['tahun'])->count() == 0) {
                Pol::insert($data);
            }
        }

        $count_akhir = pol::all()->count();

        return [
            "total" => $count_akhir - $count_awal
        ];
    }

    public function ambilDataPol()
    {
        $data = DB::connection('cardtech')->table("si_pd_monitor")->paginate(10);
        dd($data);
    }

    public function update( $request, $id)
    {
        $data = strjson($request->proses);
        if (is_array($data)) {
            $jml = count($data);
        } else {
            $jml = 0;
        }

        $pol = Pol::find($id);
        $pol->kode_pol = $request->kode_pol;
        $pol->tahun = $request->tahun;
        $pol->nama_pol = $request->nama_pol;
        $pol->jumlah_order = $request->jumlah_order;
        $pol->per_iner = $request->per_iner;
        $pol->tipe =   $request->tipe;
        $pol->proses = $request->proses;
        $pol->id_periode = $request->id_periode;
        $pol->tipe_chip = $request->tipe_chip;
        $pol->jml_proses = $jml;
        $pol->status = $request->status;
        $pol->update();

        return $pol;
    }

    public function destroy($id)
    {
        $pol = Pol::find($id);
        $pol->delete();
    }

    public function urgent( $request, $id)
    {
        $pol = Pol::find($id);
        if ($pol->urgent == 0) {
            $pol->urgent = 1;
        } else {
            $pol->urgent = 0;
        }

        $pol->update();
        return $pol;
    }

    public function ambilurgent()
    {
        $pol = Pol::where('urgent', 1)->get();
        return $pol;
    }
}
