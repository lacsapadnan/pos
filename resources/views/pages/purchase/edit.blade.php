@extends('layouts.dashboard')

@section('title', 'Edit Pembelian')
@section('menu-title', 'Edit Pembelian')

@section('content')
@include('components.alert')
    <div class="mt-5 border-0 card card-p-5 card-flush">
        <div class="card-body">
            <form action="{{ route('pembelian.update', $purchases->id) }}" method="POST">
                @method('PUT')
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <div class="mb-3 row align-items-center">
                            <label for="inputEmail3" class="col-form-label">No. Faktur Supplier</label>
                            <input id="invoice" type="text" name="invoice" class="form-control"
                                placeholder="Masukan nomor faktur" value="{{ $purchases->invoice }}" />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3 row align-items-center">
                            <label for="inputEmail3" class="col-form-label">Supplier</label>
                            <select id="supplier_id" class="form-select" name="supplier_id" data-control="select2"
                                data-placeholder="Pilih Supplier" data-allow-clear="true">
                                <option></option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ $supplier->id == $purchases->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="inputEmail3" class="col-form-label">PPN</label>
                        <input type="text" name="tax" class="form-control" value="{{ $purchases->tax }}" />
                    </div>
                </div>
                <div class="row">
                    <h2 class="mt-10">List Produk</h2>
                </div>
                @foreach ($purchases->details as $key => $purchase_detail)
                    <div class="row">
                        <div class="col-md-2">
                            <div class="mb-3 row">
                                <label for="inputEmail3" class="col-form-label">Produk</label>
                                <select class="form-select" name="product_id[]" data-control="select2"
                                    data-placeholder="Pilih Produk" data-allow-clear="true">
                                    <option></option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ $product->id == $purchase_detail->product_id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3 row">
                                <label for="inputEmail3" class="col-form-label">Unit</label>
                                <select class="form-select" name="unit_id[]" data-control="select2"
                                    data-placeholder="Pilih Unit" data-allow-clear="true">
                                    <option></option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $unit->id == $purchase_detail->unit_id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="mb-3 row">
                                <label for="inputEmail3" class="col-form-label">Jumlah</label>
                                <input type="text" name="qty[]" class="form-control" placeholder="Masukan jumlah"
                                    value="{{ $purchase_detail->quantity }}" />
                            </div>
                        </div>
                        <div class="col-md-2 ms-4">
                            <div class="mb-3 row">
                                <label for="inputEmail3" class="col-form-label">Diskon Fix</label>
                                <input type="text" name="discount_fix[]" class="form-control"
                                    placeholder="Masukan diskon fix" value="{{ $purchase_detail->discount_fix }}" />
                            </div>
                        </div>
                        <div class="col-md-2 ms-4">
                            <div class="mb-3 row">
                                <label for="inputEmail3" class="col-form-label">Diskon Persen</label>
                                <input type="text" name="discount_percent[]" class="form-control"
                                    placeholder="Masukan diskon persen" value="{{ $purchase_detail->discount_percent }}" />
                            </div>
                        </div>
                        <div class="col-md-2 ms-4">
                            <div class="mb-3 row">
                                <label for="inputEmail3" class="col-form-label">Harga</label>
                                <input id="price_unit" type="text" name="price_unit[]" class="form-control"
                                    placeholder="Masukan harga" value="{{ $purchase_detail->price_unit }}" />
                            </div>
                        </div>
                    </div>
                @endforeach
                <button type="submit" class="btn btn-primary">Update Pembelian</button>
            </form>
        </div>
    </div>
@endsection
@push('addon-script')
    <script type="text/javascript">
        var tanpa_rupiah = document.getElementById('price_unit');
        tanpa_rupiah.addEventListener('keyup', function(e) {
            tanpa_rupiah.value = formatRupiah(this.value);
        });

        function formatRupiah(angka, prefix) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }
    </script>
@endpush
