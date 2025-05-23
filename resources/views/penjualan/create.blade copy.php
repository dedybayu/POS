<form action="{{ url('/penjualan/') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Tambah Data Penjualan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Scrollable content -->
    <div class="modal-body overflow-auto" style="max-height: 70vh;">
        <div class="form-group">
            <label>Nama Pembeli</label>
            <input type="text" name="nama_pembeli" id="nama_pembeli" class="form-control" required>
            <small id="error-pembeli" class="error-text form-text text-danger"></small>
        </div>
        <div class="form-group">
            <label>Kode Penjualan</label>
            <input type="text" name="penjualan_kode" id="penjualan_kode" class="form-control" required>
            <small id="error-kode" class="error-text form-text text-danger"></small>
        </div>

        <!-- Container untuk item penjualan -->
        <div id="form-container">
            <div class="row item-row">
                <!-- Barang -->
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Barang:</label>
                        <select class="form-control barang-select" name="barang_id[]" required>
                            <option value="" disabled>- Pilih -</option>
                            @foreach($barang as $item)
                                <option value="{{ $item->barang_id }}" data-harga="{{ $item->harga_jual }}">
                                    ({{ $item->barang_kode }}) {{ $item->barang_nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="error-text form-text text-danger"></small>
                    </div>
                </div>

                <!-- Jumlah -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Jumlah:</label>
                        <input type="number" name="jumlah[]" class="form-control jumlah-input" required min="1">
                        <small class="error-text form-text text-danger"></small>
                    </div>
                </div>

                <!-- Harga -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Harga Total:</label>
                        <input type="text" class="form-control harga-total" readonly value="Rp0">
                    </div>
                </div>
            </div>
        </div>
        <!-- Tombol Tambah -->
        <div class="col-md-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" id="button-tambah" class="btn btn-success btn-block h-100">
                    <i class="bi bi-plus"></i> Tambah
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="modal-footer"
        style="position: sticky; bottom: 0; background-color: #fff; z-index: 999; border-top: 1px solid #dee2e6;">
        <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<!-- JS Script -->
<script>
    $(document).ready(function () {
        // Inisialisasi Select2 pada select barang
        $('.barang-select').select2({
            placeholder: "- Pilih -",
            allowClear: true
        });

        // Format ke rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }

        // Tambah baris baru
        $(document).on('click', '#button-tambah', function () {
            const newRow = ` 
                <div class="row item-row mt-2">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Barang:</label>
                            <select class="form-control barang-select" name="barang_id[]" required>
                                <option value="">- Pilih -</option>
                                @foreach($barang as $item)
                                    <option value="{{ $item->barang_id }}" data-harga="{{ $item->harga_jual }}">
                                        ({{ $item->barang_kode }}) {{ $item->barang_nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jumlah:</label>
                            <input type="number" name="jumlah[]" class="form-control jumlah-input" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Harga Total:</label>
                            <input type="text" class="form-control harga-total" readonly value="Rp0">
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-remove btn-block h-100">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            $('#form-container').append(newRow);
            // Inisialisasi ulang select2 untuk elemen baru
            $('.barang-select').select2({
                placeholder: "- Pilih -",
                allowClear: true
            });
        });

        // Hapus baris
        $('#form-container').on('click', '.btn-remove', function () {
            $(this).closest('.item-row').remove();
        });

        // Hitung total saat barang/jumlah berubah
        $('#form-container').on('change keyup', '.barang-select, .jumlah-input', function () {
            const row = $(this).closest('.item-row');
            const selected = row.find('.barang-select option:selected');
            const harga = parseInt(selected.data('harga')) || 0;
            const jumlah = parseInt(row.find('.jumlah-input').val()) || 0;
            const total = harga * jumlah;
            row.find('.harga-total').val(formatRupiah(total));
        });

        // Validasi dan AJAX submit
        $("#form-tambah").validate({
            rules: {
                nama_pembeli: { required: true, minlength: 3, maxlength: 100 },
                penjualan_kode: { required: true, maxlength: 5 },
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
