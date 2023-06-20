<div class="modal fade" tabindex="-1" id="kt_modal_2">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Import data customer</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <!--begin::Form-->
                <form class="form" action="{{ route('customer.import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-10">
                        <label class="form-label" for="name">File</label>
                        <input name="file" type="file" class="form-control"
                            placeholder="Upload file csv atau xlsx" />
                        <span>Download template xlsx, Klik <a href="{{ route('customer.template.download') }}">disini</a></span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Import</button>
                </form>
            </div>
        </div>
    </div>
</div>
