<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    public function all($cari)
    {
        if ($cari == 'null') {
            $search = '';
        } else {
            $search = $cari;
        }
        $user = User::where('username', 'like', '%' . $search . '%')->paginate(5);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
    }

    public function store(Request $request)
    {

        $file = $request->file("foto");
        $nama_gambar = $request->username . date('YmdHis') . "." . $file->getClientOriginalExtension();
        $lokasi = public_path('foto');

        // foreach ($this->dimensions as $row) {
        //     $canvas = Image::canvas($row, $row);
        //     $resizeImage  = Image::make($file)->resize($row, $row, function ($constraint) {
        //         $constraint->aspectRatio();
        //     });

        //     if (!File::isDirectory($this->path . '/' . $row)) {
        //         File::makeDirectory($this->path . '/' . $row);
        //     }
        //     $canvas->insert($resizeImage, 'center');
        //     $canvas->save($this->path . '/' . $row . '/' . $nama_gambar);
        // }        
        $file->move($lokasi, $nama_gambar);

        $user = new User;
        $user->name = $request['name'];
        $user->username = $request['username'];
        $user->password = bcrypt($request['password']);
        $user->foto         = $nama_gambar;
        $user->level = $request['level'];
        $user->save();

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $user->name = $request['name'];
        $user->username = $request['username'];
        $user->level = $request['level'];
        if ($request['password'] != 'undefined') {
            $user->password = bcrypt($request['password']);
        }
        if ($request->hasFile('foto')) {
            $file = $request->file("foto");
            $nama_gambar = $user->username . date('YmdHis') . "." . $file->getClientOriginalExtension();
            $lokasi = public_path('foto');

            // foreach ($this->dimensions as $row) {
            //     $canvas = Image::canvas($row, $row);
            //     $resizeImage  = Image::make($file)->resize($row, $row, function ($constraint) {
            //         $constraint->aspectRatio();
            //     });

            //     if (!File::isDirectory($this->path . '/' . $row)) {
            //         File::makeDirectory($this->path . '/' . $row);
            //     }
            //     $canvas->insert($resizeImage, 'center');
            //     $canvas->save($this->path . '/' . $row . '/' . $nama_gambar);
            // }

            $file->move($lokasi, $nama_gambar);
            $user->foto = $nama_gambar;
        }
        $user->update();
        return response()->json($user);
    }
}
