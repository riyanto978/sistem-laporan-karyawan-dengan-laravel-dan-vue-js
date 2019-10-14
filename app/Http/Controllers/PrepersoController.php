<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Preperso;
use DB;
use App\uid;

class PrepersoController extends Controller
{

    public function sementara($operator)
    {
        //$Preperso = User::Prepersos();
        // $Preperso = Preperso::all();
        $Preperso = Preperso::with('pol')->rightJoin('applet','preperso.old','id_applet')->rightJoin('users','users.id','applet.operator')->where('preperso.operator', $operator)->where('preperso.status', 1)->select('*','preperso.created_at','preperso.dead')->get();
        return response()->json($Preperso);
    }

    public function selesai($operator, $tanggal)
    {
        $awal = $tanggal . " 07:00:01";
        $akhir = hari_tambah($tanggal) . " 07:00:00";
        //$Preperso = User::Prepersos();
        // $Preperso = Preperso::all();
        $Preperso = Preperso::with('pol')->where('operator', $operator)->where('status', '>', 1)->whereBetween('Preperso.created_at', [$awal, $akhir])->get();
        return response()->json($Preperso);
    }

    public function ambilPreperso()
    {
        return response()->json(Preperso::all());
    }

    public function store(Request $request)
    {
        DB::table('applet')
            ->where('id_applet', $request->old)
            ->update([
                'status' => 3,
            ]);

        $Preperso = new Preperso;
        $Preperso->id_preperso = $request->old;
        $Preperso->id_pol = $request->id_pol;
        $Preperso->operator = $request->operator;
        $Preperso->line = $request->line;
        $Preperso->shift = $request->shift;
        $Preperso->isi = $request->isi;
        $Preperso->dead = $request->dead;
        $Preperso->status = 1;                        
        $Preperso->old = $request->old;
        $Preperso->save();
        
        $cari = Preperso::with('pol')->rightJoin('applet', 'preperso.old', 'id_applet')->rightJoin('users', 'users.id', 'applet.operator')->select('*', 'preperso.created_at', 'preperso.dead')->where('preperso.id_preperso', $request->old)->first();
        return response()->json($cari);
    }

    public function update(Request $request, $id)
    {
        $file = $request->file("log");
        $files = $request->id_preperso . '.txt';

        if ($request->hasFile('log') && $file->getClientOriginalName() == $files) {

            $Preperso = Preperso::find($request->id_preperso);                        
            $Preperso->dead = $request->dead;            
                        
            if($Preperso->status == 1){
                $Preperso->status =  2;
            }
            
            $Preperso->update();
            $operator = $Preperso->with('user')->first();

            $nama_gambar = $file->getClientOriginalName();
            $lokasi = public_path('log/log_preperso/' . $operator->user->username);
            $file->move($lokasi, $nama_gambar);

            $file_path = public_path('log/log_preperso/' . $operator->user->username . '/' . $nama_gambar);
            foreach (file($file_path) as $data) {                
                if (uid::where('uid', $data)->count() == 0) {
                    $sam = new uid;
                    $sam->id_preperso = $request->id_preperso;                    
                    $sam->uid = $data;
                    $sam->save();
                }
            }
            
            return response()->json($Preperso->with('pol')->where('id_preperso',$Preperso->id_preperso)->first());
            // return response()->json($Preperso->with('pol')->first());
        } else {
            return response()->json('error');
        }
    }


    public function destroy($id)
    {
        $Preperso = Preperso::find($id);
        DB::table('applet')
            ->where('id_applet', $Preperso->old)
            ->update([
                'status' => 2,
            ]);
        DB::table('uid')->where('id_preperso', $Preperso->id_preperso)->delete();
        $Preperso->delete();
    }

    public function PrepersoDataPreperso($cari)
    {

        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $data = Preperso::with('pol')->where('status', 2)->where('id_preperso', 'like', '%' . $search . '%')->paginate(5);
        return response()->json($data);
    }
}
