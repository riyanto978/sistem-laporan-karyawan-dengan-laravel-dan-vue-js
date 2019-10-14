<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Applet;
//use App\User;
use App\sam;
class AppletController extends Controller
{    
 
    public function sementara($operator)
    { 
        //$Applet = User::applets();
        // $Applet = Applet::all();
        $Applet = Applet::with('pol')->where('operator', $operator)->where('status', 1)->get();
        return response()->json($Applet);
    }

    public function selesai($operator, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";
        //$Applet = User::applets();
        // $Applet = Applet::all();
        $Applet = Applet::with('pol')->where('operator', $operator)->where('status','>' ,1)->whereBetween('applet.created_at',[$awal,$akhir])->get();
        return response()->json($Applet);
    }

    public function ambilApplet()
    {
        return response()->json(Applet::all());
    }
    
    public function store(Request $request)
    {
        $Applet = new Applet;
        $Applet->id_pol = $request->id_pol;
        $Applet->operator = $request->operator;
        $Applet->line = $request->line;
        $Applet->shift = $request->shift;
        $Applet->isi = $request->isi;
        $Applet->dead = $request->dead;
        $Applet->status = 1;
        $Applet->id_sam = $request->id_sam;
        $Applet->tipe_chip = $request->tipe_chip;        
        $Applet->lot = "# Lot 1 :";
        $Applet->save();        
        $cari = Applet::with('pol')->where('id_applet', $Applet->id_applet)->first();

        return response()->json($cari);
    }

    public function update(Request $request, $id)
    {
        $file = $request->file("log");
        $files = $request->id_applet . '(SUCCESS).txt';

        if ($request->hasFile('log') && $file->getClientOriginalName() == $files) {

            $applet = applet::find($request->id_applet);

            // $lot  = " # Lot 1 : " . $request->lot1;

            // if($request->lot2 !='undefined'){
            //     $lot .= " # Lot 2 : " . $request->lot2;
            // }

            // if ($request->lot3 != 'undefined') {
            //     $lot .= " # Lot 3 : " . $request->lot3;
            // }
            $applet->lot = $request->lot;                    
            $applet->dead = $request->dead;

            $applet->status =  2;
            $applet->update();
            $operator = $applet->with('user')->first();
            
            $nama_gambar = $file->getClientOriginalName();
            $lokasi = public_path('log/log_applet/' . $operator->user->username);
            $file->move($lokasi, $nama_gambar);

            $file_path = public_path('log/log_applet/' . $operator->user->username . '/' . $nama_gambar);
            foreach (file($file_path) as $g) {
                $data = explode(',', $g);
                if (sam::where('sam_uid', $data['4'])->count() == 0) {
                    $sam = new sam;
                    $sam->id_applet = $request->id_applet;
                    $sam->sam = "";
                    $sam->sam_uid = $data['4'];
                    $sam->save();
                }
            }

            return response()->json($applet->with('pol')->where('id_applet', $applet->id_applet)->first());
        } else {
            return response()->json('error');            
        }
    }


    public function destroy($id)
    {
        $Applet = Applet::find($id);
        $Applet->delete();
    }

    public function AppletDataPreperso($cari)
    {

        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $data = Applet::with('pol')->where('status', 2)->where('id_applet','like', '%' . $search . '%')->paginate(5);
        return response()->json($data);
    }
}
