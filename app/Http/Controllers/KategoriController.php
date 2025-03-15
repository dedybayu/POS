<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\DataTables\DataTables;

class KategoriController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Kategori',
            'list' => ['Home', 'Kategori']
        ];

        $page = (object) [
            'title' => 'Daftar Kategori yang terdaftar dalam sistem'
        ];

        $activeMenu = 'kategori';

        return view('kategori.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu
        ]);
    }

    public function list(Request $request)
    {
        $kategoris = KategoriModel::select('kategori_id', 'kategori_nama', 'kategori_kode');

        //Filter berdasarkan kategori
        if ($request->kategori_id) {
            $kategoris->where('kategori_id', $request->kategori_id);
        }

        return DataTables::of($kategoris)
            ->addIndexColumn()->addColumn('aksi', function ($kategori) {
                // $btn = '<a href="' . url('/kategori/' . $kategori->kategori_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                // $btn .= '<a href="' . url('/kategori/' . $kategori->kategori_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                // $btn .= '<form class="d-inline-block" method="POST" action="' .
                //     url('/kategori/' . $kategori->kategori_id) . '">' . csrf_field() . method_field('DELETE') .
                //     '<button type="submit" class="btn btn-danger btn-sm" 
                //     onclick="return confirm(\'Apakah Anda yakit menghapus data ini?\');">Hapus</button></form>';
                // return $btn;

                $btn = '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;

            })->rawColumns(['aksi'])
            ->make(true);
    }


    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah kategori',
            'list' => ['Home', 'kategori', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah kategori baru'
        ];

        $activeMenu = 'kategori'; // set menu yang sedang aktif

        return view('kategori.create', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'kategori_nama' => 'required|string|max:100',
            'kategori_kode' => 'required|string|max:5|unique:m_kategori,kategori_kode'
        ]);

        KategoriModel::create([
            'kategori_nama' => $request->kategori_nama,
            'kategori_kode' => $request->kategori_kode,
        ]);

        return redirect('/kategori')->with('success', 'Data kategori berhasil disimpan');
    }


    public function create_ajax()
    {
        // return 'create_ajax dipanggil'; //Debug
        return view('kategori.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_nama' => 'required|string|max:100',
                'kategori_kode' => 'required|string|max:5|unique:m_kategori,kategori_kode'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';
                if ($validator->errors()->has('kategori_kode')) {
                    $errorMessage = 'Validasi Gagal (Kode Sudah Digunakan)';
                }

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors(),
                ]);
            }

            KategoriModel::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Data kategori berhasil disimpan'
            ]);
        }

        return redirect('/');
    }

    // Menampilkan detail kategori
    public function show(string $id)
    {
        $kategori = KategoriModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Detail kategori',
            'list' => ['Home', 'kategori', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail kategori'
        ];

        $activeMenu = 'kategori'; // set menu yang sedang aktif

        return view('kategori.show', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'kategori' => $kategori,
            'activeMenu' => $activeMenu
        ]);
    }

    public function show_ajax(string $id)
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.show_ajax', ['kategori' => $kategori]);
    }

    // Menampilkan halaman form edit kategori
    public function edit(string $id)
    {
        $kategori = KategoriModel::find($id);

        $breadcrumb = (object) [
            'title' => 'Edit kategori',
            'list' => ['Home', 'kategori', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit kategori'
        ];

        $activeMenu = 'kategori'; // set menu yang sedang aktif

        return view('kategori.edit', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'kategori' => $kategori,
            'activeMenu' => $activeMenu
        ]);
    }

    // Menyimpan perubahan data kategori
    public function update(Request $request, string $id)
    {
        $request->validate([
            'kategori_nama' => 'required|string|max:100',
            'kategori_kode' => 'required|string|max:5'
        ]);

        try {
            $kategori = KategoriModel::find($id);

            if (!$kategori) {
                return redirect('/kategori')->with('error', 'Data Kategori tidak ditemukan');
            }
            KategoriModel::find($id)->update([
                'kategori_nama' => $request->kategori_nama,
                'kategori_kode' => $request->kategori_kode,
            ]);
            return redirect('/kategori')->with('success', 'Data kategori berhasil diubah');
        } catch (\Exception $e) {
            return redirect('/kategori')->with('error', 'Gagal Update (kode sudah terpakai)');
        }
    }


    public function edit_ajax(string $id)
    {
        // return "Berhasil";
        $kategori = KategoriModel::find($id);

        return view('kategori.edit_ajax', ['kategori' => $kategori]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_nama' => 'required|string|max:100',
                'kategori_kode' => 'required|string|max:5|unique:m_kategori,kategori_kode,' . $id . ',kategori_id'
            ];
    
            $messages = [
                'kategori_kode.unique' => 'Kode Sudah Digunakan'
            ];
    
            $validator = Validator::make($request->all(), $rules, $messages);
    
            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';
                if ($validator->errors()->has('kategori_kode')) {
                    $errorMessage = 'Validasi Gagal (Kode Sudah Digunakan)';
                }
    
                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors()
                ]);
            }
    
            $kategori = KategoriModel::find($id);
            if ($kategori) {
                $kategori->update($request->all());
                return response()->json(['status' => true, 'message' => 'Data kategori berhasil diperbarui']);
            } else {
                return response()->json(['status' => false, 'message' => 'Data kategori tidak ditemukan']);
            }
        }
        return redirect('/');
    }


    public function comfirm_ajax(string $id)
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.comfirm_ajax', ['kategori' => $kategori]);
    }

    public function delete_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $kategori = KategoriModel::find($id);
            if ($kategori) {
                $kategori->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }

    // Menghapus data kategori
    public function destroy(string $id)
    {
        $check = KategoriModel::find($id);
        if (!$check) {
            // untuk mengecek apakah data kategori dengan id yang dimaksud ada atau tidak
            return redirect('/kategori')->with('error', 'Data kategori tidak ditemukan');
        }

        try {
            KategoriModel::destroy($id); // Hapus data kategori
            return redirect('/kategori')->with('success', 'Data kategori berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi error ketika menghapus data, redirect kembali ke halaman dengan membawa pesan error
            return redirect('/kategori')->with(
                'error',
                'Data kategori gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
            );
        }
    }
}
