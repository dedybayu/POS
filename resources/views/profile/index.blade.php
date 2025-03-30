@empty($user)
    <div id="modal-profile" class="modal-dialog modal-lg" role="document">
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
                <a href="{{ url('/user') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">Data User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">

        <div class="text-center">
            <img src="{{ asset(auth()->user()->profile_picture ? 'storage/' . auth()->user()->profile_picture : 'img/user.png') }}"
                class="rounded-circle border border-2 border-primary shadow bg-white p-1"
                style="width: 160px; height: 160px; object-fit: cover;" alt="Bordered avatar">
        </div>


        <div class="mb-3">
            <label for="name" class="form-label">Your Name</label>
            <input type="text" value="{{ auth()->user()->nama }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Level Pengguna</label>
            <input type="text" value="{{ auth()->user()->level->level_nama }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Username</label>
            <input type="text" value="{{ auth()->user()->username }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Password</label>
            <input type="text" value="*************" class="form-control" required>
        </div>
    </div>
    <div class="modal-footer">
        <button onclick="modalAction('{{ url('/profile/edit') }}')"
            class="btn btn-success btn-sm">Edit
        </button>
        <button type="button" data-dismiss="modal" class="btn btn-primary btn-sm">Close</button>
    </div>
@endempty