<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data inventori</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('inventori.store') }}" method="post">
                    @csrf
                    <div class="mb-10">
                        <label class="form-label" for="select_form1">Produk</label>
                        <select name="product_id" class="form-select" data-control="select2" data-placeholder="Pilih Produk" id="select_form1" data-dropdown-parent="#kt_modal_1">
                            <option disabled></option>
                            @foreach($product as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-10">
                        <label class="form-label" for="select_form2">Cabang</label>
                        <select name="warehouse_id" class="form-select" data-control="select2" data-placeholder="Pilih cabang" id="select_form2" data-dropdown-parent="#kt_modal_1">
                            <option disabled></option>
                            @foreach($warehouse as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-10">
                        <label class="form-label" for="quantity">Quantity</label>
                        <input name="quantity" type="number" class="form-control" placeholder="Masukan quantity produk" id="quantity" />
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
