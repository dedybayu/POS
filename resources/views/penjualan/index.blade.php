@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/penjualan/create') }}')" class="btn btn-sm btn-success mt-1">
                    Tambah Ajax
                </button>
                <button onclick="modalAction('{{ url('/penjualan/import') }}')" class="btn btn-sm btn-info mt-1">Import
                    penjualan</button>
                <a href="{{ url('/penjualan/export_excel') }}" class="btn btn-sm btn-primary mt-1"><i
                        class="fa fa-file-excel"></i> Export penjualan</a>
                <a href="{{ url('/penjualan/export_pdf') }}" class="btn btn-sm btn-warning mt-1"><i
                        class="fa fa-file-pdf"></i>
                    Export penjualan</a>
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
                        <label class="col-12 col-md-1 control-label col-form-label">Filter:</label>

                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                            <select class="form-select" id="user_id" name="user_id" style="width: 100%">
                                <option value="">- Semua -</option>
                                @foreach($user as $item)
                                    <option value="{{ $item->user_id }}">{{ $item->nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">user</small>
                        </div>
                    </div>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm" id="table_penjualan">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Pembeli</th>
                            <th>Total Harga</th>
                            <th>Tanggal</th>
                            <th>Diproses Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Container --}}
    <div id="modal-crud" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static"
        data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
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
        // let newRow = '';

        function modalAction(url) {
            // newRow = ''; // Kosongkan dulu sebelum muat konten baru

            // Kosongkan isi modal sebelum load konten baru
            $("#modal-crud .modal-content").html("");

            // Panggil modal via AJAX
            $.get(url, function (response) {
                $("#modal-crud .modal-content").html(response);
                $("#modal-crud").modal("show");
            });
        }

        // Bersihkan isi modal saat ditutup
        $('#modal-crud').on('hidden.bs.modal', function () {
            $("#modal-crud .modal-content").html("");
            // newRow = ''; // Reset lagi saat modal ditutup (opsional tambahan keamanan)
        });


        var dataPenjualan;
        $(document).ready(function () {
            $('#user_id').select2({
                placeholder: "- Semua -", // Placeholder text
                allowClear: true // Enable clear button
            });

            dataPenjualan = $('#table_penjualan').DataTable({
                serverSide: true,
                ajax: {
                    url: "{{ url('penjualan/list') }}",
                    dataType: "json",
                    type: "POST",
                    data: function (d) {
                        d.user_id = $('#user_id').val();
                    }
                },

                columns: [
                    { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                    { data: "penjualan_kode", orderable: true, searchable: true },
                    { data: "pembeli", orderable: true, searchable: true },
                    { data: "total_harga", orderable: true, searchable: true },
                    { data: "penjualan_tanggal", orderable: true, searchable: true },
                    { data: "user", orderable: true, searchable: true },
                    { data: "aksi", orderable: false, searchable: false }
                ]

            });

            $('#user_id').on('change', function () {
                dataPenjualan.ajax.reload();
            });

        });
    </script>
@endpush