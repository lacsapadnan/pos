<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data produk</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('produk.store') }}" method="post">
                    @csrf
                    <div id="otherSelectContainer" class="mb-4">
                        <label class="form-label" for="otherSelect">Kelompok</label>
                        <select id="otherSelect" name="category" class="form-select form-select-solid"
                            data-control="select2" data-dropdown-parent="#kt_modal_1">
                            <option disabled selected>Pilih Kelompok</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-10 row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Nama Produk</label>
                            <input name="name" type="text" class="form-control"
                                placeholder="Masukan nama produk" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="name">Promo</label>
                            <input name="promo" type="text" class="form-control" placeholder="Masukan promo" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Barcode DUS</label>
                                <input name="barcode_dus" type="number" class="form-control"
                                    placeholder="Masukan Barcode DUS" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Barcode Pak</label>
                                <input name="barcode_pak" type="number" class="form-control"
                                    placeholder="Masukan Barcode Pak" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Barcode Eceran</label>
                                <input name="barcode_eceran" type="number" class="form-control"
                                    placeholder="Masukan Barcode eceran" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Satuan Dus</label>
                                <select name="unit_dus" class="form-select" aria-label="Select example">
                                    <option readonly>Pilih satuan dus</option>
                                    @forelse($unit as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @empty
                                        <option readonly>Belum ada satuan</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Satuan Pak</label>
                                <select name="unit_pak" class="form-select" aria-label="Select example">
                                    <option readonly>Pilih satuan pak</option>
                                    @forelse($unit as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @empty
                                        <option readonly>Belum ada satuan</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Satuan Eceran</label>
                                <select name="unit_eceran" class="form-select" aria-label="Select example">
                                    <option readonly>Pilih satuan eceran</option>
                                    @forelse($unit as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @empty
                                        <option readonly>Belum ada satuan</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Jumlah DUS ke Eceran</label>
                                <input name="dus_to_eceran" type="number" class="form-control"
                                    placeholder="Masukan jumlah DUS ke eceran" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Jumlah Pak ke Eceran</label>
                                <input name="pak_to_eceran" type="number" class="form-control"
                                    placeholder="Masukan jumlah pak ke eceran" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-10">
                                <label class="form-label" for="name">Hadiah</label>
                                <input name="hadiah" type="text" class="form-control"
                                    placeholder="Masukan hadiah" />
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
