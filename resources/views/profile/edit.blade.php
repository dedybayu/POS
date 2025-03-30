@empty($user)
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
                <a href="{{ url('/') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
    <form action="{{ url('/profile') }}" method="POST" enctype="multipart/form-data" id="form-edit">
        @csrf
        @method('PUT')
        <div class="modal-header">
            <h5 class="modal-title">Edit Profile</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="justify-content-center">
                <div class="text-center">
                    <img id="profileImage" class="img-thumbnail rounded-circle mb-3" style="width: 160px; height: 160px; object-fit: cover;"
                        src="{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : asset('img/user.png') }}"
                        alt="Profile picture">

                    <div class="mt-2">
                        <input type="file" id="profile_picture" name="profile_picture" class="d-none" accept="image/*"
                            onchange="previewImage(event)">
                        <button type="button" onclick="document.getElementById('profile_picture').click()"
                            class="btn btn-primary">
                            Change Picture
                        </button>
                        <button type="button" onclick="removeImage()" class="btn btn-outline-danger">
                            Delete Picture
                        </button>
                    </div>
                </div>
                <input type="hidden" id="remove_picture" name="remove_picture" value="0">

                {{-- <div class="form-group">
                    <label>Level Pengguna</label>
                    <select name="level_id" id="level_id" class="form-control" required>
                        <option value="">- Pilih Level -</option>
                        @foreach($level as $l)
                        <option {{ ($l->level_id == $user->level_id) ? 'selected' : '' }} value="{{ $l->level_id }}">
                            {{ $l->level_nama }}
                        </option>
                        @endforeach
                    </select>
                    <small id="error-level_id" class="error-text form-text text-danger"></small>
                </div> --}}

                <div class="form-group">
                    <label>Username</label>
                    <input value="{{ $user->username }}" type="text" name="username" id="username" class="form-control"
                        required>
                    <small id="error-username" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label>Nama</label>
                    <input value="{{ $user->nama }}" type="text" name="nama" id="nama" class="form-control" required>
                    <small id="error-nama" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input value="" type="password" name="password" id="password" class="form-control">
                    <small class="form-text text-muted">Abaikan jika tidak ingin ubah password</small>
                    <small id="error-password" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
    </form>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profileImage');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
            document.getElementById('remove_picture').value = "0";
        }

        function removeImage() {
            document.getElementById('profileImage').src = '/../img/user.png';
            document.getElementById('profile_picture').value = '';
            document.getElementById('remove_picture').value = "1";
        }

        $(document).ready(function () {
            $("#form-edit").validate({
                rules: {
                    username: { required: true, minlength: 3, maxlength: 20 },
                    nama: { required: true, minlength: 3, maxlength: 100 },
                    password: { minlength: 6, maxlength: 20 }
                },
                submitHandler: function (form) {
                    var formData = new FormData(form); // Gunakan FormData untuk menangani file
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: formData,
                        processData: false, // Penting: Agar jQuery tidak memproses data
                        contentType: false, // Penting: Agar tidak diubah menjadi application/x-www-form-urlencoded
                        success: function (response) {
                            if (response.status) {
                                $('#modal-crud').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                }).then(() => {
                                    location.reload(); // Reload halaman setelah OK ditekan
                                });
                            }
                            else {
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