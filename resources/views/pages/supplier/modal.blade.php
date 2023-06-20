<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data supplier</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('supplier.store') }}" method="post">
                    @csrf
                    <div class="mb-10">
                        <label class="form-label" for="name">Nama supplier</label>
                        <input name="name" type="text" class="form-control" placeholder="Masukan nama supplier" />
                    </div>
                    <div class="mb-10">
                        <label class="form-label" for="name">No. Telp supplier</label>
                        <input name="phone" type="number" class="form-control"
                            placeholder="Masukan no.telp cabang" />
                    </div>
                    <div class="mb-10">
                        <label class="form-label" for="name">Alamat supplier</label>
                        <textarea name="address" class="form-control" placeholder="Masukan alamat supplier"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>

            </div>
        </div>
    </div>
</div>
