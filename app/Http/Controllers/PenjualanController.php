<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar penjualan',
            'list' => ['Home', 'penjualan']
        ];

        $page = (object) [
            'title' => 'Daftar penjualan yang terdaftar dalam sistem'
        ];

        $activeMenu = 'penjualan';

        $user = UserModel::all();

        return view('penjualan.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu,
            'user' => $user
        ]);
    }

    public function list(Request $request)
    {
        // return "hello";
        $penjualans = PenjualanModel::select(
            't_penjualan.penjualan_id',
            't_penjualan.penjualan_kode',
            't_penjualan.pembeli',
            't_penjualan.penjualan_tanggal',
            't_penjualan.user_id',
        )->with('penjualan_detail', 'user.level');

        if ($request->user_id) {
            $penjualans->where('user_id', $request->user_id);
        }
        return DataTables::of($penjualans)
            ->editColumn('penjualan_tanggal', function ($penjualan) {
                return \Carbon\Carbon::parse($penjualan->penjualan_tanggal)->format('d-m-Y');
            })

            ->addColumn('total_harga', function ($penjualan) {
                $totalHarga = $penjualan->penjualan_detail->sum(function ($detail) {
                    return $detail->harga * $detail->jumlah;
                });
                return 'Rp' . number_format($totalHarga, 0, ',', '.') . ',00';
            })
            


            ->addColumn('user', function ($penjualan) {
                $user = $penjualan->user;
                return $user->nama . " (" . $user->level->level_kode . ")";
            })

            ->addIndexColumn()->addColumn('aksi', function ($penjualan) {
                $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/show') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })->rawColumns(['aksi'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return 'create_ajax dipanggil'; //Debug
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual')->get();

        return view('penjualan.create')->with([
            'barang' => $barang
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request); // Mengakses array 'barang[]' dari request
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail', 'user.level')->find($id);
    
        if (!$penjualan) {
            abort(404); // atau redirect dengan pesan error
        }
    
        $totalHarga = $penjualan->penjualan_detail->sum('harga');
        $user = $penjualan->user->mama . ' (' . $penjualan->user->level->level_kode . ')';
    
        return view('penjualan.show', [
            'penjualan' => $penjualan,
            'total_harga' => $totalHarga,
            'user' => $user
        ]);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PenjualanModel $penjualanModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PenjualanModel $penjualanModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PenjualanModel $penjualanModel)
    {
        //
    }
}
