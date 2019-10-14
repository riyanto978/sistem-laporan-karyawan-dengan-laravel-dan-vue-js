<?php

namespace App\repositories;

use App\models\Pol;
use App\models\Lot;
use App\models\Reguler;
use DB;
use App\models\Counter;
use App\models\Transfer;

class DataLaporanRepository implements DataLaporanInterface
{
    public function resumeHome($operator, $tanggal)
    {
        $user = DB::table('users')->where('username', $operator)->first();
        //dd($user);
        $operator = $user->id;

        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";

        $data = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->where('operator', $operator)
            ->whereBetween('reguler.created_at', [$awal, $akhir])->groupBy('pol.id_pol')->get();
        // dd($data);
        $arr = [];
        foreach ($data as $item) {
            $lap = Reguler::select('*', 'reguler.created_at', 'reguler.updated_at')
                ->join('proses', 'reguler.id_proses', 'proses.id_proses')
                ->where('id_pol', $item->id_pol)->where('operator', $operator)
                ->whereBetween('reguler.created_at', [$awal, $akhir])->get();

            $row = array();
            $row['pol'] = $item->kode_pol;
            $row['nama_pol'] = $item->nama_pol;
            $row['data'] = $lap;

            $arr[] = $row;
        }

        return $arr;
    }

    public function resumeMonitoring($operator, $tanggal, $id_proses, $id_pol)
    {
        if ($operator != 'all') {
            $user = DB::table('users')->where('username', $operator)->first();
            $op = $user->id;
        } else {
            $op = '';
        }

        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";

        $data = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->join('proses', 'reguler.id_proses', 'proses.id_proses')
            ->when($operator != 'all', function ($query) use ($op) {
                return $query->where('operator', $op);
            })
            ->where('reguler.id_proses', $id_proses)
            ->where('reguler.id_pol', $id_pol)
            ->whereBetween('reguler.created_at', [$awal, $akhir])->groupBy('pol.id_pol')->get();

        $arr = [];
        foreach ($data as $item) {
            $lap = Reguler::select('*', 'reguler.created_at', 'reguler.updated_at')
                ->join('proses', 'reguler.id_proses', 'proses.id_proses')
                ->where('reguler.id_proses', $item->id_proses)
                ->where('reguler.id_pol', $item->id_pol)
                ->when($operator != 'all', function ($query) use ($op) {
                    return $query->where('operator', $op);
                })
                ->whereBetween('reguler.created_at', [$awal, $akhir])->get();

            $row = array();
            $row['pol'] = $item->kode_pol;
            $row['nama_pol'] = $item->nama_pol;
            $row['nama_proses'] = $item->nama_proses;
            $row['data'] = $lap;

            $arr[] = $row;
        }

        return $arr;
    }

    public function resumeTanggal($id, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";

        $data = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->where('reguler.id_pol', $id)
            ->whereBetween('reguler.created_at', [$awal, $akhir])->groupBy('pol.id_pol')->get();
        // dd($data);
        $arr = [];
        foreach ($data as $item) {
            $lap = Reguler::select('*', 'reguler.created_at', 'reguler.updated_at')
                ->join('proses', 'reguler.id_proses', 'proses.id_proses')
                ->where('id_pol', $item->id_pol)
                ->whereBetween('reguler.created_at', [$awal, $akhir])->get();

            $row = array();
            $row['pol'] = $item->kode_pol;
            $row['nama_pol'] = $item->nama_pol;
            $row['data'] = $lap;

            $arr[] = $row;
        }

        return $arr;
    }

    public function resume($shift, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";

        $data = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->where('shift', $shift)
            ->whereBetween('reguler.created_at', [$awal, $akhir])->groupBy('pol.id_pol')->get();

        $counter = DB::table('counter')->join('pol', 'pol.id_pol', 'counter.id_pol')
            ->where('shift', $shift)
            ->whereBetween('counter.created_at', [$awal, $akhir])->groupBy('pol.id_pol')->get();

        $arr = [];
        foreach ($data as $item) {
            $lap = Reguler::select('*', 'reguler.created_at', 'reguler.updated_at')
                ->join('proses', 'reguler.id_proses', 'proses.id_proses')
                ->join('users', 'reguler.operator', 'users.id')
                ->where('id_pol', $item->id_pol)->where('shift', $shift)
                ->whereBetween('reguler.created_at', [$awal, $akhir])->get();

            $row = array();
            $row['pol'] = $item->kode_pol;
            $row['nama_pol'] = $item->nama_pol;
            $row['data'] = $lap;

            $arr[] = $row;
        }
        $arr_counter = [];
        foreach ($counter as $item) {
            $lap = DB::table('counter')
                ->where('id_pol', $item->id_pol)->where('shift', $shift)
                ->whereBetween('counter.created_at', [$awal, $akhir])->get();

            $row = array();
            $row['pol'] = $item->kode_pol;
            $row['nama_pol'] = $item->nama_pol;
            $row['data'] = $lap;

            $arr_counter[] = $row;
        }

        return ['arr' => $arr, 'arr_counter' => $arr_counter];
    }

    public function monitoring($shift, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";

        //$data = DB::select("select reguler.id_proses,sum(reguler.dead) as dead,sum(reguler.chip_lemah) as lemah,sum(reguler.chip_error) as error,proses.nama_proses as proses,sum(reguler.isi) as total,pol.nama_pol,pol.kode_pol from reguler INNER JOIN pol on reguler.id_pol = pol.id_pol Inner join proses on proses.id_proses = reguler.id_proses where shift=? and reguler.created_at between ? and ?  and reguler.status <> '1' group by reguler.id_proses,reguler.id_pol order By pol.kode_pol", [$shift, $awal, $akhir]);
        $data = DB::table('reguler')
            ->select('reguler.id_proses', DB::raw(' pol.jumlah_order as jmlorder,reguler.id_pol,sum(reguler.dead) as dead,sum(reguler.chip_lemah) as lemah,sum(reguler.chip_error) as error,proses.nama_proses as proses,sum(reguler.isi) as total,pol.nama_pol,pol.kode_pol'))
            ->join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->join('proses', 'proses.id_proses', 'reguler.id_proses')
            ->when($shift != 'all', function ($query) use ($shift) {
                return $query->where('shift', $shift);
            })
            ->where('reguler.status', '>', 1)
            ->where('laporan_tipe', 'reguler')
            ->whereBetween('reguler.created_at', [$awal, $akhir])
            ->groupBy('reguler.id_proses', 'reguler.id_pol')
            ->get();
        $monitoring = [];
        foreach ($data as $cari) {
            $terproses = DB::table('reguler')->where('id_pol', $cari->id_pol)->where('id_proses', $cari->id_proses)->sum('isi');
            $cari->terproses = $terproses;
            $monitoring[] = $cari;
        }

        $data = DB::table('reguler')
            ->select('reguler.id_proses', DB::raw(' pol.jumlah_order as jmlorder,reguler.id_pol,sum(reguler.dead) as dead,sum(reguler.chip_lemah) as lemah,sum(reguler.chip_error) as error,proses.nama_proses as proses,sum(reguler.isi) as total,pol.nama_pol,pol.kode_pol'))
            ->join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->join('proses', 'proses.id_proses', 'reguler.id_proses')
            ->when($shift != 'all', function ($query) use ($shift) {
                return $query->where('shift', $shift);
            })
            ->where('reguler.status', '>', 1)
            ->where('laporan_tipe', 'memo')
            ->whereBetween('reguler.created_at', [$awal, $akhir])
            ->groupBy('reguler.id_proses', 'reguler.id_pol')
            ->get();
        $memo = [];
        foreach ($data as $cari) {
            $terproses = DB::table('reguler')->where('id_pol', $cari->id_pol)->where('id_proses', $cari->id_proses)->sum('isi');
            $cari->terproses = $terproses;
            $memo[] = $cari;
        }


        $cari_counter = DB::table('counter')->join('pol', 'pol.id_pol', 'counter.id_pol')
            ->select('counter.id_pol', 'pol.nama_pol', 'pol.kode_pol', DB::raw(' sum(jumlah) as jumlah, min(iner_awal) as iner_awal, max(iner_akhir) as iner_akhir, sum(reject_plastic) as reject_plastic'))
            ->when($shift != 'all', function ($query) use ($shift) {
                return $query->where('shift', $shift);
            })
            ->whereBetween('counter.created_at', [$awal, $akhir])
            ->groupBy('counter.id_pol')
            ->get();
        $counter = [];
        foreach ($cari_counter as $data) {
            $wip = DB::table('counter')->select('wip')
                ->when($shift != 'all', function ($query) use ($shift) {
                    return $query->where('shift', $shift);
                })
                ->whereBetween('counter.created_at', [$awal, $akhir])
                ->where('id_pol', $data->id_pol)->orderBy('id_counter', 'desc')->first();
            $data->wip = $wip;
            $counter[] = $data;
        }


        $transfer = Transfer::with('pol')->when($shift != 'all', function ($query) use ($shift) {
            return $query->where('shift', $shift);
        })
            ->whereBetween('transfer.created_at', [$awal, $akhir])
            ->groupBy('transfer.id_pol')
            ->select('transfer.id_pol', DB::raw('count(id_transfer) as counter, sum(isi) as isi'))
            ->get();

        $total = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->join('proses', 'proses.id_proses', 'reguler.id_proses')
            ->whereBetween('reguler.created_at', [$awal, $akhir])
            ->when($shift != 'all', function ($query) use ($shift) {
                return $query->where('shift', $shift);
            })
            ->where('reguler.status', '>', 1)
            ->groupBy('reguler.id_pol', 'reguler.id_proses')
            ->get();

        $arr = [];
        foreach ($total as $item) {
            //$cari = DB::select(   id_proses =? GROUP by reguler.id_pol, reguler.id_proses, reguler.operator", [$awal, $akhir, $shift, $item->id_pol, $item->id_proses]);
            $cari = DB::table('reguler')
                ->join('users', 'reguler.operator', 'users.id')
                ->select(DB::raw("username as operator, sum(isi) as total, sum(chip_lemah + chip_error + dead + card_body) as reject"))
                ->whereBetween('reguler.created_at', [$awal,  $akhir])
                ->where('reguler.id_pol', $item->id_pol)
                ->where('reguler.status', '>', 1)
                ->where('reguler.id_proses', $item->id_proses)
                ->when($shift != 'all', function ($query) use ($shift) {
                    return $query->where('shift', $shift);
                })
                ->groupBy('reguler.id_pol', 'reguler.id_proses', 'reguler.operator')->get();
            $row = array();

            $row['header'] = $item;
            $row['line']['columns'] = ['operator', 'total', 'reject'];
            $row['line']['rows'] = $cari;

            $arr[] = $row;
        }

        return [
            'monitoring' => $monitoring,
            'memo' => $memo,
            'total' => $arr,
            'transfer' => $transfer,
            'counter' => $counter
        ];
    }

    public function resumepol($id)
    {
        $data = DB::select("select pol.jumlah_order,sum(card_body) as card_body,sum(reguler.dead) as dead,sum(reguler.chip_lemah) as lemah,sum(reguler.chip_error) as error,proses.nama_proses as proses,sum(reguler.isi) as total,pol.nama_pol,pol.kode_pol from reguler INNER JOIN pol on reguler.id_pol = pol.id_pol Inner join proses on proses.id_proses = reguler.id_proses where reguler.id_pol = ? group by reguler.id_proses,reguler.id_pol order By pol.kode_pol", [$id]);
        $lot_count = Lot::where('id_pol', $id)->count();
        $lot_jumlah = Lot::where('id_pol', $id)->sum('jumlah');
        $lot_reject = Lot::where('id_pol', $id)->sum('reject');
        $lot_good = Lot::where('id_pol', $id)->sum('good');

        $total = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
            ->join('proses', 'proses.id_proses', 'reguler.id_proses')
            ->where('reguler.id_pol', $id)
            ->groupBy(DB::raw("date('reguler.created_at')"), 'reguler.id_proses')
            ->get();

        $arr = [];
        foreach ($total as $item) {
            $cari = DB::select("Select date(reguler.created_at) as tanggal, sum(isi) as total, sum(chip_lemah + chip_error + dead + card_body) as reject FROM `reguler` inner join users on reguler.operator = users.id where id_pol =? and id_proses =? GROUP by date(reguler.created_at),reguler.id_pol, reguler.id_proses", [$item->id_pol, $item->id_proses]);
            $row = array();

            $row['header'] = $item;
            $row['line']['columns'] = ['tanggal', 'total', 'reject'];
            $row['line']['rows'] = $cari;

            $arr[] = $row;
        }
        $pol = Pol::find($id);

        $tanggal = DB::select("select date(created_at) as tanggal from reguler where id_pol = ?  group by date(created_at)", [$id]);
        //dd($tanggal);
        $resume = [];
        foreach ($tanggal as $hari) {
            $awal_hari = $hari->tanggal . " 07:00:01";
            $akhir_hari = hari_tambah($hari->tanggal) . " 07:00:00";
            $cari = DB::table('reguler')
                ->select('pol.kode_pol', 'pol.nama_pol', 'pol.jumlah_order', 'nama_proses', 'reguler.shift', DB::raw("sum(reguler.isi) as good, sum(reguler.dead) as dead, sum(reguler.chip_lemah) as chip_lemah,sum(card_body) as card_body,sum(chip_error) as chip_error"))
                ->join('pol', 'pol.id_pol', 'reguler.id_pol')
                ->join('proses', 'proses.id_proses', 'reguler.id_proses')
                ->where('reguler.status', '>', 1)
                ->whereBetween('reguler.created_at', [$awal_hari, $akhir_hari])
                ->where('reguler.id_pol', $id)
                ->groupBy('reguler.id_proses', 'reguler.shift')
                ->get();
            $row = [];
            $row['tanggal'] = $hari->tanggal;
            $row['data'] = $cari;
            $resume[] = $row;
        }

        $proses = strjson($pol->proses);
        $no = 1;
        $list_operator = [];
        foreach ($proses as $data) {
            $row = [];
            $row['header'] = $data['nama_proses'];
            $row['data'] = Reguler::join('users', 'users.id', 'reguler.operator')->where('id_pol', $pol->id_pol)->where('proses_ke', $no)->where('status', '>', 1)->groupBy('operator', 'proses_ke')->select('users.name', DB::raw('sum(isi) as isi'))->get();
            $list_operator[] = $row;
            $no++;
        }

        return [
            'pol' => $pol,
            'list_operator' => $list_operator,
            'data' => $data,
            'resume' => $resume,
            'data_grafik' => $arr,
            'lot_count' => $lot_count,
            'lot_jumlah' => $lot_jumlah,
            'lot_reject' => $lot_reject,
            'lot_good' => $lot_good
        ];
    }

    public function resumecounter($id)
    {
        $pol = Pol::find($id);
        $lot_count = Counter::where('id_pol', $id)->count();
        $lot_jumlah = Counter::where('id_pol', $id)->sum('jumlah');
        $wip = Counter::where('id_pol', $id)->orderBy('id_counter', 'desc')->first();
        return [
            'pol' => $pol,
            'lot_count' => $lot_count,
            'lot_jumlah' => $lot_jumlah,
            'wip' => $wip
        ];
    }

    public function pencapaian($tipe, $id_pol)
    {
        if ($tipe == 'reguler') {


            $cari = Reguler::join('pol', 'pol.id_pol', 'reguler.id_pol')
                ->join('proses', 'proses.id_proses', 'reguler.id_proses')
                ->select(DB::raw('reguler.id_proses,pol.kode_pol,pol.nama_pol,proses.nama_proses'))
                ->where('reguler.id_pol', $id_pol)
                ->groupBy('reguler.id_pol', 'reguler.id_proses')
                ->get();

            $row = [];
            foreach ($cari as $data) {
                $data->berjalan = Reguler::where('id_pol', $id_pol)->where('status', 1)->where('id_proses', $data->id_proses)->sum('isi');
                $data->selesai = Reguler::where('id_pol', $id_pol)->where('status', '>', 1)->where('id_proses', $data->id_proses)->sum('isi');
                $row[] = $data;
            }
        } else {
            $row = DB::table('counter')->join('pol', 'pol.id_pol', 'counter.id_pol')
                ->select(DB::raw(" 'counter' as nama_proses,pol.kode_pol,pol.nama_pol, sum(counter.jumlah) as selesai"))
                ->where('counter.id_pol', $id_pol)
                ->groupBy('counter.id_pol')
                ->get();
        }
        return $row;
    }

    public function coba()
    {
        $pol = Pol::find(1184);
        $data = strjson(1);
        dd(count($data));
    }

    public function cetak($awa, $akhi)
    {

        $awal = $awa . " 07:00:01";
        $akhir = hari_tambah($akhi) . " 07:00:00";

        $tanggal = DB::select("select date(created_at) as tanggal from reguler where created_at between '$awal' and '$akhir' group by date(created_at) ");
        //dd($tanggal);
        $data = [];
        foreach ($tanggal as $hari) {
            $awal_hari = $hari->tanggal . " 07:00:01";
            $akhir_hari = hari_tambah($hari->tanggal) . " 07:00:00";
            $cari = DB::table('reguler')
                ->select('reguler.id_proses', DB::raw(' date(reguler.created_at) as tanggal,pol.jumlah_order as jmlorder,reguler.id_pol,sum(reguler.dead) as dead,sum(reguler.chip_lemah) as lemah,sum(reguler.chip_error) as error,proses.nama_proses as proses,sum(reguler.isi) as total,pol.nama_pol,pol.kode_pol'))
                ->join('pol', 'pol.id_pol', 'reguler.id_pol')
                ->join('proses', 'proses.id_proses', 'reguler.id_proses')
                ->where('reguler.status', '>', 1)
                ->whereBetween('reguler.created_at', [$awal_hari, $akhir_hari])
                ->groupBy('reguler.id_proses', 'reguler.id_pol')
                ->get();

            $row = [];
            $row['tanggal'] = $hari->tanggal;
            $row['data'] = $cari;
            $data[] = $row;
        }


        return ['data' => $data, 'awal' => $awal, 'akhir' => $akhir];
    }

    public function export()
    {
        $hari_ini = date("2019-07");
        $tgl_pertama = date('Y-m-01', strtotime($hari_ini));
        $tgl_terakhir = date('Y-m-t', strtotime($hari_ini));
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
