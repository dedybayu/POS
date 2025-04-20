<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
        dd($request);
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
        dd($id);
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


    public function import()
    {
        return view('user.import');
    }

    public function import_ajax(Request $request)
    {
        // if ($request->ajax() || $request->wantsJson()) {
        //     $rules = [
        //         'file_user' => ['required', 'mimes:xlsx', 'max:1024']
        //     ];
        //     $validator = Validator::make($request->all(), $rules);
        //     if ($validator->fails()) {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Validasi Gagal',
        //             'msgField' => $validator->errors()
        //         ]);
        //     }

        //     $file = $request->file('file_user');
        //     if (!$file->isValid()) {
        //         return response()->json(['error' => 'Invalid file'], 400);
        //     }

        //     $filename = time() . '_' . $file->getClientOriginalName();
        //     $destinationPath = storage_path('app/public/file_user');
        //     if (!file_exists($destinationPath)) {
        //         mkdir($destinationPath, 0775, true);
        //     }

        //     $file->move($destinationPath, $filename);
        //     $filePathRelative = "file_user/$filename";
        //     $filePath = storage_path("app/public/file_user/$filename");

        //     $reader = IOFactory::createReader('Xlsx');
        //     $reader->setReadDataOnly(true);
        //     $spreadsheet = $reader->load($filePath);
        //     $sheet = $spreadsheet->getActiveSheet();
        //     $data = $sheet->toArray(null, false, true, true);


        //     // Validasi Header
        //     $expectedHeader = ['A' => 'level_id', 'B' => 'username', 'C' => 'nama', 'D' => 'password'];
        //     $actualHeader = $data[1] ?? [];

        //     foreach ($expectedHeader as $col => $expected) {
        //         if (trim($actualHeader[$col] ?? '') !== $expected) {
        //             return response()->json([
        //                 'status' => false,
        //                 'message' => "Header kolom $col tidak valid. Harus '$expected'."
        //             ]);
        //         }
        //     }

        //     // Hapus file upload
        //     if (Storage::disk('public')->exists($filePathRelative)) {
        //         Storage::disk('public')->delete($filePathRelative);
        //     }

        //     if (count($data) <= 1) {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Tidak ada data yang diimport'
        //         ]);
        //     }

        //     $insert = [];
        //     $jumlahBarisData = 0;
        //     $message = '';
        //     $haveInvalid = false;

        //     foreach ($data as $baris => $value) {
        //         if ($baris > 1) {
        //             if($value['A'] == null || $value['B'] == null || $value['C'] == null || $value['D'] == null ){
        //                 $haveInvalid = true;
        //                 continue;
        //             }

        //             $jumlahBarisData++;

        //             $level_id = trim($value['A'] ?? '');
        //             $username = trim($value['B'] ?? '');
        //             $nama = trim($value['C'] ?? '');
        //             $password = trim($value['D'] ?? '');

        //             // Validasi data tidak kosong dan unik
        //             $insert[] = [
        //                 'level_id' => $level_id,
        //                 'username' => $username,
        //                 'nama' => $nama,
        //                 'password' => $password,
        //                 'created_at' => now(),
        //             ];
        //         }
        //     }

        //     if (count($insert) > 0) {
        //         try {
        //             UserModel::insert($insert);
        //             if (count($insert) == $jumlahBarisData && $haveInvalid == false) {
        //                 return response()->json([
        //                     'status' => true,
        //                     'message' => 'Semua (' . count($insert) . ') data berhasil diimport'
        //                 ]);
        //             } else {
        //                 return response()->json([
        //                     'status' => true,
        //                     'message' => count($insert) . ' data berhasil diimport, namun beberapa data gagal karena data tidak lengkap'
        //                 ]);
        //             }
        //         } catch (\Exception $e) {
        //             return response()->json([
        //                 'status' => false,
        //                 'message' => 'Terjadi kesalahan "Data Tidak Valid"',
        //                 'error' => $e->getMessage()
        //             ]);
        //         }
        //     } else {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Tidak ada data yang valid untuk diimport'
        //         ]);
        //     }
        // }

        // return redirect('/');
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
        )->with('supplier', 'barang.kategori', 'user.level')->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Barang');
        $sheet->setCellValue('C1', 'Kode');
        $sheet->setCellValue('D1', 'Kategori');
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
            $sheet->setCellValue('D' . $baris, $data->barang->kategori->kategori_nama ?? '-'); // Kategori
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
        )->with('supplier', 'barang.kategori', 'user.level')->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = Pdf::loadView('stok.export_pdf', ['stok' => $stoks]);
        $pdf->setPaper('a4', 'landscape'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data Stok ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
