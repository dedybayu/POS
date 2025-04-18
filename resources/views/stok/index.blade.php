@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/stok/import') }}')" class="btn btn-sm btn-info mt-1">Import
                    stok</button>
                <a class="btn btn-sm btn-primary mt-1" href="{{ url('stok/create') }}">Tambah</a>
                <button onclick="modalAction('{{ url('/stok/create_ajax') }}')" class="btn btn-sm btn-success mt-1">
                    Tambah Ajax
                </button>
                <a href="{{ url('/stok/export_excel') }}" class="btn btn-sm btn-primary mt-1"><i
                        class="fa fa-file-excel"></i> Export stok</a>
                <a href="{{ url('/stok/export_pdf') }}" class="btn btn-sm btn-warning mt-1"><i class="fa fa-file-pdf"></i>
                    Export stok</a>
            </div>
        </div>


        

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Filter --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label">Filter:</label>

                        <div class="col-3">
                            <select class="form-select" id="supplier_id" name="supplier_id" style="width: 100%">
                                <option value="">- Semua -</option>
                                @foreach($supplier as $item)
                                    <option value="{{ $item->supplier_id }}">{{ $item->supplier_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Supplier</small>
                        </div>

                        <div class="col-3">
                            <select class="form-select" id="kategori_id" name="kategori_id" style="width: 100%">
                                <option value="">- Semua -</option>
                                @foreach($kategori as $item)
                                    <option value="{{ $item->kategori_id }}">{{ $item->kategori_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Kategori</small>
                        </div>

                        <div class="col-3">
                            <select class="form-select" id="user_id" name="user_id" style="width: 100%">
                                <option value="">- Semua -</option>
                                @foreach($user as $item)
                                    <option value="{{ $item->user_id }}">{{ $item->nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">User Terkait</small>
                        </div>
                    </div>
                </div>


            </div>
            {{-- Filter --}}
            <div class="row">

            </div>

            <table class="table table-bordered table-striped table-hover table-sm" id="table_stok">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Kode</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Supplier</th>
                        <th>User</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal Container --}}
    <div id="modal-crud" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static"
        data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS for Select2 placeholder centering -->
    <style>
        /* Styling untuk placeholder Select2 */
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            /* Sesuaikan dengan tinggi form input Bootstrap 4 */
            line-height: calc(2.25rem);
            /* Menjaga teks tetap di tengah */
        }

        .select2-selection__rendered {
            line-height: calc(2.25rem);
            /* Menjaga placeholder tetap di tengah */
            padding-left: 10px;
            /* Sesuaikan padding sesuai kebutuhan */
        }

        /* Optional: menyesuaikan ukuran input untuk padding lebih baik */
        .select2-container .select2-selection--single {
            padding-right: 20px;
            /* Memberikan ruang untuk ikon clear (X) */
        }

        .select2-selection__clear {
            padding: 0 10px;
            /* Menyesuaikan ukuran clear button */
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        function modalAction(url) {
            // Kosongkan modal sebelum memuat konten baru
            $("#modal-crud .modal-content").html("");

            // Panggil modal melalui AJAX
            $.get(url, function (response) {
                $("#modal-crud .modal-content").html(response);
                $("#modal-crud").modal("show");
            });
        }

        // Bersihkan isi modal setelah ditutup
        $('#modal-crud').on('hidden.bs.modal', function () {
            $("#modal-crud .modal-content").html("");
        });

        var dataStok;
        $(document).ready(function () {
            $('#supplier_id, #kategori_id, #user_id').select2({
                placeholder: "- Semua -", // Placeholder text
                allowClear: true // Enable clear button
            });

            dataStok = $('#table_stok').DataTable({
                serverSide: true,
                ajax: {
                    url: "{{ url('stok/list') }}",
                    dataType: "json",
                    type: "POST",
                    data: function (d) {
                        d.supplier_id = $('#supplier_id').val();
                        d.kategori_id = $('#kategori_id').val();
                        d.user_id = $('#user_id').val();
                    }
                },

                columns: [
                    { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                    { data: "barang.barang_nama", orderable: true, searchable: true },
                    { data: "barang.barang_kode", orderable: true, searchable: true }, // kalau ada field kode
                    { data: "barang.kategori.kategori_nama", orderable: true, searchable: true }, // kalau ada field kode
                    { data: "stok_jumlah", orderable: true, searchable: true },
                    { data: "supplier.supplier_nama", orderable: true, searchable: true },
                    { data: "user.nama", orderable: true, searchable: false }, // pastikan field ini benar
                    { data: "stok_tanggal", orderable: true, searchable: true }, // pastikan field ini benar
                    { data: "aksi", orderable: false, searchable: false }
                ]

            });

            $('#supplier_id, #kategori_id, #user_id').on('change', function () {
                dataStok.ajax.reload();
            });

        });
    </script>
@endpush