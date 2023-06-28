@extends('layouts.dashboard')

@section('title', 'Penjualan')
@section('menu-title', 'Penjualan Barang')

@push('addon-style')
    <link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="mt-3">
            <form id="form1">
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3 align-items-center">
                            <label for="inputEmail3" class="col-form-label">Tanggal</label>
                            <div class="input-group" id="kt_td_picker_date_only" data-td-target-input="nearest"
                                data-td-target-toggle="nearest">
                                <input id="kt_td_picker_date_only_input" type="text" class="form-control"
                                    data-td-target="#kt_td_picker_date_only" name="transaction_date"
                                    value="{{ date('Y-m-d') }}" disabled>
                                <!-- Set the value to today's date and make it readonly -->
                                <span class="input-group-text" data-td-target="#kt_td_picker_date_only"
                                    data-td-toggle="datetimepicker">
                                    <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span
                                            class="path2"></span></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3 align-items-center">
                            <label for="inputEmail3" class="col-form-label">Marketing</label>
                            <input id="user_id" type="text" name="user_id" class="form-control"
                                value="{{ auth()->user()->name }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 row align-items-center">
                            <label for="inputEmail3" class="col-form-label">Customer</label>
                            <select id="customer" class="form-select" name="customer_id" data-control="select2"
                                data-placeholder="Pilih customer" data-allow-clear="true">
                                <option value="1">CASH</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3 align-items-center">
                            <label for="inputEmail3" class="col-form-label">Piutang</label>
                            <input id="piutang" type="text" name="piutang" class="form-control"
                                placeholder="Masukan piutang" value="0" readonly />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3 align-items-center">
                            <label for="inputEmail3" class="col-form-label">No. Order</label>
                            <input id="order_number" type="text" name="order_number" class="form-control"
                                placeholder="Masukan no.order" value="{{ $orderNumber }}" readonly />
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data inventori">
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
                                <th>Stok</th>
                                <th>Jml Per Dus</th>
                                <th>Jml Per Pak</th>
                                <th>Jml Jual Dus</th>
                                <th>Diskon Dus</th>
                                <th>Harga Dus</th>
                                <th>Jml Jual Pak</th>
                                <th>Diskon Pak</th>
                                <th>Harga Pak</th>
                                <th>Jml Jual Eceran</th>
                                <th>Diskon Eceran</th>
                                <th>Harga Eceran</th>
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
                                    <th>Jml Beli</th>
                                    <th>Satuan</th>
                                    <th>Diskon</th>
                                    <th>Subtotal</th>
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
                                        <td>{{ $cart->diskon ?? 0 }}</td>
                                        <td>
                                            {{ number_format($cart->price * $cart->quantity) }}
                                        </td>
                                        <td>
                                            <form action="{{ route('penjualan.destroyCart', $cart->id) }}"
                                                method="POST">
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
                <form id="form2" action="{{ route('penjualan.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="transaction_date" id="transaction_date_form2">
                    <input type="hidden" name="order_number" id="order_number_form2">
                    <input type="hidden" name="customer" id="customer_form2">
                    <input type="hidden" name="user_id" id="user_id_form2">
                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <label for="subtotal" class="col-form-label">Subtotal</label>
                                <input type="text" name="subtotal" class="form-control" id="subtotal"
                                    value="{{ $subtotal }}" readonly />
                            </div>
                            <div class="mb-1">
                                <label for="bayar" class="col-form-label">Bayar</label>
                                <input type="text" name="pay" class="form-control" id="bayar"
                                    oninput="calculateTotal()" />
                            </div>
                            <div class="mb-1">
                                <label for="grandTotal" class="col-form-label">Grand Total</label>
                                <input type="text" name="grand_total" class="form-control" id="grandTotal"
                                    readonly />
                            </div>
                            <div class="mb-1">
                                <label for="kembali" class="col-form-label">Kembali</label>
                                <input type="text" name="change" class="form-control" id="kembali" readonly />
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
        function calculateTotal() {
            var subtotal = parseFloat(document.getElementById('subtotal').value.replace(/[^0-9.-]+/g, '')) || 0;
            var bayar = parseFloat(document.getElementById('bayar').value.replace(/[^0-9.-]+/g, '')) || 0;

            var grandTotal = subtotal;
            var kembali = calculateKembali(grandTotal, bayar);

            document.getElementById('grandTotal').value = grandTotal.toFixed(0);
            document.getElementById('kembali').value = kembali.toFixed(0);
        }

        function calculateKembali(grandTotal, bayar) {
            return Math.max(bayar - grandTotal, 0);
        }

        function submitForms() {
            // Copy values from form1 to form2 hidden inputs
            document.getElementById('transaction_date_form2').value = document.getElementById(
                'kt_td_picker_date_only_input').value;
            document.getElementById('order_number_form2').value = document.getElementById('order_number').value;
            document.getElementById('customer_form2').value = document.getElementById('customer').value;
            document.getElementById('user_id_form2').value = document.getElementById('user_id').value;

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
                    fixedColumns: {
                        left: 2
                    },
                    "ajax": {
                        url: '{{ route('api.inventori') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            data: "product.group"
                        },
                        {
                            data: "product.name"
                        },
                        {
                            data: "quantity"
                        },
                        {
                            data: "product.dus_to_eceran"
                        },
                        {
                            data: "product.pak_to_eceran"
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="quantity_dus" class="form-control">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="diskon_dus" class="form-control">`;
                            }
                        },
                        {
                            data: "product.price_dus",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="quantity_pak" class="form-control">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="diskon_pak" class="form-control">`;
                            }
                        },
                        {
                            data: "product.price_pak",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="quantity_eceran" class="form-control">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="diskon_eceran" class="form-control">`;
                            }
                        },
                        {
                            data: "product.price_eceran",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
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
                            target: 5,
                            className: 'min-w-60px',
                        },
                        {
                            target: 6,
                            className: 'min-w-80px',
                        },
                        {
                            target: 8,
                            className: 'min-w-60px',
                        },
                        {
                            target: 9,
                            className: 'min-w-80px',
                        },
                        {
                            target: 11,
                            className: 'min-w-60px',
                        },
                        {
                            target: 12,
                            className: 'min-w-80px',
                        },
                        {
                            targets: -1,
                            className: 'text-center'
                        }
                    ],
                });

                $(table).on('click', '.btn-submit', function() {
                    var rowData = datatable.row($(this).closest('tr')).data();
                    var productId = $(this).data('product-id');
                    var quantityDus = $(this).closest('tr').find('input[name="quantity_dus"]').val();
                    var quantityPak = $(this).closest('tr').find('input[name="quantity_pak"]').val();
                    var quantityEceran = $(this).closest('tr').find('input[name="quantity_eceran"]').val();
                    var diskonDus = $(this).closest('tr').find('input[name="diskon_dus"]').val();
                    var diskonPak = $(this).closest('tr').find('input[name="diskon_pak"]').val();
                    var diskonEceran = $(this).closest('tr').find('input[name="diskon_eceran"]').val();

                    var inputRequest = {
                        product_id: productId,
                        quantity_dus: quantityDus,
                        quantity_pak: quantityPak,
                        quantity_eceran: quantityEceran,
                        diskon_dus: diskonDus,
                        diskon_pak: diskonPak,
                        diskon_eceran: diskonEceran,
                    };

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('penjualan.addCart') }}',
                        type: 'POST',
                        data: inputRequest,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // reload page
                            location.reload();
                        },
                        error: function(xhr, status, error) {
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
