@if (session()->has('success'))
    <!--begin::Alert-->
    <div class="p-5 mb-10 alert alert-primary d-flex align-items-center">
        <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4"><span class="path1"></span><span
                class="path2"></span></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-primary">Sukses</h4>
            <span>{{ session()->get('success') }}</span>
        </div>
        <button type="button"
            class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
            data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-2x text-primary"><span class="path1"></span><span
                    class="path2"></span></i>
        </button>
    </div>
@endif
@if ($errors->any())
    <!--begin::Alert-->
    <div class="p-5 mb-10 alert alert-danger d-flex align-items-center">
        <i class="ki-duotone ki-close-circle fs-2hx text-danger me-4"><span class="path1"></span><span
                class="path2"></span></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-danger">Gagal</h4>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span><br>
            @endforeach
        </div>
        <button type="button"
            class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
            data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-2x text-danger"><span class="path1"></span><span
                    class="path2"></span></i>
        </button>
    </div>
@endif
