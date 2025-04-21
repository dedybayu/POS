<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanDetailModel;
use App\Models\PenjualanModel;
use App\Models\StokModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
                $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/show') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/edit') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
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
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual')
            ->with('stok') // ambil stok terbaru
            ->whereHas('stok')
            ->get();

        // dd($barang);
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

                $status = StokController::update_stok($dataDetail['jumlah'], $dataDetail['barang_id']);

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
                'status' => false,
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


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $penjualan = PenjualanModel::with('penjualan_detail.barang', 'user.level')->find($id);

        if (!$penjualan) {
            abort(404); // atau redirect dengan pesan error
        }
        // dd($id);
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual')->get();

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
        dd($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PenjualanModel $penjualanModel)
    {
        //
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
