<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data user</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('user.store') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Nama</label>
                        <input name="name" type="text" class="form-control" placeholder="Masukan nama user" value="{{ old('name') }}"/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input name="email" type="email" class="form-control" placeholder="Masukan email user" value="{{ old('email') }}"/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input name="password" type="password" class="form-control" placeholder="Masukan password user" />
                    </div>
                    <div class="mb-5">
                        <label for="inputEmail3" class="col-form-label">Role</label>
                        <select name="role" class="form-select" aria-label="Select example">
                            @forelse ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @empty
                                <option value="">Tidak ada role</option>
                            @endforelse
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
