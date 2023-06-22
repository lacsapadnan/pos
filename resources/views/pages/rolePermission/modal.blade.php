<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah role & permission</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('role-permission.store') }}" method="post">
                    @csrf
                    <div class="mb-10">
                        <label class="form-label" for="name">Nama Role</label>
                        <input name="name" type="text" class="form-control" placeholder="Masukan nama role" />
                    </div>
                    <div class="mb-10">
                        <label class="form-label">Hak Akses</label>
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gap-3">
                            @foreach ($permissions as $permission)
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}">
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
