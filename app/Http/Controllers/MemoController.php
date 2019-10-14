<?php

namespace App\Http\Controllers;

//use App\Exports\UsersExport;
//use Maatwebsite\Excel\Facades\Excel;


use Illuminate\Http\Request;
use App\Lot;
use App\Reguler;
use App\Pol;
use DB;
use PDF;

class LaporanController extends Controller
{

    public function index()
    { }

    public function inerReguler($id_pol)
    {
        $pol = Pol::find($id_pol);
        $bagi = Lot::where('id_pol', $id_pol)->orderBy('id_lot', 'desc')->groupBy('no_awal')->get();
        $arr = array();
        foreach ($bagi as $data) {
            $lot = Lot::where('id_pol', $id_pol)->where('no_awal', $data->no_awal)->sum('jumlah');
            $jumlah_iner = ceil($lot / $pol->per_iner);
            $last_iner = $lot % $pol->per_iner;
            for ($i = 1; $i < $jumlah_iner; $i++) {
                $iner = $i + $data->no_awal - 1;
                $isi = DB::table('reguler')->where('id_pol', $pol->id_pol)->where('proses_ke', 1)->where('iner', $iner)->sum('isi');
                if ($isi < $pol->per_iner) {
                    $row = array();
                    $row['iner'] = $iner;
                    $row['isi'] = $pol->per_iner - $isi;
                    $arr[] = $row;
                }
            }

            $jumlah_last = DB::table('reguler')->where('id_pol', $pol->id_pol)->where('proses_ke', 1)->where('iner', $jumlah_iner - 1)->sum('isi');
            if ($jumlah_last < $last_iner) {
                $jum = DB::table('reguler')->where('id_pol', $pol->id_pol)->where('proses_ke', 1)->where('iner', $jumlah_iner + $data->no_awal - 1)->sum('isi');
                
				if($jum < $pol->per_iner){
                    $row = array();
                    $row['iner'] = $jumlah_iner + $data->no_awal - 1;
                    $row['isi'] = $last_iner - $jumlah_last;
                    $arr[] = $row;
                }
            }
        }

        $data = super_unique($arr, 'iner');

        $keys = array_column($data, 'iner');
        array_multisort($keys, SORT_ASC, $data);
        //dd($cari);
        return response()->json($data);
    }

    public function inerRegulerke($ke, $id_pol)
    {
        $reguler = Reguler::where('id_pol', $id_pol)->where('proses_ke', $ke)->where('status', 2)
            ->select('*', DB::raw("sum(isi) as isi"))
            ->groupBy('iner')
            ->with('user')->get();
        return response()->json($reguler);
    }


    public function allLaporan($id_pol, $proses_ke, $operator, $status, $tanggal)
    {


        if ($status == 'sementara') {
            $stat = 'b.status = 1';
        } else {
            $stat = 'b.status > 1';
        }

        if ($tanggal != 'null') {
            $awal = $tanggal . " 07:00:01";
            $akhir = hari_tambah($tanggal) . " 07:00:00";
            $time = "and b.created_at between '$awal' and '$akhir'";
        } else {
            $time = '';
        }

        $data = DB::select("SELECT name as oplama,b.* FROM `reguler` as a right join reguler as b on a.id_laporan = b.old left join users on a.operator = users.id where b.id_pol = ? and b.proses_ke = ? and b.operator =? and $stat $time", [$id_pol, $proses_ke, $operator]);
        return response()->json($data);
    }

    public function allTanggal($id_pol, $proses_ke, $operator)
    {
        // $awal = $tanggal . " 07:00:01";
        // $akhir = hari_tambah($tanggal) . " 07:00:00";


        $data = DB::select("SELECT date(created_at) as tanggal from reguler where id_pol = ? and proses_ke = ? and operator =? and status>1 group by date(created_at)", [$id_pol, $proses_ke, $operator]);
        $row = [];
        foreach ($data as $item) {
            $row[] = $item->tanggal;
        }
        return response()->json($row);
    }

    public function store(Request $request)
    {

        $pol = Pol::find($request->id_pol);
        $isilastiner =  $pol->jmlorder % $pol->per_iner;
        $data = Reguler::where('iner', $request->iner)->where('id_pol', $request->id_pol)->where('proses_ke', $request->proses_ke)->count();
        $jumlah_iner = ceil($pol->jmlorder / $pol->per_iner);
        if ($data > 0) {
            if ($jumlah_iner == $request->iner) {
                $jumlah = Reguler::where('iner', $request->iner)->where('id_pol', $request->id_pol)->where('proses_ke', $request->proses_ke)->sum('isi');
                $sisa = $isilastiner - $jumlah;
            } else {
                $jumlah = Reguler::where('iner', $request->iner)->where('id_pol', $request->id_pol)->where('proses_ke', $request->proses_ke)->sum('isi');
                $sisa = $pol->per_iner - $jumlah;
            }

            if ($request->isi > $sisa) {
                $isi = $sisa;
            } else {
                $isi = $request->isi;
            }
        } else {
            if ($pol->jumlah_iner == $request->iner) {
                //jika laporan nomor terakhir                                          
                if ($request->isi > $isilastiner) {
                    $isi = $isilastiner;
                } else {
                    $isi = $request->isi;
                }
            } else {
                //jika laporan nomor bukan terakhir
                if ($request->isi > $pol->per_iner) {
                    $isi = $pol->per_iner;
                } else {
                    $isi = $request->isi;
                }
            }
        }

        if ($request->old != '') {
            $laporan = Reguler::find($request->old);
            $laporan->status = 3;
            $laporan->save();
        }

        if ($isi <> 0) {
            $reguler = new Reguler;
            $reguler->id_pol = $request->id_pol;
            $reguler->iner = $request->iner;
            $reguler->isi = $isi;
            $reguler->id_proses = $request->id_proses;
            $reguler->proses_ke = $request->proses_ke;
            $reguler->shift = $request->shift;
            $reguler->line = $request->line;
            $reguler->operator = $request->operator;
            $reguler->status = $request->status;
            $reguler->old = $request->old;
            $reguler->save();
            $data   = DB::select("SELECT name as oplama, b.* FROM `reguler` as a right join reguler as b on a.id_laporan = b.old left join users on a.operator = users.id where b.id_laporan = ?", [$reguler->id_laporan]);
            return response()->json($data);
        }
    }

    public function update(Request $request, $id)
    {
        $reguler = Reguler::find($id);
        $pol = Pol::find($reguler->id_pol);
        if ($reguler->id_lot != '') {
            $lot = Lot::find($reguler->id_lot);
            $lot->reject += ($reguler->dead + $reguler->chip_lemah + $reguler->chip_error + $reguler->card_body + $reguler->miss_print + $reguler->miss_laser);
            $lot->good = $lot->jumlah - $lot->reject;
            $lot->save();
        }

        if ($request->hasFile('log')) {

            $file = $request->file("log");
            $nama_gambar = $file->getClientOriginalName();
            $lokasi = public_path('log/' . $pol->tahun . '/' . $pol->kode_pol . ' - ' . $pol->nama_pol);
            $file->move($lokasi, $nama_gambar);
        }

        if ($reguler->created_at == $reguler->updated_at) {
            $reguler->id_lot = $request->id_lot;
            $reguler->dead = $request->dead;
            $reguler->chip_lemah = $request->chip_lemah;
            $reguler->chip_error = $request->chip_error;
            $reguler->card_body = $request->card_body;
            $reguler->miss_laser = $request->miss_laser;
            $reguler->miss_print = $request->miss_print;
            $reguler->serial_awal = $request->serial_awal;
            $reguler->serial_akhir = $request->serial_akhir;
            if ($request->keterangan == 'null') {
                $reguler->keterangan = '';
            } else {
                $reguler->keterangan = $request->keterangan;
            }

            if ($reguler->status == 1) {
                $reguler->status = 2;
            }
            $reguler->update();
        } else {
            if ($reguler->status == 1) {
                $status = 2;
            } else {
                $status = $reguler->status;
            }
            if ($request->keterangan == 'null') {
                $keterangan = '';
            } else {
                $keterangan = $request->keterangan;
            }
            DB::table('reguler')->where('id_laporan', $id)->update([
                'id_lot' => $request->id_lot,
                'dead' => $request->dead,
                'chip_lemah' => $request->chip_lemah,
                'chip_error' => $request->chip_error,
                'card_body' => $request->card_body,
                'miss_print' => $request->miss_print,
                'miss_laser' => $request->miss_laser,
                'serial_awal' => $request->serial_awal,
                'serial_akhir' => $request->serial_akhir,
                'keterangan' => $keterangan,
                'status' =>  $status
            ]);
            $reguler = Reguler::find($id);
        }

        $cek = Reguler::where('proses_ke', $pol->jml_proses)->sum('isi');
        if ($cek >= $pol->jumlah_order) {
            $pol->status = 1;
            $pol->update();
        }

        $reg = Reguler::where('id_pol', $pol->id_pol)->where('proses_ke', 1)->first();
        $lot = Lot::find($reg->id_lot);
        $lot->reject += ($reguler->dead + $reguler->chip_lemah + $reguler->chip_error + $reguler->card_body + $reguler->miss_print + $reguler->miss_laser);
        $lot->good = $lot->jumlah - $lot->reject;
        $lot->update();

        $data   = DB::select("SELECT name as oplama, b.*,b.dead,b.chip_error,b.card_body,b.chip_lemah,b.miss_print,b.miss_laser FROM `reguler` as a right join reguler as b on a.id_laporan = b.old left join users on a.operator = users.id where b.id_laporan = ?", [$reguler->id_laporan]);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $reguler = Reguler::find($id);
        if ($reguler->old != '') {
            $cek = Reguler::where('id_laporan', $reguler->old)->count();
            if ($cek > 0) {
                $old = Reguler::find($reguler->old);
                $old->status = 2;
                $old->update();
            }
        }

        if ($reguler->id_lot != '') {
            $lot = Lot::find($reguler->id_lot);
            $lot->reject += ($reguler->dead + $reguler->chip_lemah + $reguler->chip_error + $reguler->card_body + $reguler->miss_print + $reguler->miss_laser);
            $lot->good = $lot->jumlah - $lot->reject;
            $lot->update();
        }
        $reguler->delete();
    }

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

        return response()->json($arr);
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

        return response()->json($arr);
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

        return response()->json($arr);
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

        return response()->json([
            'reguler' => $arr,
            'counter' => $arr_counter,
        ]);
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
            ->whereBetween('reguler.created_at', [$awal, $akhir])
            ->groupBy('reguler.id_proses', 'reguler.id_pol')
            ->get();
        $monitoring = [];
        foreach ($data as $cari) {
            $terproses = DB::table('reguler')->where('id_pol', $cari->id_pol)->where('id_proses', $cari->id_proses)->sum('isi');
            $cari->terproses = $terproses;
            $monitoring[] = $cari;
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


        $transfer = \App\Transfer::with('pol')->when($shift != 'all', function ($query) use ($shift) {
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

        return response()->json([
            'data' => $monitoring,
            'total' => $arr,
            'transfer' => $transfer,
            'counter' => $counter
        ]);
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

        return response()->json([
            'pol' => $pol,
            'data' => $data,
            'resume' => $resume,
            'data_grafik' => $arr,
            'lot_count' => $lot_count,
            'lot_jumlah' => $lot_jumlah,
            'lot_reject' => $lot_reject,
            'lot_good' => $lot_good
        ]);
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
        return response()->json($row);
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

        // dd($data);
        $pdf = PDF::loadView('pdf', compact('data', 'awal', 'akhir'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream();
    }

    public function export()
    {
        $hari_ini = date("2019-07");
        $tgl_pertama = date('Y-m-01', strtotime($hari_ini));
        $tgl_terakhir = date('Y-m-t', strtotime($hari_ini));
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
