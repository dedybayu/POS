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
        {{-- <form action="{{ route('profile.update', auth()->user()->id) }}" method="POST" enctype="multipart/form-data" class="p-3">
            @method('PUT')
            @csrf --}}
            <input type="hidden" id="remove_picture" name="remove_picture" value="0">
        
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-md-8 bg-light p-4 rounded shadow">
                        <div class="text-center">
                            <img id="profileImage" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px;"
                                src="{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : asset('img/user.png') }}"
                                alt="Profile picture">
        
                            <div class="mt-2">
                                <input type="file" id="profile_picture" name="profile_picture" class="d-none" accept="image/*" onchange="previewImage(event)">
                                <button type="button" onclick="document.getElementById('profile_picture').click()" class="btn btn-primary">
                                    Change Picture
                                </button>
                                <button type="button" onclick="removeImage()" class="btn btn-outline-danger">
                                    Delete Picture
                                </button>
                            </div>
                        </div>
        
                        <div class="mt-4">
                            {{-- <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" class="form-control" required>
                            </div>
        
                            <div class="mb-3">
                                <label for="occupancy" class="form-label">Your Occupancy</label>
                                <input type="text" id="occupancy" name="occupancy" value="{{ auth()->user()->occupancy }}" class="form-control" required>
                            </div>
        
                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email</label>
                                <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" class="form-control" required>
                            </div>
        
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" rows="4" class="form-control" placeholder="Write your bio here...">{{ auth()->user()->bio }}</textarea>
                            </div> --}}
        
                            <div class="d-flex justify-content-between">
                                <a href="/profile" class="btn btn-danger">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {{-- </form> --}}
        
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
        </script>
        
    </div>
    <div class="modal-footer">
        <button onclick="modalAction('{{ url('/user/' . $user->user_id . '/edit_ajax') }}')" 
            class="btn btn-success btn-sm">Edit
        </button>
        <button type="button" data-dismiss="modal" class="btn btn-primary btn-sm">Close</button>
    </div>
@endempty