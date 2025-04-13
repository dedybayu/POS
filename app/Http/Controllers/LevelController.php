<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Storage;
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


    public function show_ajax(string $id)
    {
        $level = LevelModel::find($id);

        return view('level.show_ajax', ['level' => $level]);
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


    public function edit_ajax(string $id)
    {
        // return "Berhasil";
        $level = LevelModel::find($id);

        return view('level.edit_ajax', ['level' => $level]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_nama' => 'required|string|max:100',
                'level_kode' => 'required|string|max:5|unique:m_level,level_kode,' . $id . ',level_id'
            ];
    
            $messages = [
                'level_kode.unique' => 'Kode Sudah Digunakan'
            ];
    
            $validator = Validator::make($request->all(), $rules, $messages);
    
            if ($validator->fails()) {
                $errorMessage = 'Validasi Gagal';
                if ($validator->errors()->has('level_kode')) {
                    $errorMessage = 'Validasi Gagal (Kode Sudah Digunakan)';
                }
    
                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'msgField' => $validator->errors()
                ]);
            }
    
            $level = LevelModel::find($id);
            if ($level) {
                $level->update($request->all());
                return response()->json(['status' => true, 'message' => 'Data level berhasil diperbarui']);
            } else {
                return response()->json(['status' => false, 'message' => 'Data level tidak ditemukan']);
            }
        }
        return redirect('/');
    }
    


    public function confirm_ajax(string $id)
    {
        $level = LevelModel::find($id);

        return view('level.confirm_ajax', ['level' => $level]);
    }

    public function delete_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $level = LevelModel::find($id);
            if ($level) {
                try {
                    $level->delete();
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


    public function import()
    {
        return view('level.import');
    }

    public function import_ajax(Request $request)
    {
        // return "HELLO";
        // dd($request->file('file_level')->getRealPath());
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_level' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }


            $file = $request->file('file_level');

            if (!$file->isValid()) {
                return response()->json(['error' => 'Invalid file'], 400);
            }

            // Nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();

            // Pastikan folder penyimpanan ada
            $destinationPath = storage_path('app/public/file_level');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0775, true);
            }

            $file->move($destinationPath, $filename);

            $filePathRelative = "file_level/$filename";
            $filePath = storage_path("app/public/file_level/$filename"); // Simpan path gambar

            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($filePath); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel


            // Validasi Header
            $expectedHeader = ['A' => 'level_kode', 'B' => 'level_nama'];
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

            $existingCodes = LevelModel::pluck('level_kode')->toArray();
            $insert = [];
            $excelCodes = []; // Cek duplikat dalam Excel
            $jumlahBarisData = 0;

            foreach ($data as $baris => $value) {
                if ($baris > 1) {
                    $jumlahBarisData++;

                    $kode = trim($value['A'] ?? '');
                    $nama = trim($value['B'] ?? '');

                    // Validasi data tidak kosong dan unik
                    if (
                        $kode && $nama &&
                        !in_array($kode, $existingCodes) &&
                        !in_array($kode, $excelCodes)
                    ) {
                        $insert[] = [
                            'level_kode' => $kode,
                            'level_nama' => $nama,
                            'created_at' => now(),
                        ];
                        $excelCodes[] = $kode;
                    }
                }
            }

            if (count($insert) > 0) {
                try {
                    LevelModel::insert($insert);
                    if (count($insert) == $jumlahBarisData) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Semua (' . count($insert) . ') data berhasil diimport'
                        ]);
                    } else {
                        return response()->json([
                            'status' => true,
                            'message' => count($insert) . ' data berhasil diimport, namun beberapa data gagal karena duplikasi kode atau data tidak lengkap'
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
                    'message' => 'Tidak ada data yang valid untuk diimport (semua data duplikat atau tidak valid)'
                ]);
            }
        }

        return redirect('/');
    }

    public function export_excel()
    {
        $level = LevelModel::select('level_id', 'level_kode', 'level_nama')
            ->orderBy('level_id')
            ->orderBy('level_kode')
            ->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode level');
        $sheet->setCellValue('C1', 'Nama level');

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $no = 1;
        $baris = 2;

        foreach ($level as $key => $data) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $data->level_kode);
            $sheet->setCellValue('C' . $baris, $data->level_nama);
            $no++;
            $baris++;
        }

        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data level');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data level_' . date('Y-m-d H:i:s') . '.xlsx';

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
        $level = LevelModel::select('level_id', 'level_kode', 'level_nama')
            ->orderBy('level_id')
            ->orderBy('level_kode')
            ->get();

        // use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = Pdf::loadView('level.export_pdf', ['level' => $level]);
        $pdf->setPaper('a4', 'portrait'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data level ' . date('Y-m-d H:i:s') . '.pdf');
    }
}