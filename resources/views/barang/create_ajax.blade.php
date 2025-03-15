<form action="{{ url('/barang/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Tambah Data barang</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label>Kategori Barang</label>
            <select name="kategori_id" id="kategori_id" class="form-control" required>
                <option value="">- Pilih kategori -</option>
                @foreach($kategori as $l)
                    <option value="{{ $l->kategori_id }}">{{ $l->kategori_nama }}</option>
                @endforeach
            </select>
            <small id="error-kategori_id" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" name="barang_nama" id="barang_nama" class="form-control" required>
            <small id="error-nama" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Kode Barang</label>
            <input type="text" name="barang_kode" id="barang_kode" class="form-control" required>
            <small id="error-kode" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Harga Beli</label>
            <input type="number" name="harga_beli" id="harga_beli" class="form-control" required>
            <small id="error-harga_beli" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Harga Jual</label>
            <input type="number" name="harga_jual" id="harga_jual" class="form-control" required>
            <small id="error-harga_jual" class="error-text form-text text-danger"></small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $("#form-tambah").validate({
            rules: {
                kategori_id: { required: true, number: true },
                barang_nama: { required: true, minlength: 3, maxlength: 100 },
                barang_kode: { required: true, minlength: 3, maxlength: 10 },
                harga_beli: { required: true, number: true },
                harga_jual: { required: true, number: true },
            },
            submitHandler: function(form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function(response) {
                        if (response.status) {
                            $('#modal-crud').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            dataBarang.ajax.reload();
                        } else {
                            $('.error-text').text('');
                            $.each(response.msgField, function(prefix, val) {
                                $('#error-' + prefix).text(val[0]);
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: response.message
                            });
                        }
                    }
                });
                return false;
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
