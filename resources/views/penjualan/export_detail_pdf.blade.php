<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 6px 20px 5px 20px;
            line-height: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 4px 3px;
        }

        th {
            text-align: left;
        }

        .d-block {
            display: block;
        }

        img.image {
            width: auto;
            height: 80px;
            max-width: 150px;
            max-height: 150px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .p-1 {
            padding: 5px 1px 5px 1px;
        }

        .font-10 {
            font-size: 10pt;
        }

        .font-11 {
            font-size: 11pt;
        }

        .font-12 {
            font-size: 12pt;
        }

        .font-13 {
            font-size: 13pt;
        }

        .border-bottom-header {
            border-bottom: 1px solid;
        }

        .border-all,
        .border-all th,
        .border-all td {
            border: 1px solid;
        }
    </style>
</head>

<body>
    <table class="border-bottom-header">
        <tr>
            <td width="15%" class="text-center"><img src="{{ asset('img/Logo-Polinema.png')}}" width="80" height="80"></td>
            <td width="85%">
                <span class="text-center d-block font-11 font-bold mb-1">KEMENTERIAN
                    PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</span>
                <span class="text-center d-block font-13 font-bold mb-1">POLITEKNIK NEGERI
                    MALANG</span>
                <span class="text-center d-block font-10">Jl. Soekarno-Hatta No. 9 Malang
                    65141</span>
                <span class="text-center d-block font-10">Telepon (0341) 404424 Pes. 101-
                    105, 0341-404420, Fax. (0341) 404420</span>
                <span class="text-center d-block font-10">Laman: www.polinema.ac.id</span>
            </td>
        </tr>
    </table>
    <h3 class="text-center">LAPORAN DETAIL PENJUALAN</h4>
        <table class="table table-sm table-bordered table-striped">
            <tr>
                <th class="text-right col-3">Nama Pembeli :</th>
                <td class="col-9">{{ $penjualan->pembeli }}</td>
            </tr>
            <tr>
                <th class="text-right col-3">Kode Penjualan :</th>
                <td class="col-9">{{ $penjualan->penjualan_kode }}</td>
            </tr>
            <tr>
                <th class="text-right col-3">Total Harga :</th>
                <td class="col-9">Rp{{ $total_harga }},00</td>
            </tr>
            <tr>
                <th class="text-right col-3">Diproses Oleh :</th>
                <td class="col-9">{{ $penjualan->user->nama . ' (' . $penjualan->user->level->level_kode . ')' }}</td>
            </tr>
        </table>
        <h5 class="modal-title">Detail penjualan</h5>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penjualan->penjualan_detail as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $detail->barang->barang_nama ?? '-' }}</td>
                    <td>Rp{{ number_format($detail->harga, 0, ',', '.') }},00</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>Rp{{ number_format($detail->harga * $detail->jumlah, 0, ',', '.') }},00</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th>Rp{{ $total_harga }},00</th>
                </tr>
            </tfoot>
        </table>
</body>

</html>