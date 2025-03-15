@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <a class="btn btn-sm btn-primary mt-1" href="{{ url('user/create') }}">Tambah</a>
                <button onclick="modalAction('{{ url('/user/create_ajax') }}')" class="btn btn-sm btn-success mt-1">
                    Tambah Ajax
                </button>
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
                            <select class="form-control" id="level_id" name="level_id" required>
                                <option value="">- Semua -</option>
                                @foreach($level as $item)
                                    <option value="{{ $item->level_id }}">{{ $item->level_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Level Pengguna</small>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-striped table-hover table-sm" id="table_user">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Level Pengguna</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal Container --}}
    <div id="modal-tambah" class="modal fade animate shake" tabindex="-1" role="dialog"
        data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')

    <script>
        function modalAction(url) {
            // Kosongkan modal sebelum memuat konten baru
            $("#modal-tambah .modal-content").html("");

            // Panggil modal melalui AJAX
            $.get(url, function(response) {
                $("#modal-tambah .modal-content").html(response);
                $("#modal-tambah").modal("show");
            });
        }

        // Bersihkan isi modal setelah ditutup
        $('#modal-tambah').on('hidden.bs.modal', function () {
            $("#modal-tambah .modal-content").html("");
        });

        var dataUser;
        $(document).ready(function () {
            dataUser = $('#table_user').DataTable({
                serverSide: true,
                ajax: {
                    url: "{{ url('user/list') }}",
                    dataType: "json",
                    type: "POST",
                    data: function (d) {
                        d.level_id = $('#level_id').val();
                    }
                },
                columns: [
                    { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                    { data: "username", orderable: true, searchable: true },
                    { data: "nama", orderable: true, searchable: true },
                    { data: "level.level_nama", orderable: false, searchable: false },
                    { data: "aksi", orderable: false, searchable: false }
                ]
            });

            $('#level_id').on('change', function () {
                dataUser.ajax.reload();
            });
        });
    </script>
@endpush
