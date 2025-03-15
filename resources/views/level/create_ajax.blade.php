<form action="{{ url('/level/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Tambah Data Level</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label>Nama Level</label>
            <input type="text" name="level_nama" id="level_nama" class="form-control" required>
            <small id="error-nama" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Kode Level</label>
            <input type="text" name="level_kode" id="level_kode" class="form-control" required>
            <small id="error-kode" class="error-text form-text text-danger"></small>
            @error('level_kode')
                <small class="form-text text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $("#form-tambah").validate({
            rules: {
                nama: { required: true, minlength: 3, maxlength: 100 },
                level_kode: { required: true, maxlength: 5 }
            },
            submitHandler: function (form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function (response) {
                        if (response.status) {
                            $('#modal-crud').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            dataLevel.ajax.reload();
                        } else {
                            $('.error-text').text('');
                            $.each(response.msgField, function (prefix, val) {
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
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>