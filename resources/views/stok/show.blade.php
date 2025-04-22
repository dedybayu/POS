@empty($stok)
    <div id="modal-delete" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Kesalahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
                    Data yang anda cari tidak ditemukan
                </div>
                <a href="{{ url('/stok') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">Data stok</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <table class="table table-sm table-bordered table-striped">
            <tr>
                <th class="text-right col-3">Nama Barang :</th>
                <td class="col-9">{{ $stok->barang->barang_nama }}</td>
            </tr>
            <tr>
                <th class="text-right col-3">Kode Barang :</th>
                <td class="col-9">{{ $stok->barang->barang_kode }}</td>
            </tr>
            <tr>
                <th class="text-right col-3">Kategori Barang :</th>
                <td class="col-9">{{ $stok->barang->kategori->kategori_nama }}</td>
            </tr>
            <tr>
                <th class="text-right col-3">Stok :</th>
                <td class="col-9">{{ $stok->stok_jumlah }}</td>
            </tr>
            <tr>
                <th class="text-right col-3">Penanggungjawab :</th>
                <td class="col-9">{{ $stok->user->nama }} ({{ $stok->user->level->level_kode }})</td>
            </tr>
            <tr>
                <th class="text-right col-3">Tanggal :</th>
                <td class="col-9">{{ \Carbon\Carbon::parse($stok->stok_tanggal)->format('d-m-Y H:i:s') }}</td>
            </tr>
        </table>
    </div>
    <div class="modal-footer">
        @if(Auth::check() && in_array(Auth::user()->getRole(), ['ADM', 'MNG']))
            <button onclick="modalAction('{{ url('/stok/' . $stok->stok_id . '/tambah') }}')"
                class="btn btn-warning btn-sm">Tambah
            </button>
            <button onclick="modalAction('{{ url('/stok/' . $stok->stok_id . '/edit') }}')" class="btn btn-success btn-sm">Edit
            </button>
        @endif
        <button type="button" data-dismiss="modal" class="btn btn-primary btn-sm">Close</button>
    </div>
@endempty