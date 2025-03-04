@extends('layouts.dashboard')

@section('title', 'Pindah Stok')
@section('menu-title', 'Pindah Stok')

@push('addon-style')
    <link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        ::-webkit-scrollbar-thumb {
            -webkit-border-radius: 10px;
            border-radius: 10px;
            background: rgba(192, 192, 192, 0.3);
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
            background-color: #818B99;
        }
    </style>
@endpush

@section('content')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="mt-3">
            <form id="form1">
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3 align-items-center">
                            <label for="inputEmail3" class="col-form-label">Marketing</label>
                            <input id="user_id" type="text" name="user_id" class="form-control"
                                value="{{ auth()->user()->name }}" readonly />
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @include('components.alert')
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data inventori"
                        id="searchInput">
                </div>
                <!--end::Search-->
            </div>
        </div>
        <div class="card-body">
            <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
                <div class="table-responsive">
                    <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_example">
                        <thead>
                            <tr class="text-start fw-bold fs-7 text-uppercase">
                                <th>Kelompok</th>
                                <th>Nama Barang</th>
                                <th>Barcode Dus</th>
                                <th>Barcode Eceran</th>
                                <th>Stok</th>
                                <th>Jml Pindah Dus</th>
                                <th>Jml Pindah Pak</th>
                                <th>Jml Pindah Eceran</th>
                                <th class="min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4 row">
            <div class="col-md-8">
                <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
                    <div class="table-responsive">
                        <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                            id="kt_datatable_cart">
                            <thead>
                                <tr class="text-start fw-bold fs-7 text-uppercase">
                                    <th>No</th>
                                    <th>Kelompok</th>
                                    <th class="min-w-100px">Nama Barang</th>
                                    <th>Jml Stok Pindah</th>
                                    <th>Satuan</th>
                                    <th>Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold">
                                @foreach ($cart as $cart)
                                    <tr class="odd">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $cart->product->group }}</td>
                                        <td>{{ $cart->product->name }}</td>
                                        <td>{{ $cart->quantity }}</td>
                                        <td>{{ $cart->unit->name }}</td>
                                        <td>
                                            <form action="{{ route('pindah-stok.destroyCart', $cart->id) }}" method="POST">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn-sm btn-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <form id="form2" action="{{ route('pindah-stok.store') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Cabang Tujuan</label>
                                <select name="to_warehouse" class="form-select" data-control="select2"
                                    data-placeholder="Pilih cabang tujuan">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option
                                            value="{{ $warehouse->id }} {{ old('to_warehouse') == $warehouse->id ? 'selected' : '' }}">
                                            {{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 row">
                        <button type="button" onclick="submitForms()" class="btn btn-primary">Simpan</button>
                        <button type="button" class="mt-5 btn btn-danger" disabled>Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @includeIf('pages.sell.modal')
@endsection

@push('addon-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ URL::asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

    {{-- calculated form --}}
    <script>
        function submitForms() {
            // Submit form2
            document.getElementById('form2').submit();
        }
    </script>


    {{-- Datepicker --}}
    <script>
        new tempusDominus.TempusDominus(document.getElementById("kt_td_picker_date_only"), {
            localization: {
                locale: "id",
                startOfTheWeek: 1
            },
            display: {
                viewMode: "calendar",
                components: {
                    decades: true,
                    year: true,
                    month: true,
                    date: true,
                    hours: false,
                    minutes: false,
                    seconds: false
                }
            }
        });
    </script>

    {{-- Datatables Top --}}
    <script>
        "use strict";

        // Class definition
        var KTDatatablesExample = function() {
            // Shared variables
            var table;
            var datatable;

            // Private functions
            var initDatatable = function() {
                // Set date data order
                const tableRows = table.querySelectorAll('tbody tr');

                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    "order": [],
                    "pageLength": 10,
                    "scrollX": true,
                    deferRender: true,
                    processing: true,
                    serverSide: true,
                    fixedColumns: {
                        left: 2
                    },
                    "ajax": {
                        url: '{{ route('api.data-all') }}',
                        type: 'GET',
                        data: function(d) {
                            d.searchQuery = $('#searchInput').val();
                        }
                    },
                    "columns": [{
                            data: "product.group"
                        },
                        {
                            data: "product.name"
                        },
                        {
                            data: "product.barcode_dus",
                            render: function(data, type, row) {
                                if (data == null) {
                                    return "Tidak ada barcode";
                                } else {
                                    return data;
                                }
                            }
                        },
                        {
                            data: "product.barcode_eceran",
                            render: function(data, type, row) {
                                if (data == null) {
                                    return "Tidak ada barcode";
                                } else {
                                    return data;
                                }
                            }
                        },
                        {
                            data: "quantity"
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="quantity_dus" class="form-control">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="quantity_pak" class="form-control">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="quantity_eceran" class="form-control">`;
                            }
                        },
                        {
                            data: null,
                            className: 'text-center',
                            render: function(data, type, row) {
                                return `<button class="btn btn-primary btn-submit" data-product-id="${row.product.id}"><i class="fas fa-cart-plus"></i></button>`;
                            }
                        }
                    ],
                    "columnDefs": [{
                            target: 0,
                            className: 'min-w-100px',
                        },
                        {
                            target: 1,
                            className: 'min-w-100px',
                        },
                        {
                            targets: -1,
                            className: 'text-center'
                        }
                    ],
                });

                $(table).on('click', '.btn-submit', function() {
                    var inputRequests = [];

                    $(table).find('tbody tr').each(function() {
                        var productId = $(this).find('.btn-submit').data('product-id');
                        var quantityDus = $(this).find('input[name="quantity_dus"]').val();
                        var quantityPak = $(this).find('input[name="quantity_pak"]').val();
                        var quantityEceran = $(this).find('input[name="quantity_eceran"]').val();

                        if (quantityDus || quantityPak || quantityEceran) { // Only submit if any quantity is filled
                            var inputRequest = {
                                product_id: productId,
                                quantity_dus: quantityDus,
                                quantity_pak: quantityPak,
                                quantity_eceran: quantityEceran,
                            };

                            inputRequests.push(inputRequest);
                        }
                    });

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('pindah-stok.addCart') }}',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ items: inputRequests }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            var errorMessage = xhr.responseJSON.message || 'Terjadi kesalahan, silakan coba lagi.';
                            alert(errorMessage);
                        }
                    });
                });
            }

            // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
            var handleSearchDatatable = () => {
                const filterSearch = document.querySelector('[data-kt-filter="search"]');
                filterSearch.addEventListener('keyup', function(e) {
                    datatable.search(e.target.value).draw();
                });
            }

            // Public methods
            return {
                init: function() {
                    table = document.querySelector('#kt_datatable_example');

                    if (!table) {
                        return;
                    }

                    initDatatable();
                    handleSearchDatatable();
                    $(table).on('keydown', 'input[name^="quantity_"], input[name^="diskon_"]', function(event) {
                        if (event.which === 13) {
                            event.preventDefault();
                            var btnSubmit = $(this).closest('tr').find('.btn-submit');
                            btnSubmit.click();
                        }
                    });
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
    </script>

    {{-- Datatables Bottom --}}
    <script>
        "use strict";

        // Initialize the data table
        var KTDatatablesCart = function() {
            // Shared variables
            var table;
            var datatable;

            // Private functions
            var initDatatable = function() {
                // Set date data order
                const tableRows = table.querySelectorAll('tbody tr');

                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                });
            }


            // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
            var handleSearchDatatable = () => {
                const filterSearch = document.querySelector('[data-kt-filter="search"]');
                filterSearch.addEventListener('keyup', function(e) {
                    datatable.search(e.target.value).draw();
                });
            }

            // Public methods
            return {
                init: function() {
                    table = document.querySelector('#kt_datatable_cart');

                    if (!table) {
                        return;
                    }

                    initDatatable();
                    handleSearchDatatable();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesCart.init();
        });

        // Class definition
    </script>
@endpush
