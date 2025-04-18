<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class StokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Stok',
            'list' => ['Home', 'stok']
        ];

        $page = (object) [
            'title' => 'Daftar Stok yang terdaftar dalam sistem'
        ];

        $activeMenu = 'stok';

        $supplier = SupplierModel::all();
        $kategori = KategoriModel::all();
        $user = UserModel::all();

        return view('stok.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'supplier' => $supplier,
            'kategori' => $kategori,
            'user' => $user,
            'activeMenu' => $activeMenu
        ]);
    }

    public function list(Request $request)
    {
        // return "hello";
        $stoks = StokModel::select(
            't_stok.stok_id',
            't_stok.supplier_id',
            't_stok.barang_id',
            't_stok.user_id',
            't_stok.stok_tanggal',
            't_stok.stok_jumlah'
        )->with('supplier', 'barang.kategori', 'user');

        // Filter berdasarkan supplier
        if ($request->supplier_id) {
            $stoks->where('supplier_id', $request->supplier_id);
        }

        // Filter berdasarkan kategori (harus melalui relasi ke barang)
        if ($request->kategori_id) {
            $stoks->whereHas('barang', function ($query) use ($request) {
                $query->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter berdasarkan user
        if ($request->user_id) {
            $stoks->where('user_id', $request->user_id);
        }
        // dd($stoks);
        return DataTables::of($stoks)
            ->editColumn('stok_tanggal', function ($stok) {
                return \Carbon\Carbon::parse($stok->stok_tanggal)->format('d-m-Y');
            })
            ->addIndexColumn()->addColumn('aksi', function ($stok) {
                $btn = '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(StokModel $stokModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokModel $stokModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StokModel $stokModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokModel $stokModel)
    {
        //
    }
}
