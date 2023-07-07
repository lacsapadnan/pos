@extends('layouts.dashboard')

@section('title', 'Pindah Stok')
@section('menu-title', 'Pindah Stok')

@section('content')
    {{-- session success --}}
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
        <div class="p-5 mb-10 alert alert-dismissible bg-danger d-flex flex-column flex-sm-row">
            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                <h4 class="mb-2 text-light">Gagal Menyimpan data</h4>
                <span>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            </div>
            <button type="button"
                class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-light"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="card-body">
            <form action="{{ route('pindah-stok.store') }}" method="post">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Cabang Awal</label>
                        <select name="from_warehouse" class="form-select" data-control="select2"
                            data-placeholder="Pilih cabang awal">
                            <option></option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }} {{ old('from_warehouse') == $warehouse->id ? 'selected' : '' }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cabang Tujuan</label>
                        <select name="to_warehouse" class="form-select" data-control="select2"
                            data-placeholder="Pilih cabang tujuan">
                            <option></option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }} {{ old('from_warehouse') == $warehouse->id ? 'selected' : '' }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="kt_docs_repeater_basic">
                    <!--begin::Form group-->
                    <div class="form-group">
                        <div data-repeater-list="product_list">
                            <div data-repeater-item>
                                <div class="form-group row mb-5">
                                    <div class="col-md-3">
                                        <label class="form-label">Produk:</label>
                                        <select name="product_id" class="form-select" data-control="select2"
                                            data-placeholder="Pilih produk">
                                            <option></option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }} {{ old('product_id') == $product->id ? 'selected' : '' }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Unit:</label>
                                        <select name="unit" class="form-select" data-control="select2"
                                            data-placeholder="Pilih unit produk">
                                            <option></option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }} {{ old('unit_id') == $unit->id ? 'selected' : '' }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Quantity:</label>
                                        <input name="quantity" type="text" class="form-control mb-2 mb-md-0"
                                            placeholder="Masukan jumlah produk" />
                                    </div>
                                    <div class="col-md-3">
                                        <a href="javascript:;" data-repeater-delete
                                            class="btn btn-sm btn-light-danger mt-3 mt-md-8">
                                            <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span
                                                    class="path2"></span><span class="path3"></span><span
                                                    class="path4"></span><span class="path5"></span></i>
                                            Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Form group-->

                    <!--begin::Form group-->
                    <div class="form-group mt-5">
                        <a href="javascript:;" data-repeater-create class="btn btn-light-primary">
                            <i class="ki-duotone ki-plus fs-3"></i>
                            Tambah Produk
                        </a>
                    </div>
                    <!--end::Form group-->
                </div>
                <button type="submit" class="btn btn-primary mt-5">Simpan</button>
            </form>

        </div>
    </div>
    </div>
@endsection

@push('addon-script')
    <script src="{{ URL::asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <script>
        $('#kt_docs_repeater_basic').repeater({
            initEmpty: false,

            show: function() {
                $(this).slideDown();
                $(this).find('select[data-control="select2"]').select2();
            },

            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            },

            ready: function() {
                $('[data-kt-repeater="select2"]').select2();
            },
        });
    </script>
@endpush
