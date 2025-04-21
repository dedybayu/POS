<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Storage;
use Validator;
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
            $stoks->where('t_stok.supplier_id', $request->supplier_id);
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
                $btn = '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id)
                    . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/edit') . '\')" class="btn btn-success btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/tambah') . '\')" class="btn btn-warning btn-sm">Tambah</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/delete') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama', 'supplier_kode')->get();
        // Ambil daftar barang_id yang sudah ada di stok
        // $barangSudahAdaStok = StokModel::pluck('barang_id');

        // Ambil hanya barang yang belum ada di stok
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_beli')
            // ->whereNotIn('barang_id', $barangSudahAdaStok)
            ->get();

        return view('stok.create', compact('barang', 'supplier'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id' => 'required',
                'barang_id' => [
                    'required',
                    Rule::unique('t_stok')->where(function ($query) use ($request) {
                        return $query->where('supplier_id', $request->supplier_id);
                    }),
                ],
                'stok_jumlah' => 'required',
            ];

            $messages = [
                'barang_id.unique' => 'Kombinasi Supplier dan Barang sudah ada.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';

                if ($validator->errors()->has('supplier_id')) {
                    $errorMessage = 'Supplier harus dipilih.';
                } elseif ($validator->errors()->has('barang_id')) {
                    if (in_array('Kombinasi Supplier dan Barang sudah ada.', $validator->errors()->get('barang_id'))) {
                        $errorMessage = 'Kombinasi Supplier dan Barang sudah ada.';
                    } else {
                        $errorMessage = 'Barang harus dipilih.';
                    }
                } elseif ($validator->errors()->has('stok_jumlah')) {
                    $errorMessage = 'Jumlah stok wajib diisi.';
                }


                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors(),
                ]);
            }

            $data = $request->all();
            $data['user_id'] = auth()->user()->user_id; // gunakan user() bukan user_id langsung
            $data['stok_tanggal'] = now(); // gunakan user() bukan user_id langsung

            StokModel::create($data);


            return response()->json([
                'status' => true,
                'message' => 'Data stok berhasil disimpan'
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stok = StokModel::with(['supplier', 'barang.kategori', 'user.level'])
            ->where('stok_id', $id)
            ->firstOrFail();

        return view('stok.show', compact('stok'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function tambah(string $id)
    {
        $stok = StokModel::with(['supplier', 'barang'])
            ->where('stok_id', $id)
            ->firstOrFail();

        return view('stok.tambah', compact('stok'));
    }

    public function update_tambah(Request $request, $id)
    {
        // dd($id);
        if ($request->ajax() || $request->wantsJson()) {
            $stokModel = StokModel::findOrFail($id); // Ambil data berdasarkan ID

            $rules = [
                'stok_jumlah' => 'required'
            ];

            $messages = [
                'stok_jumlah.required' => 'Jumlah stok wajib diisi.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'msgField' => $validator->errors(),
                ]);
            }

            $data = $request->all();
            $jumlah = $data['stok_jumlah'];

            $data['stok_jumlah'] = $stokModel->stok_jumlah + $jumlah;
            // dd($data['stok_jumlah']);
            $data['user_id'] = auth()->user()->user_id;
            $data['stok_tanggal'] = now();

            $stokModel->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Data stok berhasil ditambah',
            ]);
        }
    }

    public function edit(string $id)
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama', 'supplier_kode')->get();

        // Ambil hanya barang yang belum ada di stok
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_beli')
            ->get();

        $stok = StokModel::with(['supplier', 'barang'])
            ->where('stok_id', $id)
            ->firstOrFail();

        return view('stok.edit', compact('stok', 'barang', 'supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $stokModel = StokModel::findOrFail($id); // Ambil data berdasarkan ID

            $rules = [
                'supplier_id' => 'required',
                'barang_id' => [
                    'required',
                    Rule::unique('t_stok')
                        ->where(function ($query) use ($request) {
                            return $query->where('supplier_id', $request->supplier_id);
                        })
                        ->ignore($id, 'stok_id'), // Abaikan data yang sedang diupdate
                ],
                'stok_jumlah' => 'required',
            ];

            $messages = [
                'supplier_id.required' => 'Supplier harus dipilih.',
                'barang_id.required' => 'Barang harus dipilih.',
                'barang_id.unique' => 'Kombinasi Supplier dan Barang sudah ada.',
                'stok_jumlah.required' => 'Jumlah stok wajib diisi.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'msgField' => $validator->errors(),
                ]);
            }

            $data = $request->all();
            $data['user_id'] = auth()->user()->user_id;
            $data['stok_tanggal'] = now();

            $stokModel->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Data stok berhasil diperbarui',
            ]);
        }
    }


    public function confirm_delete(string $id)
    {
        $stok = StokModel::with(['supplier', 'barang.kategori', 'user.level'])
            ->where('stok_id', $id)
            ->firstOrFail();

        return view('stok.confirm_delete', compact('stok'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $stok = StokModel::find($id);
            if ($stok) {
                try {
                    $stok->delete();
                    return response()->json([
                        'status' => true,
                        'message' => 'Data berhasil dihapus'
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data user gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
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
        return view('stok.import');
    }

    public function import_excel(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }


            $file = $request->file('file_stok');

            if (!$file->isValid()) {
                return response()->json(['error' => 'Invalid file'], 400);
            }

            // Nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();

            // Pastikan folder penyimpanan ada
            $destinationPath = storage_path('app/public/file_stok');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0775, true);
            }

            $file->move($destinationPath, $filename);

            $filePathRelative = "file_stok/$filename";
            $filePath = storage_path("app/public/file_stok/$filename"); // Simpan path gambar

            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($filePath); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel


            // Validasi Header
            $expectedHeader = ['A' => 'supplier_id', 'B' => 'barang_id', 'C' => 'stok_jumlah'];
            $actualHeader = $data[1] ?? [];

            foreach ($expectedHeader as $col => $expected) {
                if (trim($actualHeader[$col] ?? '') !== $expected) {
                    return response()->json([
                        'status' => false,
                        'message' => "Header kolom $col tidak valid. Harus '$expected'."
                    ]);
                }
            }
            // $insert = [];

            // Hapus Kembali File Upload dengan Storage::disk('public')->delete()
            if (Storage::disk('public')->exists($filePathRelative)) {
                Storage::disk('public')->delete($filePathRelative);
            }

            $insert = [];
            $jumlahBarisData = 0;
            $seenCombinations = [];

            foreach ($data as $baris => $value) {
                if ($baris > 1) {
                    $jumlahBarisData++;

                    $supplier_id = trim($value['A'] ?? '');
                    $barang_id = trim($value['B'] ?? '');
                    $stok_jumlah = trim($value['C'] ?? '');

                    $combinationKey = $supplier_id . '-' . $barang_id;

                    if (
                        $supplier_id && $barang_id && $stok_jumlah &&
                        !in_array($combinationKey, $seenCombinations)
                    ) {
                        // Cek di database apakah kombinasi ini sudah ada
                        $exists = StokModel::where('supplier_id', $supplier_id)
                            ->where('barang_id', $barang_id)
                            ->exists();


                        if (!$exists) {
                            $insert[] = [
                                'supplier_id' => $supplier_id,
                                'barang_id' => $barang_id,
                                'stok_jumlah' => $stok_jumlah,
                                'user_id' => auth()->user()->user_id,
                                'stok_tanggal' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $seenCombinations[] = $combinationKey;
                        }
                    }
                }
            }


            if (count($insert) > 0) {
                try {
                    StokModel::insert($insert);
                    if (count($insert) == $jumlahBarisData) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Semua (' . count($insert) . ') data berhasil diimport'
                        ]);
                    } else {
                        return response()->json([
                            'status' => true,
                            'message' => count($insert) . ' data berhasil diimport, namun beberapa data gagal karena duplikasi kombinasi barang dan supplier atau data tidak lengkap'
                        ]);
                    }
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
                    'message' => 'Tidak ada data yang valid untuk diimport (semua kombinasi barang dan supplier duplikat atau tidak valid)'
                ]);
            }
        }

        return redirect('/');
    }


    public function export_excel()
    {
        $stoks = StokModel::select(
            't_stok.stok_id',
            't_stok.supplier_id',
            't_stok.barang_id',
            't_stok.user_id',
            't_stok.stok_tanggal',
            't_stok.stok_jumlah'
        )->with('supplier', 'barang.stok', 'user.level')->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Barang');
        $sheet->setCellValue('C1', 'Kode');
        $sheet->setCellValue('D1', 'stok');
        $sheet->setCellValue('E1', 'Stok');
        $sheet->setCellValue('F1', 'Supplier');
        $sheet->setCellValue('G1', 'User');
        $sheet->setCellValue('H1', 'Tanggal');

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $no = 1;
        $baris = 2;

        foreach ($stoks as $data) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $data->barang->barang_nama ?? '-'); // Nama barang
            $sheet->setCellValue('C' . $baris, $data->barang->barang_kode ?? '-'); // Kode barang
            $sheet->setCellValue('D' . $baris, $data->barang->stok->stok_nama ?? '-'); // stok
            $sheet->setCellValue('E' . $baris, $data->stok_jumlah); // Stok
            $sheet->setCellValue('F' . $baris, $data->supplier->supplier_nama ?? '-'); // Supplier
            if ($data->user && $data->user->level) {
                $sheet->setCellValue('G' . $baris, $data->user->nama . ' (' . $data->user->level->level_kode . ')');
            } else {
                $sheet->setCellValue('G' . $baris, '-');
            }
            $sheet->setCellValue('H' . $baris, \Carbon\Carbon::parse($data->stok_tanggal)->format('d-m-Y')); // Tanggal

            $no++;
            $baris++;
        }
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Stok');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Stok_' . date('Y-m-d H:i:s') . '.xlsx';

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
        $stoks = StokModel::select(
            't_stok.stok_id',
            't_stok.supplier_id',
            't_stok.barang_id',
            't_stok.user_id',
            't_stok.stok_tanggal',
            't_stok.stok_jumlah'
        )->with('supplier', 'barang.stok', 'user.level')->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = Pdf::loadView('stok.export_pdf', ['stok' => $stoks]);
        $pdf->setPaper('a4', 'landscape'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data Stok ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public static function update_stok(int $jumlah, $barang_id)
    {
        while ($jumlah > 0) {
            // Ambil stok pertama yang masih punya stok
            $stok = StokModel::where('barang_id', $barang_id)
                ->where('stok_jumlah', '>', 0)
                ->orderBy('stok_tanggal', 'asc') // FIFO
                ->first();

            // Jika tidak ada stok tersisa, hentikan
            if (!$stok) {
                return false;
            }

            if ($stok->stok_jumlah >= $jumlah) {
                // cukup di stok ini
                $stok->stok_jumlah -= $jumlah;
                $stok->save();
                $jumlah = 0;
            } else {
                // habiskan stok ini dan lanjutkan loop
                $jumlah -= $stok->stok_jumlah;
                $stok->stok_jumlah = 0;
                $stok->save();
            }
        }

        return true;
    }

    public static function update_stok_edit(int $jumlah, $barang_id)
    {
        if ($jumlah === 0) {
            return true;
        }

        // KURANGI STOK (jumlah positif)
        if ($jumlah > 0) {
            while ($jumlah > 0) {
                $stok = StokModel::where('barang_id', $barang_id)
                    ->where('stok_jumlah', '>', 0)
                    ->orderBy('stok_tanggal', 'asc') // FIFO
                    ->first();

                if (!$stok) {
                    return false; // Tidak ada stok tersisa
                }

                if ($stok->stok_jumlah >= $jumlah) {
                    $stok->stok_jumlah -= $jumlah;
                    $stok->save();
                    $jumlah = 0;
                } else {
                    $jumlah -= $stok->stok_jumlah;
                    $stok->stok_jumlah = 0;
                    $stok->save();
                }
            }
        }
        // TAMBAH STOK (jumlah negatif)
        else {
            $jumlah = abs($jumlah);
            // Tambahkan ke stok terakhir
            $stok = StokModel::where('barang_id', $barang_id)
                ->orderBy('stok_tanggal', 'desc') // LIFO
                ->first();

            if ($stok) {
                $stok->stok_jumlah += $jumlah;
                $stok->save();
            } else {
                // Jika tidak ada stok, bisa buat baru jika diperlukan
                // Misalnya:
                StokModel::create([
                    'barang_id' => $barang_id,
                    'stok_jumlah' => $jumlah,
                    'stok_tanggal' => now()
                ]);
            }
        }

        return true;
    }


}
