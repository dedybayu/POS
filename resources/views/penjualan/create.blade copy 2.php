<form action="{{ url('/penjualan/') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Tambah Data penjualan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label>Nama Pembeli</label>
            <input type="text" name="nama_pembeli" id="nama_pembeli" class="form-control" required>
            <small id="error-pembeli" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Kode penjualan</label>
            <input type="text" name="penjualan_kode" id="penjualan_kode" class="form-control" required>
            <small id="error-kode" class="error-text form-text text-danger"></small>
        </div>

        {{-- Filter --}}
        <div class="row">
            {{-- Barang --}}
            <div class="col-md-5">
                <div class="form-group">
                    <label for="barang_id">Barang:</label>
                    <select class="form-control barang-select" id="barang_id" name="barang_id" style="width: 100%" required>
                        <option value="">- Semua -</option>
                        @foreach($barang as $item)
                            <option value="{{ $item->barang_id }}">({{ $item->barang_kode }}) {{ $item->barang_nama }}</option>
                        @endforeach
                    </select>
                    <small id="error-barang" class="error-text form-text text-danger"></small>
                </div>
            </div>
        
            {{-- Jumlah --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" name="jumlah" id="jumlah" class="form-control" required min="1">
                    <small id="error-jumlah" class="error-text form-text text-danger"></small>
                </div>
            </div>
        
            {{-- Tombol Plus --}}
            <div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-success btn-block h-100">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        
        
        
        
        



    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>



<script>
    $(document).ready(function () {
        // Inisialisasi Select2 pada barang_id
        $('.barang-select').select2({
            dropdownParent: $('#modal-crud'), // ini penting agar select2 muncul di dalam modal
            placeholder: "- Pilih Barang -",
            allowClear: true
        });

        // Validasi form tetap seperti sebelumnya
        $("#form-tambah").validate({
            rules: {
                nama_pembeli: { required: true, minlength: 3, maxlength: 100 },
                penjualan_kode: { required: true, maxlength: 5 },
                barang_id: { required: true },
                jumlah: { required: true, number: true, min: 1}
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
                            dataPenjualan.ajax.reload();
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
