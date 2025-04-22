<form action="{{ url('/stok/' . $stok->stok_id) }}" method="POST" id="form-tambah">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit Data stok</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div id="form-container">
            <div class="row item-row">
                <!-- Barang -->
                <div class="col-md-7">
                    <div class="form-group">
                        <label>Barang:</label>
                        <select class="form-control barang-select" id="barang-select" name="barang_id"
                            style="width: 100%" required>
                            <option value="" disabled selected>- Pilih -</option>
                            @foreach($barang as $item)
                                <option value="{{ $item->barang_id }}" data-harga="{{ $item->harga_beli }}"
                                    @if($item->barang_id == $stok->barang_id) selected @endif>
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
                        <input type="number" name="stok_jumlah" class="form-control jumlah-input" value="{{$stok->stok_jumlah}}" required min="1">
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

            <div class="form-group">
                <label>Supplier:</label>
                <select class="form-control select2 supplier_select" name="supplier_id" style="width: 100%" required>
                    <option value="" disabled selected>- Pilih -</option>
                    @foreach($supplier as $item)
                        <option value="{{ $item->supplier_id }}"
                            @if($item->supplier_id == $stok->supplier_id) selected @endif>
                            ({{ $item->supplier_kode }}) {{ $item->supplier_nama }}
                        </option>
                    @endforeach
                </select>
                <small class="error-text form-text text-danger"></small>
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
        // Inisialisasi Select2 pada select barang
        $('.barang-select, .supplier_select').select2({
            dropdownParent: $('#modal-crud'),
            placeholder: "- Pilih -",
            allowClear: false
        });

        // Format ke rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }

        $('#form-container').on('change keyup', '.barang-select, .jumlah-input', function () {
            $('.item-row').each(function () {
                const selectBarang = $(this).find('.barang-select');
                const barangData = selectBarang.select2('data')[0]; // data dari select2
                const harga = parseFloat(barangData?.element?.dataset?.harga) || 0; // pastikan harga adalah angka

                const jumlah = parseInt($(this).find('.jumlah-input').val()) || 0;
                const total = harga * jumlah;

                $(this).find('.harga-total').val(formatRupiah(total));
            });
        });


        $("#form-tambah").validate({
            rules: {
                stok_nama: { required: true, minlength: 3, maxlength: 100 },
                stok_kode: { required: true, maxlength: 5 }
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
                            dataStok.ajax.reload();
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