<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanDetailModel;
use App\Models\PenjualanModel;
use App\Models\StokModel;
use App\Models\UserModel;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Storage;
use Validator;
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
                if (Auth::check() && in_array(Auth::user()->getRole(), ['ADM', 'MNG'])) {
                    // Akses diizinkan untuk role ADM dan MNG
                    // Lanjutkan proses
                    $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/show') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/edit') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/delete') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                } else if (
                    Auth::check() &&
                    in_array(Auth::user()->getRole(), ['ADM', 'MNG', 'STF']) &&
                    $penjualan->user_id == Auth::user()->user_id
                ) {
                    $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/show') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/edit') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/delete') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                } else {
                    $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                        '/show') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                    $btn .= '<button class="btn btn-secondary btn-sm" disabled>Edit</button> ';
                    $btn .= '<button class="btn btn-dark btn-sm" disabled>Hapus</button>';

                }


                return $btn;
            })->rawColumns(['aksi'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual')
            ->with('stok')
            ->get()
            ->filter(function ($brg) {
                return $brg->real_stok > 0;
            })
            ->values(); // reset keys

        return view('penjualan.create')->with([
            'barang' => $barang
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'pembeli' => 'required|string|max:100',
                'penjualan_kode' => 'required|string|max:5|unique:t_penjualan,penjualan_kode',
                'barang_id' => 'required|array',
                'barang_id.*' => 'required|integer|exists:m_barang,barang_id',
                'jumlah' => 'required|array',
                'jumlah.*' => 'required|integer',
            ];


            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';
                if ($validator->errors()->has('penjualan_kode')) {
                    $errorMessage = 'Validasi Gagal (Kode Sudah Digunakan)';
                }

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors(),
                ]);
            }

            $dataPenjualan['pembeli'] = $request['pembeli'];
            $dataPenjualan['penjualan_kode'] = $request['penjualan_kode'];
            $dataPenjualan['user_id'] = auth()->user()->user_id;
            $dataPenjualan['penjualan_tanggal'] = now();
            $dataPenjualan['created_at'] = now();
            $dataPenjualan['updated_at'] = now();

            // dd($dataPenjualan['pembeli']);
            $idPenjualan = 0;
            try {
                $idPenjualan = PenjualanModel::create($dataPenjualan)->penjualan_id;

            } catch (\Throwable $th) {
                return response()->json([
                    // 'status' => true,
                    'status' => false,
                    'message' => 'Gagal Disimpan'
                ]);
            }

            if ($idPenjualan == 0) {
                return response()->json([
                    // 'status' => true,
                    'status' => false,
                    'message' => 'Gagal Disimpan'
                ]);
            }

            $jumlahBarang = count($request->barang_id);

            for ($i = 0; $i < $jumlahBarang; $i++) {
                $dataDetail['penjualan_id'] = $idPenjualan;
                $dataDetail['barang_id'] = $request['barang_id'][$i];
                $dataDetail['harga'] = $request['harga'][$i];
                $dataDetail['jumlah'] = $request['jumlah'][$i];
                $dataDetail['created_at'] = now();
                $dataDetail['updated_at'] = now();

                $status = StokController::get_status_stok($dataDetail['jumlah'], $dataDetail['barang_id']);

                if ($status == false) {
                    return response()->json([
                        // 'status' => true,
                        'status' => false,
                        'message' => 'Stok Barang Tidak Cukup'
                    ]);
                }
                // PenjualanDetailModel::create($dataDetail);

                try {
                    PenjualanDetailModel::create($dataDetail);
                } catch (\Throwable $th) {
                    return response()->json([
                        // 'status' => true,
                        'status' => false,
                        'message' => 'Gagal Disimpan'
                    ]);
                }
            }
            // dd($jumlahBarang);

            return response()->json([
                // 'status' => true,
                'status' => true,
                'message' => 'Data penjualan berhasil disimpan'
            ]);
        }

        return redirect('/');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail.barang', 'user.level')->find($id);

        if (!$penjualan) {
            abort(404); // atau redirect dengan pesan error
        }

        $totalHarga = number_format($penjualan->penjualan_detail->map(function ($detail) {
            return $detail->harga * $detail->jumlah;
        })->sum(), 0, ',', '.');

        $user = $penjualan->user->mama . ' (' . $penjualan->user->level->level_kode . ')';

        return view('penjualan.show', [
            'penjualan' => $penjualan,
            'total_harga' => $totalHarga,
            'user' => $user
        ]);
    }

    public function export_detail_pdf(string $id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail.barang', 'user.level')->find($id);

        if (!$penjualan) {
            abort(404); // atau redirect dengan pesan error
        }

        $totalHarga = number_format($penjualan->penjualan_detail->map(function ($detail) {
            return $detail->harga * $detail->jumlah;
        })->sum(), 0, ',', '.');

        $user = $penjualan->user->mama . ' (' . $penjualan->user->level->level_kode . ')';


        $pdf = Pdf::loadView('penjualan.export_detail_pdf', [
            'penjualan' => $penjualan,
            'total_harga' => $totalHarga,
            'user' => $user
        ]);
        $pdf->setPaper('a4', 'portrait'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Detail Penjualan ' . date('Y-m-d H:i:s') . '.pdf');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail.barang', 'user.level')->find($id);
    
        if (!$penjualan) {
            abort(404); // atau redirect dengan pesan error
        }
    
        // Ambil hanya barang dengan real stok > 0
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual')
            ->with('stok')
            ->get()
            ->filter(function ($brg) {
                return $brg->real_stok > 0;
            })
            ->values();
    
        return view('penjualan.edit')->with([
            'penjualan' => $penjualan,
            'barang' => $barang
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'pembeli' => 'required|string|max:100',
                'penjualan_kode' => 'required|string|max:5|unique:t_penjualan,penjualan_kode,' . $id . ',penjualan_id',
                'barang_id_awal' => 'array',
                'barang_id_awal.*' => 'integer|exists:m_barang,barang_id',
                'barang_id_baru' => 'array',
                'barang_id_baru.*' => 'integer|exists:m_barang,barang_id',
                'jumlah' => 'array',
                'jumlah.*' => 'integer',
                'harga' => 'array',
                'harga.*' => 'integer',
                'barang_id_new' => 'array',
                'barang_id_new.*' => 'integer|exists:m_barang,barang_id',
                'jumlah_new' => 'array',
                'jumlah_new.*' => 'integer',
                'harga_new' => 'array',
                'harga_new.*' => 'integer',
                'status' => 'array',
                'status.*' => 'integer',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';
                if ($validator->errors()->has('penjualan_kode')) {
                    $errorMessage = 'Validasi Gagal (Kode Sudah Digunakan)';
                }

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors(),
                ]);
            }


            $dataPenjualan['pembeli'] = $request['pembeli'];
            $dataPenjualan['penjualan_kode'] = $request['penjualan_kode'];
            $dataPenjualan['penjualan_tanggal'] = now();
            // $dataPenjualan['created_at'] = now();
            $dataPenjualan['updated_at'] = now();

            // dd($dataPenjualan['pembeli']);
            try {
                $penjualan = PenjualanModel::findOrFail($id);
                $userIdLama = $penjualan->user_id;
                $dataPenjualan['user_id'] = $userIdLama;

                $penjualan->update($dataPenjualan);

            } catch (\Throwable $th) {
                return response()->json([
                    // 'status' => true,
                    'status' => false,
                    'message' => 'Update Gagal Disimpan'
                ]);
            }

            if (isset($request['barang_id_awal']) && is_array($request['barang_id_awal']) && count($request['barang_id_awal']) > 0) {
                $jumlahEditBarang = count($request->barang_id_baru);
                for ($i = 0; $i < $jumlahEditBarang; $i++) {
                    $idDetail = $request['detail_id'][$i];
                    $detailModel = PenjualanDetailModel::findOrFail($idDetail);

                    $dataDetail['penjualan_id'] = $id;
                    $dataDetail['barang_id'] = $request['barang_id_baru'][$i];
                    $dataDetail['harga'] = $request['harga'][$i];
                    $dataDetail['jumlah'] = $request['jumlah'][$i];
                    $dataDetail['created_at'] = now();
                    $dataDetail['updated_at'] = now();

                    // $status = StokController::update_stok_edit($request['status'][$i], $dataDetail['barang_id']);
                    $status = true;
                    if ($request['status'][$i] > 0) {
                        $status = StokController::get_status_stok($request['status'][$i], $dataDetail['barang_id']);
                    }

                    if ($status == false) {
                        return response()->json([
                            // 'status' => true,
                            'status' => false,
                            'message' => 'Stok Barang Tidak Cukup'
                        ]);
                    }
                    // PenjualanDetailModel::create($dataDetail);

                    try {
                        $detailModel->update($dataDetail);
                    } catch (\Throwable $th) {
                        return response()->json([
                            // 'status' => true,
                            'status' => false,
                            'message' => 'Gagal Disimpan'
                        ]);
                    }
                }
            }



            if (isset($request['barang_id_new']) && is_array($request['barang_id_new']) && count($request['barang_id_new']) > 0) {
                $jumlahBarangBaru = count($request->barang_id_new);

                for ($i = 0; $i < $jumlahBarangBaru; $i++) {
                    $dataDetail['penjualan_id'] = $id;
                    $dataDetail['barang_id'] = $request['barang_id_new'][$i];
                    $dataDetail['harga'] = $request['harga_new'][$i];
                    $dataDetail['jumlah'] = $request['jumlah_new'][$i];
                    $dataDetail['created_at'] = now();
                    $dataDetail['updated_at'] = now();

                    // $status = StokController::update_stok($dataDetail['jumlah'], $dataDetail['barang_id']);
                    $status = StokController::get_status_stok($dataDetail['jumlah'], $dataDetail['barang_id']);

                    if ($status == false) {
                        return response()->json([
                            // 'status' => true,
                            'status' => false,
                            'message' => 'Stok Barang Tidak Cukup'
                        ]);
                    }
                    // PenjualanDetailModel::create($dataDetail);

                    try {
                        PenjualanDetailModel::create($dataDetail);
                    } catch (\Throwable $th) {
                        return response()->json([
                            // 'status' => true,
                            'status' => false,
                            'message' => 'Gagal Disimpan'
                        ]);
                    }
                }
            }


            if (isset($request['detail_id_dihapus']) && is_array($request['detail_id_dihapus']) && count($request['detail_id_dihapus']) > 0) {
                $jumlahHapusBarang = count($request->detail_id_dihapus);
                for ($i = 0; $i < $jumlahHapusBarang; $i++) {

                    // $status = StokController::update_stok_edit(-$request['jumlah_dihapus'][$i], $request['barang_id_dihapus'][$i]);

                    // if ($status == false) {
                    //     return response()->json([
                    //         // 'status' => true,
                    //         'status' => false,
                    //         'message' => 'Stok Barang Tidak Cukup'
                    //     ]);
                    // }
                    PenjualanDetailModel::whereIn('detail_id', $request['detail_id_dihapus'])->delete();

                    try {
                        $detailModel->update($dataDetail);
                    } catch (\Throwable $th) {
                        return response()->json([
                            // 'status' => true,
                            'status' => false,
                            'message' => 'Gagal Disimpan'
                        ]);
                    }
                }
            }

            return response()->json([
                // 'status' => true,
                'status' => true,
                'message' => 'Data penjualan berhasil disimpan'
            ]);
        }

        return redirect('/');
    }


    public function confirm_delete(string $id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail.barang', 'user.level')->find($id);

        if (!$penjualan) {
            abort(404); // atau redirect dengan pesan error
        }

        $totalHarga = number_format($penjualan->penjualan_detail->map(function ($detail) {
            return $detail->harga * $detail->jumlah;
        })->sum(), 0, ',', '.');

        $user = $penjualan->user->mama . ' (' . $penjualan->user->level->level_kode . ')';

        return view('penjualan.confirm_delete', [
            'penjualan' => $penjualan,
            'total_harga' => $totalHarga,
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $penjualan = PenjualanModel::find($id);
            if ($penjualan) {
                try {
                    $penjualanDetail = PenjualanDetailModel::where('penjualan_id', $id)->get();

                    // dd($penjualanDetail);

                    foreach ($penjualanDetail as $key => $detail) {
                        // $idBarang = $detail->barang_id;
                        $status = StokController::update_stok_edit(-$detail->jumlah, $detail->barang_id);
                        if ($status == false) {
                            return response()->json([
                                // 'status' => true,
                                'status' => false,
                                'message' => 'Stok Barang Tidak Ada'
                            ]);
                        }
                        // StokController::update_stok_edit()
                    }
                    PenjualanDetailModel::where('penjualan_id', $id)->delete();
                    $penjualan->delete();
                    return response()->json([
                        'status' => true,
                        'message' => 'Data berhasil dihapus'
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Penjualan gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }

    public function import()
    {
        return view('penjualan.import');
    }


    public function import_excel(Request $request)
    {
        // return 'hallo';
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_penjualan' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }


            $file = $request->file('file_penjualan');

            if (!$file->isValid()) {
                return response()->json(['error' => 'Invalid file'], 400);
            }

            // Nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();

            // Pastikan folder penyimpanan ada
            $destinationPath = storage_path('app/public/file_penjualan');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0775, true);
            }

            $file->move($destinationPath, $filename);

            $filePathRelative = "file_penjualan/$filename";
            $filePath = storage_path("app/public/file_penjualan/$filename"); // Simpan path gambar

            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($filePath); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel
            // dd($data);


            // Hapus file setelah proses
            if (Storage::disk('public')->exists($filePathRelative)) {
                Storage::disk('public')->delete($filePathRelative);
            }

            $insert = [];
            $jumlahBarisData = 0;

            // Tangkap Nama Pembeli dan Kode
            $nama_pembeli = trim($data[1]['B'] ?? ''); // Baris 1 kolom B
            $penjualan_kode = trim($data[2]['B'] ?? ''); // Baris 2 kolom B
            $user_id = auth()->user()->user_id;


            // dd($nama_pembeli);

            if (!$nama_pembeli || !$penjualan_kode) {
                return response()->json([
                    'status' => false,
                    'message' => "Nama pembeli atau Kode Kosong"
                ]);
            }
            // Validasi Header Baris ke-3 (index ke-2)
            $expectedHeader = ['A' => 'barang_id', 'B' => 'jumlah'];
            $actualHeader = $data[3] ?? [];

            foreach ($expectedHeader as $col => $expected) {
                if (strtolower(trim($actualHeader[$col] ?? '')) !== strtolower($expected)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Header kolom $col tidak valid. Harus '$expected'."
                    ]);
                }
            }

            // Cek apakah penjualan_kode sudah ada di database
            $penjualanExists = PenjualanModel::where('penjualan_kode', $penjualan_kode)->exists();

            if (!$penjualanExists) {
                foreach ($data as $baris => $value) {
                    if ($baris >= 4) { // Mulai dari baris ke-4 (index ke-3)
                        $jumlahBarisData++;

                        $barang_id = trim($value['A'] ?? '');
                        $jumlah = trim($value['B'] ?? '');
                        $harga = BarangModel::where('barang_id', $barang_id)->value('harga_jual');
                        // Cek Stok
                        if (!StokController::cek_stok($jumlah, $barang_id)) {
                            return response()->json([
                                'status' => false,
                                'message' => "Terdapat barang yang stoknya tidak ada/kurang"
                            ]);
                        }

                        if ($barang_id && $jumlah) {
                            $insert[] = [
                                // 'nama_pembeli' => $idPenjualan,
                                'barang_id' => $barang_id,
                                'jumlah' => $jumlah,
                                'harga' => $harga,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // Insert ke database
                if (!empty($insert)) {
                    try {
                        $idPenjualan = PenjualanModel::create([
                            'pembeli' => $nama_pembeli,
                            'penjualan_kode' => $penjualan_kode,
                            'user_id' => $user_id,
                            'penjualan_tanggal' => now()
                        ])->penjualan_id;

                        // dd($idPenjualan);
                        for ($i = 0; $i < count($insert); $i++) {
                            $insert[$i]['penjualan_id'] = $idPenjualan;
                            StokController::update_stok($insert[$i]['jumlah'], $insert[$i]['barang_id']);
                        }

                        PenjualanDetailModel::insert($insert);
                        return response()->json([
                            'status' => true,
                            'message' => 'Data Penjualan Berhasil Diimport',
                        ]);

                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Terjadi kesalahan "Data Tidak Valid"',
                            'error' => $e->getMessage()
                        ]);
                    }

                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Tidak ada data untuk di import"
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Penjualan dengan kode '$penjualan_kode' sudah ada."
                ]);
            }
        }

        return redirect('/');
    }

    public function export_excel()
    {
        $penjualans = PenjualanModel::select(
            't_penjualan.penjualan_id',
            't_penjualan.penjualan_kode',
            't_penjualan.pembeli',
            't_penjualan.penjualan_tanggal',
            't_penjualan.user_id',
        )->with('penjualan_detail', 'user.level')->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode');
        $sheet->setCellValue('C1', 'Pembeli');
        $sheet->setCellValue('D1', 'Total Harga');
        $sheet->setCellValue('E1', 'Tanggal');
        $sheet->setCellValue('F1', 'Diproses Oleh');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $no = 1;
        $baris = 2;

        foreach ($penjualans as $data) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $data->penjualan_kode ?? '-'); // Kode Penjualan
            $sheet->setCellValue('C' . $baris, $data->pembeli ?? '-'); // Nama Pembeli

            // Hitung total harga berdasarkan detail penjualan (harga * jumlah)
            $totalHarga = $data->penjualan_detail->sum(function ($detail) {
                return $detail->harga * $detail->jumlah;
            });
            $sheet->setCellValue('D' . $baris, 'Rp' . number_format($totalHarga, 0, ',', '.') . ',00'); // Total Harga

            // Format tanggal
            $sheet->setCellValue('E' . $baris, \Carbon\Carbon::parse($data->penjualan_tanggal)->format('d-m-Y H:i:s'));

            // Nama user + kode level (jika ada)
            if ($data->user && $data->user->level) {
                $sheet->setCellValue('F' . $baris, $data->user->nama . ' (' . $data->user->level->level_kode . ')');
            } else {
                $sheet->setCellValue('F' . $baris, '-');
            }

            $no++;
            $baris++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Penjualan');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Penjualan_' . date('Y-m-d H:i:s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $penjualans = PenjualanModel::select(
            't_penjualan.penjualan_id',
            't_penjualan.penjualan_kode',
            't_penjualan.pembeli',
            't_penjualan.penjualan_tanggal',
            't_penjualan.user_id',
        )->with('penjualan_detail', 'user.level')->get();

        $pdf = Pdf::loadView('penjualan.export_pdf', ['penjualan' => $penjualans]);
        $pdf->setPaper('a4', 'landscape'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data Penjualan ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
