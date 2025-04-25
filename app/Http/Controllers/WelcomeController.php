<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\LevelModel;
use App\Models\PenjualanDetailModel;
use App\Models\PenjualanModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(){
        $breadcrumb = (object)[
            'title' => 'Selamat Datang',
            'list' => ['Home', 'Welcome']
        ];
        $activeMenu = 'dashboard';
        $jumlahUser = UserModel::count();
        $jumlahLevel = LevelModel::count();
        $totalPendapatan = PenjualanDetailModel::sum('harga');
        // $totalStok = BarangModel::sum('real_stok');
        $totalStok = BarangModel::all()->sum(function ($barang) {
            return $barang->real_stok;
        });
        $jumlahBarang = BarangModel::count();
        $jumlahKategori = KategoriModel::count();
        $jumlahSupplier = SupplierModel::count();
        $totalTransaksi = PenjualanModel::count();

        return view('welcome', [
            'breadcrumb' => $breadcrumb, 
            'activeMenu' => $activeMenu, 
            'jumlah_user' => $jumlahUser,
            'jumlah_level' => $jumlahLevel,
            'total_pendapatan' => 'Rp' . number_format($totalPendapatan, 0, ',', '.') . ',00',
            'jumlah_barang' => $jumlahBarang,
            'jumlah_kategori' => $jumlahKategori,
            'jumlah_supplier' => $jumlahSupplier,
            'total_stok' => $totalStok,
            'total_transaksi' => $totalTransaksi,
        ]);
    }
}
