<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data karyawan</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('karyawan.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Nama</label>
                        <input name="name" type="text" class="form-control" placeholder="Masukan nama karyawan"
                            value="{{ old('name') }}" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="nickname">Nickname</label>
                        <input name="nickname" type="text" class="form-control" placeholder="Masukan nickname karyawan"
                            value="{{ old('nickname') }}" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="ktp">Foto KTP</label>
                        <input name="ktp" type="file" class="form-control" accept="image/*" />
                        <div class="form-text">Upload gambar KTP (JPG, PNG, GIF, max 2MB)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">phone</label>
                        <input name="phone" type="number" class="form-control" placeholder="Masukan phone karyawan" />
                    </div>
                    <div class="mb-3">
                        <label for="warehouse" class="col-form-label">Cabang</label>
                        <select name="warehouse_id" class="form-select" aria-label="Select example">
                            @forelse ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @empty
                            <option value="">Tidak ada cabang</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Karyawan</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isActive" value="1" checked
                                id="activeSwitch">
                            <label class="form-check-label" for="activeSwitch">
                                Aktif
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
