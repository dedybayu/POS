<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index(){
        // DB::insert('insert into m_level(level_kode, level_nama, created_at) values (?, ?, ?)', ['CUS', 'Pelanggan', now()]);
        // return 'Insert Data Baru Berhasil';
        
        // $row = DB::insert('update m_level set level_nama = ? where level_kode = ?', ['Customer', 'CUS']);
        // return 'Update Data Berhasil, Jumlah data yang diupdate: ' . $row . ' Baris';

        // $row = DB::delete('delete from m_level where level_kode = ?', ['CUS']);
        // return 'Delete data berhasil, jumlah data yang dihapus: ' . $row . ' Baris';

        $data = DB::select('select * from m_level');
        return view('level', ['data' => $data]);
    }
}
