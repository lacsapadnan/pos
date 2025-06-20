@extends('layouts.dashboard')

@section('title', 'Edit Produk')
@section('menu-title', 'Edit Produk')

@section('content')
<div class="mt-5 card">
    <x-alert />
    <div class="card-body">
        <form action="{{ route('produk.update', $product->id) }}" method="post">
            @csrf
            @method('PUT')
            <div id="otherSelectContainer" class="mb-4">
                <label class="form-label" for="otherSelect">Kelompok</label>
                <select id="otherSelect" name="category" class="form-select form-select-solid" data-control="select2">
                    <option selected>{{ $product->group }}</option>
                    @foreach ($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-10 row">
                <div class="col-md-4">
                    <label class="form-label" for="name">Nama Produk</label>
                    <input name="name" type="text" class="form-control" placeholder="Masukan nama produk"
                        value="{{ $product->name }}" />
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="promo">Promo</label>
                    <input name="promo" type="text" class="form-control" placeholder="Masukan promo produk"
                        value="{{ $product->promo }}" />
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="isShow" value="1" id="isShowToggle" {{
                            $product->isShow ? 'checked' : '' }} />
                        <label class="form-check-label" for="isShowToggle">
                            Tampilkan Produk
                        </label>
                    </div>
                    <div class="form-text">Aktifkan untuk menampilkan produk di form penjualan dan pembelian</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="barcode_dus">Barcode DUS</label>
                        <input name="barcode_dus" type="number" class="form-control" placeholder="Masukan Barcode DUS"
                            value="{{ $product->barcode_dus }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="barcode_pak">Barcode Pak</label>
                        <input name="barcode_pak" type="number" class="form-control" placeholder="Masukan Barcode PAK"
                            value="{{ $product->barcode_pak }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="barcode_eceran">Barcode Eceran</label>
                        <input name="barcode_eceran" type="number" class="form-control"
                            placeholder="Masukan Barcode eceran" value="{{ $product->barcode_eceran }}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="price_dus">Harga DUS</label>
                        <input name="price_dus" type="number" class="form-control" placeholder="Masukan Harga DUS"
                            value="{{ $product->price_dus }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="price_pak">Harga Pak</label>
                        <input name="price_pak" type="number" class="form-control" placeholder="Masukan harga Pak"
                            value="{{ $product->price_pak }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="price_eceran">Harga Eceran</label>
                        <input name="price_eceran" type="number" class="form-control" placeholder="Masukan harga eceran"
                            value="{{ $product->price_eceran }}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="unit_dus">Satuan Dus</label>
                        <select name="unit_dus" class="form-select" aria-label="Select example">
                            <option readonly>Pilih satuan dus</option>
                            @forelse($unit as $item)
                            <option value="{{ $item->id }}" {{ $item->id == $product->unit_dus ? 'selected' : '' }}>{{
                                $item->name }}
                            </option>
                            @empty
                            <option readonly>Belum ada satuan</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="unit_pak">Satuan Pak</label>
                        <select name="unit_pak" class="form-select" aria-label="Select example">
                            <option readonly>Pilih satuan pak</option>
                            @forelse($unit as $item)
                            <option value="{{ $item->id }}" {{ $item->id == $product->unit_pak ? 'selected' : '' }}>{{
                                $item->name }}
                            </option>
                            @empty
                            <option readonly>Belum ada satuan</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="unit_eceran">Satuan Eceran</label>
                        <select name="unit_eceran" class="form-select" aria-label="Select example">
                            <option readonly>Pilih satuan eceran</option>
                            @forelse($unit as $item)
                            <option value="{{ $item->id }}" {{ $item->id == $product->unit_eceran ? 'selected' : ''
                                }}>{{ $item->name }}
                            </option>
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
                        <label class="form-label" for="dus_to_eceran">Jumlah DUS ke Eceran</label>
                        <input name="dus_to_eceran" type="number" class="form-control"
                            placeholder="Masukan jumlah DUS ke eceran" value="{{ $product->dus_to_eceran }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="pak_to_eceran">Jumlah Pak ke Eceran</label>
                        <input name="pak_to_eceran" type="number" class="form-control"
                            placeholder="Masukan jumlah pak ke eceran" value="{{ $product->pak_to_eceran }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-10">
                        <label class="form-label" for="hadiah">Hadiah</label>
                        <input name="hadiah" type="text" class="form-control" placeholder="Masukan hadiah"
                            value="{{ $product->hadiah }}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="lastest_price_eceran">Harga Eceran Terakhir</label>
                        <input name="lastest_price_eceran" type="number" class="form-control"
                            placeholder="Masukan harga eceran terakhir" value="{{ $product->lastest_price_eceran }}" />
                        <span class="mt-2">Harga eceran terakhir: {{ $product->lastest_price_eceran }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="price_sell_dus">Harga Jual Dus</label>
                        <input name="price_sell_dus" type="number" class="form-control"
                            placeholder="Masukan harga jual dus" value="{{ $product->price_sell_dus }}" />
                        <span>Harga jual dus: {{ $product->price_sell_dus }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="price_sell_pak">Harga Jual Pak</label>
                        <input name="price_sell_pak" type="number" class="form-control"
                            placeholder="Masukan harga jual pak" value="{{ $product->price_sell_pak }}" />
                        <span>Harga Jual Pak: {{ $product->price_sell_pak }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="price_sell_eceran">Harga Jual Eceran</label>
                        <input name="price_sell_eceran" type="number" class="form-control"
                            placeholder="Masukan harga jual eceran" value="{{ $product->price_sell_eceran }}" />
                        <span>Harga Jual Eceran: {{ $product->price_sell_eceran }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="lastest_price_eceran_out_of_town">Harga Eceran Terakhir Luar
                            Kota</label>
                        <input name="lastest_price_eceran_out_of_town" type="number" class="form-control"
                            placeholder="Masukan harga eceran terakhir luar kota"
                            value="{{ $product->lastest_price_eceran_out_of_town ?? 0 }}" />
                        <span>Harga eceran terakhir luar kota: {{ $product->lastest_price_eceran_out_of_town ?? 0
                            }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="price_sell_dus_out_of_town">Harga Jual Dus Luar Kota</label>
                        <input name="price_sell_dus_out_of_town" type="number" class="form-control"
                            placeholder="Masukan harga jual dus luar kota"
                            value="{{ $product->price_sell_dus_out_of_town }}" />
                        <span>Harga jual dus luar kota: {{ $product->price_sell_dus_out_of_town }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="price_sell_pak_out_of_town">Harga Jual Pak Luar Kota</label>
                        <input name="price_sell_pak_out_of_town" type="number" class="form-control"
                            placeholder="Masukan harga jual pak luar kota"
                            value="{{ $product->price_sell_pak_out_of_town }}" />
                        <span>Harga jual pak luar kota: {{ $product->price_sell_pak_out_of_town }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-10">
                        <label class="form-label" for="price_sell_eceran_out_of_town">Harga Jual Eceran Luar
                            Kota</label>
                        <input name="price_sell_eceran_out_of_town" type="number" class="form-control"
                            placeholder="Masukan harga jual eceran luar kota"
                            value="{{ $product->price_sell_eceran_out_of_town }}" />
                        <span>Harga jual eceran luar kota: {{ $product->price_sell_eceran_out_of_town }}</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
@endsection
@push('addon-script')
<script>
    $(document).ready(function() {
            $('#otherSelect').select2({
                tags: true,
            });
        });
</script>
@endpush