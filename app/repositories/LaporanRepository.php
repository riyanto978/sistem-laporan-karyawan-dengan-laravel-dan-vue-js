<?php

namespace App\repositories;

use App\models\Pol;
use App\models\Lot;
use App\models\Reguler;
use DB;

class LaporanRepository implements LaporanInterface
{
    public function inerReguler($id_pol)
    {
        //ambil data Pol
        $pol = Pol::find($id_pol);
        //ambil lot sesuai no awal
        $bagi = Lot::where('id_pol', $id_pol)->orderBy('id_lot', 'desc')->orderBy('no_awal', 'asc')->groupBy('no_awal')->select('no_awal', DB::raw("sum(jumlah) as total"))->get();
        $arr = array();
        //loop lot sesuai no_awal
        foreach ($bagi as $data) {
            //jumlah data per no awal / per iner
            $jumlah_iner = ceil($data->total / $pol->per_iner);
            $last_iner = $data->total % $pol->per_iner;

            for ($i = 1; $i <= $jumlah_iner; $i++) {
                $iner = $i + $data->no_awal - 1;
                $isi = DB::table('reguler')->where('id_pol', $pol->id_pol)->where('proses_ke', 1)->where('iner', $iner)->sum('isi');
                if ($isi < $pol->per_iner) {
                    $row = array();
                    $row['iner'] = $iner;
                    $row['isi'] = $pol->per_iner - $isi;
                    $arr[] = $row;
                }
            }
        }

        $data = super_unique($arr, 'iner');

        $keys = array_column($data, 'iner');
        array_multisort($keys, SORT_ASC, $data);
        return $data;
    }

    public function inerRegulerke($ke, $id_pol)
    {
        $reguler = Reguler::where('id_pol', $id_pol)->where('proses_ke', $ke)->where('status', 2)
            ->select('*', DB::raw("sum(isi) as isi"))
            ->groupBy('iner')
            ->with('user')->get();
        return $reguler;
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
        return $data;
    }

    public function allTanggal($id_pol, $proses_ke, $operator)
    {

        $data = DB::select("SELECT date(created_at) as tanggal from reguler where id_pol = ? and proses_ke = ? and operator =? and status>1 group by date(created_at)", [$id_pol, $proses_ke, $operator]);
        return $data;
    }

    public function hitungIsi($request)
    {
        $pol = Pol::find($request->id_pol);
        $isilastiner =  $pol->jumlah_order % $pol->per_iner;

        $data = Reguler::where('iner', $request->iner)->where('id_pol', $request->id_pol)->where('proses_ke', $request->proses_ke)->count();
        $jumlah_iner = floor($pol->jumlah_order / $pol->per_iner);

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
            if ($jumlah_iner == $request->iner) {
                //jika laporan nomor terakhir

                if ($isilastiner == 0) {
                    $isi = $pol->per_iner;
                } elseif ($request->isi > $isilastiner) {
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
        return $isi;
    }

    public function store($request)
    {
        $pol = Pol::find($request->id_pol);
        $isi = $this->hitungIsi($request);

        //update laporan sebelumnya jika jumlah lebih besar dari pol per iner
        if ($request->old != '') {
            $laporan = Reguler::find($request->old);
            $jumlah_ke = Reguler::where('id_pol', $pol->id_pol)->where('iner', $laporan->iner)->where('proses_ke', $laporan->proses_ke)->sum('isi');

            if ($jumlah_ke >= $pol->per_iner) {
                $laporan->status = 3;
                $laporan->save();
            }
        }
        //insert ke database jika isi tidak 0
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
            $reguler->laporan_tipe = $request->laporan_tipe;
            $reguler->save();
            $data   = DB::select("SELECT name as oplama, b.* FROM `reguler` as a right join reguler as b on a.id_laporan = b.old left join users on a.operator = users.id where b.id_laporan = ?", [$reguler->id_laporan]);
            return $data;
        }
    }



    public function UploadFile($request, $pol)
    {
        if ($request->hasFile('log')) {
            $file = $request->file("log");
            $nama_gambar = $file->getClientOriginalName();
            $lokasi = public_path('log/' . $pol->tahun . '/' . $pol->kode_pol . ' - ' . $pol->nama_pol);
            $file->move($lokasi, $nama_gambar);
        }
    }

    public function UpdateLaporan($request, $reguler, $id)
    {
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
        return $reguler;
    }

    public function UpdateLot($reguler)
    {
        $total = Reguler::where('id_pol', $reguler->id_pol)->where('iner', $reguler->iner)->groupBy('id_pol','iner')->select('id_lot', DB::raw("sum(dead+chip_lemah+card_body+chip_error+miss_laser+miss_print) as total"))->first();
        //dd($total);
        if($total->id_lot != ''){
            $lot = Lot::find($total->id_lot);
            $lot ->reject = $total->total;
            $lot->good = $lot->jumlah - $lot->reject;
            $lot->update();
        }

    }

    public function update($request, $id)
    {
        $reguler = Reguler::find($id);
        $pol = Pol::find($reguler->id_pol);

        //update lot
        //$this->KurangLot($reguler);
        //upload File
        $this->UploadFile($request, $pol);
        //update Laporan
        $reguler = $this->UpdateLaporan($request, $reguler, $id);

        //update lot
        $this->UpdateLot($reguler);

        $data   = DB::select("SELECT name as oplama, b.*,b.dead,b.chip_error,b.card_body,b.chip_lemah,b.miss_print,b.miss_laser FROM `reguler` as a right join reguler as b on a.id_laporan = b.old left join users on a.operator = users.id where b.id_laporan = ?", [$reguler->id_laporan]);
        return $data;

        // $cek = Reguler::where('id_pol', $pol->id_pol)->where('proses_ke', $pol->jml_proses)->sum('isi');
        // $cek_proses = Reguler::where('id_pol', $pol->id_pol)->where('proses_ke', $pol->jml_proses)->where('status', 1)->count();
        // if ($cek >= $pol->jumlah_order && $cek_proses == 0) {
        //     $pol->status = 1;
        //     $pol->update();
        // }
    }

    public function destroy($id)
    {
        $reguler = Reguler::find($id);;

        if ($reguler->old != '') {
            $cek = Reguler::where('id_laporan', $reguler->old)->count();
            if ($cek > 0) {
                $old = Reguler::find($reguler->old);
                $old->status = 2;
                $old->update();
            }
        }
        $data = $reguler;
        //update lot
        $reguler->delete();
        $this->UpdateLot($data);
    }
}
