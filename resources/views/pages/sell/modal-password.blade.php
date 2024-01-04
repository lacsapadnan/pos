<div class="modal fade" tabindex="-1" id="passwordModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Customer masih memiliki hutang</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <div class="mb-3" >
                    <label for="bayar" class="form-label">User Master</label>
                    <select class="form-select" id="user_master" name="user_master">
                        <option value="">Pilih User Master</option>
                        @foreach ($masters as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" >
                    <label for="bayar" class="form-label">User Master</label>
                     <input type="password" class="form-control" id="masterUserPassword"
                    placeholder="Masukan password user master">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="checkMasterUserPassword()" id="submitPasswordBtn">Submit</button>
            </div>
        </div>
    </div>
</div>
