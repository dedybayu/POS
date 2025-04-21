@empty($penjualan)
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
            <a href="{{ url('/penjualan') }}" class="btn btn-warning">Kembali</a>
        </div>
    </div>
</div>
@else
    <form action="{{ url('/penjualan/' . $penjualan->penjualan_id) }}" method="POST" id="form-delete">
        @csrf
        @method('DELETE')
        <div class="modal-header">
            <h5 class="modal-title">Delete Data Penjualan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-ban"></i> Konfirmasi !!!</h5>
                Apakah Anda ingin menghapus data seperti di bawah ini?
            </div>
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
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
            <button type="submit" class="btn btn-primary">Ya, Hapus</button>
        </div>
    </form>
    <script>
        $(document).ready(function () {
            $("#form-delete").validate({
                rules: {},
                submitHandler: function (form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        success: function (response) {
                            if (response.status) {
                                $('#modal-crud').modal('hide');
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                                dataPenjualan.ajax.reload();
                            } else {
                                $('.error-text').text('');
                                $.each(response.msgField, function (prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: response.message });
                            }
                        }
                    });
                    return false;
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endempty