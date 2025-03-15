@empty($barang)
    <div id="modal-crud" class="modal-dialog modal-lg" role="document">
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
                <a href="{{ url('/barang') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/barang/' . $barang->barang_id . '/update_ajax') }}" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div class="modal-header">
            <h5 class="modal-title">Edit Data Barang</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Kategori Barang</label>
                <select name="kategori_id" id="kategori_id" class="form-control" required>
                    <option value="">- Pilih Kategori -</option>
                    @foreach($kategori as $l)
                        <option {{ ($l->kategori_id == $barang->kategori_id) ? 'selected' : '' }} value="{{ $l->kategori_id }}">
                            {{ $l->kategori_nama }}
                        </option>
                    @endforeach
                </select>
                <small id="error-kategori_id" class="error-text form-text text-danger"></small>
            </div>
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="barang_nama" id="barang_nama" class="form-control" value="{{$barang->barang_nama}}"
                    required>
                <small id="error-nama" class="error-text form-text text-danger"></small>
            </div>
            <div class="form-group">
                <label>Kode Barang</label>
                <input type="text" name="barang_kode" id="barang_kode" class="form-control" value="{{$barang->barang_kode}}"
                    required>
                <small id="error-kode" class="error-text form-text text-danger"></small>
            </div>
            <div class="form-group">
                <label>Harga Beli</label>
                <input type="number" name="harga_beli" id="harga_beli" class="form-control" value="{{$barang->harga_beli}}"
                    required>
                <small id="error-harga_beli" class="error-text form-text text-danger"></small>
            </div>
            <div class="form-group">
                <label>Harga Jual</label>
                <input type="number" name="harga_jual" id="harga_jual" class="form-control" value="{{$barang->harga_jual}}"
                    required>
                <small id="error-harga_jual" class="error-text form-text text-danger"></small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>

    <script>
        $(document).ready(function () {
            $("#form-edit").validate({
                rules: {
                    kategori_id: { required: true, number: true },
                    barang_nama: { required: true, minlength: 3, maxlength: 100 },
                    barang_kode: { required: true, minlength: 3, maxlength: 10 },
                    harga_beli: { required: true, number: true },
                    harga_jual: { required: true, number: true },
                },
                submitHandler: function (form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        success: function (response) {
                            if (response.status) {
                                $('#modal-crud').modal('hide');
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message });
                                dataBarang.ajax.reload();
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