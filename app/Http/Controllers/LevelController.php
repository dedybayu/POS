<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use Illuminate\Http\Request;
use Validator;
use Yajra\DataTables\DataTables;

class LevelController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Level',
            'list' => ['Home', 'Level']
        ];

        $page = (object) [
            'title' => 'Daftar Level yang terdaftar dalam sistem'
        ];

        $activeMenu = 'level';

        return view('level.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu
        ]);
    }

    public function list(Request $request)
    {
        $levels = LevelModel::select('level_id', 'level_nama', 'level_kode');

        return DataTables::of($levels)
            ->addIndexColumn()->addColumn('aksi', function ($level) {
                // $btn = '<a href="' . url('/level/' . $level->level_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                // $btn .= '<a href="' . url('/level/' . $level->level_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                // $btn .= '<form class="d-inline-block" method="POST" action="' .
                //     url('/level/' . $level->level_id) . '">' . csrf_field() . method_field('DELETE') .
                //     '<button type="submit" class="btn btn-danger btn-sm" 
                //     onclick="return confirm(\'Apakah Anda yakit menghapus data ini?\');">Hapus</button></form>';
                // return $btn;
    
                $btn = '<button onclick="modalAction(\'' . url('/level/' . $level->level_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/level/' . $level->level_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/level/' . $level->level_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah level',
            'list' => ['Home', 'level', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah level baru'
        ];

        $activeMenu = 'level'; // set menu yang sedang aktif

        return view('level.create', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'level_nama' => 'required|string|max:100',
            'level_kode' => 'required|string|max:5|unique:m_level,level_kode'
        ]);

        LevelModel::create([
            'level_nama' => $request->level_nama,
            'level_kode' => $request->level_kode,
        ]);

        return redirect('/level')->with('success', 'Data level berhasil disimpan');
    }


    public function create_ajax()
    {
        // return 'create_ajax dipanggil'; //Debug
        return view('level.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_nama' => 'required|string|max:100',
                'level_kode' => 'required|string|max:5|unique:m_level,level_kode'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';
                if ($validator->errors()->has('level_kode')) {
                    $errorMessage = 'Validasi Gagal (Kode Sudah Digunakan)';
                }

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors(),
                ]);
            }

            LevelModel::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Data level berhasil disimpan'
            ]);
        }

        return redirect('/');
    }


    // Menampilkan detail level
    public function show(string $id)
    {
        $level = LevelModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Detail level',
            'list' => ['Home', 'level', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail level'
        ];

        $activeMenu = 'level'; // set menu yang sedang aktif

        return view('level.show', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'level' => $level,
            'activeMenu' => $activeMenu
        ]);
    }




    // Menampilkan halaman form edit level
    public function edit(string $id)
    {
        $level = LevelModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Edit level',
            'list' => ['Home', 'level', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit level'
        ];

        $activeMenu = 'level'; // set menu yang sedang aktif

        return view('level.edit', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'level' => $level,
            'activeMenu' => $activeMenu
        ]);
    }

    // Menyimpan perubahan data level
    public function update(Request $request, string $id)
    {
        $request->validate([
            'level_nama' => 'required|string|max:100',
            'level_kode' => 'required|string|max:5'
        ]);

        try {
            $level = LevelModel::find($id);

            if (!$level) {
                return redirect('/level')->with('error', 'Data level tidak ditemukan');
            }

            $level->update([
                'level_nama' => $request->level_nama,
                'level_kode' => $request->level_kode,
            ]);

            return redirect('/level')->with('success', 'Data level berhasil diubah');
        } catch (\Exception $e) {
            return redirect('/level')->with('error', 'Gagal Update (kode sudah terpakai)');
        }
    }


    // Menghapus data level
    public function destroy(string $id)
    {
        $check = LevelModel::find($id);
        if (!$check) {
            // untuk mengecek apakah data level dengan id yang dimaksud ada atau tidak
            return redirect('/level')->with('error', 'Data level tidak ditemukan');
        }

        try {
            LevelModel::destroy($id); // Hapus data level
            return redirect('/level')->with('success', 'Data level berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi error ketika menghapus data, redirect kembali ke halaman dengan membawa pesan error
            return redirect('/level')->with(
                'error',
                'Data level gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
            );
        }
    }
}