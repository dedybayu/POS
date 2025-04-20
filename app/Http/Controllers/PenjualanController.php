<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
