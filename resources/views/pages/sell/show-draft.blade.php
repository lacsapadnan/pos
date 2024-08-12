@extends('layouts.dashboard')

@section('title', 'Penjualan')
@section('menu-title', 'Penjualan Barang')

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

        .dataTables_scrollBody {
            transform: rotateX(180deg);
        }

        .dataTables_scrollBody::-webkit-scrollbar {
            height: 16px;
        }

        .dataTables_scrollBody table {
            transform: rotateX(180deg);
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
                                value="{{ $sell->cashier->name }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 row align-items-center">
                            <label for="inputEmail3" class="col-form-label">Customer</label>
                            <select id="customer" class="form-select" name="customer_id" data-control="select2"
                                data-placeholder="Pilih customer" data-allow-clear="true" required>
                                <option readonly disabled>Pilih Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ $customer->id == $sell->customer_id ? 'selected' : '' }}>{{ $customer->name }}
                                    </option>
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
                                <th>Stok</th>
                                <th>Jml Per Dus</th>
                                <th>Jml Per Pak</th>
                                <th>Jml Jual Dus</th>
                                <th>Diskon Dus</th>
                                <th>Harga Jual Dus</th>
                                <th>Jml Jual Pak</th>
                                <th>Diskon Pak</th>
                                <th>Harga Jual Pak</th>
                                <th>Jml Jual Eceran</th>
                                <th>Diskon Eceran</th>
                                <th>Harga Jual Eceran</th>
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
                                            {{ number_format($cart->price * $cart->quantity - $cart->diskon) }}
                                        </td>
                                        <td>
                                            <form action="{{ route('penjualan-draft.destroyCart', $cart->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="product_id" value="{{ $cart->product_id }}">
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
                <form id="form2" action="{{ route('penjualan-draft.update', $sell->id) }}" method="post">
                    @csrf
                    @method('put')
                    <input type="hidden" name="transaction_date" id="transaction_date_form2">
                    <input type="hidden" name="order_number" id="order_number_form2">
                    <input type="hidden" name="customer" id="customer_form2">
                    <input type="hidden" name="user_id" id="user_id_form2">
                    <input type="hidden" name="status" id="status_form2">
                    <input type="hidden" name="cashier_id" id="cashier_form2" value="{{ $sell->cashier_id }}">

                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <label for="subtotal" class="col-form-label">Subtotal</label>
                                <input type="text" name="subtotal" class="form-control" id="subtotal"
                                    value="{{ number_format($subtotal) }}" readonly />
                            </div>

                            <!-- Parent div with id="bayarDiv" -->
                            <div class="mb-1" id="bayarDiv">
                                <label for="bayar" class="col-form-label">Bayar</label>
                                <input type="text" name="pay" class="form-control" id="bayar"
                                    oninput="calculateTotal()" />
                            </div>

                            <!-- Additional input fields initially hidden -->
                            <div class="mb-1" style="display: none;" id="transferDiv">
                                <label for="transfer" class="col-form-label">Transfer</label>
                                <input type="text" name="transfer" class="form-control" id="transfer" oninput="formatNumber(this); calculateTotal()" />
                            </div>
                            
                            <div class="mb-1" style="display: none;" id="cashDiv">
                                <label for="cash" class="col-form-label">Cash</label>
                                <input type="text" name="cash" class="form-control" id="cash" oninput="formatNumber(this); calculateTotal()" />
                            </div>

                            <div class="mb-1">
                                <label for="grandTotal" class="col-form-label">Grand Total</label>
                                <input type="text" name="grand_total" class="form-control" id="grandTotal"
                                    readonly />
                            </div>

                            <div class="mb-1">
                                <label for="grandTotal" class="col-form-label">Metode Bayar</label>
                                <select name="payment_method" class="form-select" aria-label="Select example"
                                    onchange="togglePaymentFields()">
                                    <option value="">Pilih Pembayaran</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="split">Split Payment</option>
                                </select>
                            </div>

                            <div class="mb-1">
                                <label for="kembali" class="col-form-label">Kembali</label>
                                <input type="text" name="change" class="form-control" id="kembali" readonly />
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 row">
                        <button type="button" onclick="submitForms()" class="btn btn-primary">Simpan</button>
                        <button type="button" onclick="draftForms()" class="mt-5 btn btn-danger">Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @includeIf('pages.sell.modal')
    @includeIf('pages.sell.modal-password')
@endsection

@push('addon-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ URL::asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

    {{-- calculated form --}}
    <script>
        function formatNumber(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');
                
            // Tambahkan separator ribuan
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                
            // Set nilai input dengan format yang baru
            input.value = value;
            }
        function calculateTotal() {
            var subtotal = parseFloat(document.getElementById('subtotal').value.replace(/[^0-9.-]+/g, '')) || 0;
            var grandTotal = subtotal;

            var paymentMethod = document.getElementsByName('payment_method')[0].value;
            var transfer = parseFloat(document.getElementById('transfer').value.replace(/[^0-9.-]+/g, '')) || 0;
            var cash = parseFloat(document.getElementById('cash').value.replace(/[^0-9.-]+/g, '')) || 0;

            if (paymentMethod === 'split') {
                // Calculate grand total based on the sum of transfer and cash
                grandTotal = subtotal;
            }

            var kembali = calculateKembali(paymentMethod, grandTotal, transfer, cash);

            document.getElementById('grandTotal').value = new Intl.NumberFormat('id-ID').format(grandTotal);
            document.getElementById('kembali').value = new Intl.NumberFormat('id-ID').format(kembali);
        }

        function calculateKembali(paymentMethod, grandTotal, transfer, cash) {
            var bayar = parseFloat(document.getElementById('bayar').value.replace(/[^0-9.-]+/g, '')) || 0;

            if (paymentMethod === 'transfer' || paymentMethod === 'cash') {
                // For "Transfer" or "Cash," calculate the change as (transfer or cash) - grand total
                return Math.max((paymentMethod === 'transfer' ? transfer : cash) - grandTotal, 0);
            } else if (paymentMethod === 'split') {
                // For "Split Payment," calculate the change as (transfer + cash) - grand total
                return Math.max(transfer + cash - grandTotal, 0);
            } else {
                // Handle other payment methods, if any, here
                return Math.max(bayar - grandTotal, 0);
            }
        }

        function togglePaymentFields() {
            const paymentMethod = document.getElementsByName('payment_method')[0].value;
            const bayarDiv = document.getElementById('bayarDiv');
            const transferDiv = document.getElementById('transferDiv');
            const cashDiv = document.getElementById('cashDiv');

            bayarDiv.style.display = 'none';
            transferDiv.style.display = 'none';
            cashDiv.style.display = 'none';

            if (paymentMethod === 'transfer') {
                transferDiv.style.display = 'block';
            } else if (paymentMethod === 'cash') {
                cashDiv.style.display = 'block';
            } else if (paymentMethod === 'split') {
                transferDiv.style.display = 'block';
                cashDiv.style.display = 'block';
            }

            // Recalculate grand total when payment method changes
            calculateTotal();
        }

        // Call the function initially to handle the default state of the form
        togglePaymentFields();

        // Attach event listeners to the input fields to trigger the calculation
        document.getElementById('bayar').addEventListener('input', calculateTotal);
        document.getElementById('transfer').addEventListener('input', calculateTotal);
        document.getElementById('cash').addEventListener('input', calculateTotal);

        function submitForms() {
            // Copy values from form1 to form2 hidden inputs
            document.getElementById('transaction_date_form2').value = document.getElementById(
                'kt_td_picker_date_only_input').value;
            document.getElementById('order_number_form2').value = document.getElementById('order_number').value;
            document.getElementById('customer_form2').value = document.getElementById('customer').value;
            document.getElementById('user_id_form2').value = document.getElementById('user_id').value;
            var customerId = document.getElementById('customer_form2').value;

            // Make an AJAX request to check customer status
            $.ajax({
                url: '/check-customer-status', // Update the URL to your Laravel route
                method: 'GET',
                data: {
                    customer_id: customerId
                },
                success: function(response) {
                    if (response.status === 'not_piutang') {
                        document.getElementById('form2').submit();
                    } else {
                        $('#passwordModal').modal('show');
                    }
                },
                error: function(error) {
                    console.error('Error checking customer status:', error);
                }
            });
        }

        function checkMasterUserPassword() {
            var userId = document.getElementById('user_master').value;
            var masterUserPassword = document.getElementById('masterUserPassword').value;

            // Make an AJAX request to validate the master user's password
            $.ajax({
                url: '/validate-master-password', // Update the URL to your Laravel route
                method: 'POST',
                data: {
                    user_id: userId,
                    password: masterUserPassword
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Password is correct, submit form2
                        document.getElementById('form2').submit();
                    } else {
                        // Password is incorrect, show an error message
                        alert('Invalid Master User Password. Please try again.');
                    }
                },
                error: function(error) {
                    console.error('Error validating master user password:', error);
                }
            });
        }

        function draftForms() {
            // Copy values from form1 to form2 hidden inputs
            document.getElementById('transaction_date_form2').value = document.getElementById(
                'kt_td_picker_date_only_input').value;
            document.getElementById('order_number_form2').value = document.getElementById('order_number').value;
            document.getElementById('customer_form2').value = document.getElementById('customer').value;
            document.getElementById('user_id_form2').value = document.getElementById('user_id').value;
            document.getElementById('status_form2').value = 'draft';

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
                        left: 2,
                        right: 1
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
                                return `
                                <input type="text" name="quantity_dus" class="form-control">
                                <input type="hidden" name="unit_dus" value="${row.product.unit_dus}">
                                <input type="hidden" name="price_dus" value="${row.product.price_sell_dus}">
                                <input type="hidden" name="sell_id" value="{{ $sell->id }}">
                                `;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="diskon_dus" class="form-control">`;
                            }
                        },
                        {
                            data: "product.price_sell_dus",
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
                                return `
                                <input type="text" name="quantity_pak" class="form-control">
                                <input type="hidden" name="unit_pak" value="${row.product.unit_pak}">
                                <input type="hidden" name="price_pak" value="${row.product.price_sell_pak}">
                                `;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="diskon_pak" class="form-control">`;
                            }
                        },
                        {
                            data: "product.price_sell_pak",
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
                                return `
                                <input type="text" name="quantity_eceran" class="form-control">
                                <input type="hidden" name="unit_eceran" value="${row.product.unit_eceran}">
                                <input type="hidden" name="price_eceran" value="${row.product.price_sell_eceran}">
                                `;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="number" name="diskon_eceran" class="form-control">`;
                            }
                        },
                        {
                            data: "product.price_sell_eceran",
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
                            className: 'min-w-80px',
                        },
                        {
                            target: 7,
                            className: 'min-w-100px',
                        },
                        {
                            target: 8,
                            className: 'min-w-80px',
                        },
                        {
                            target: 10,
                            className: 'min-w-100px',
                        },
                        {
                            target: 11,
                            className: 'min-w-80px',
                        },
                        {
                            target: 13,
                            className: 'min-w-100px',
                        },
                        {
                            target: 14,
                            className: 'min-w-80px',
                        },
                        {
                            targets: -1,
                            className: 'text-center'
                        }
                    ],
                });

                $(table).on('click', '.btn-submit', function() {
                    var rows = $(table).find('tbody tr');
                    var inputRequests = [];

                    rows.each(function() {
                        var rowData = datatable.row($(this)).data();
                        var productId = $(this).find('.btn-submit').data('product-id');
                        var quantityDus = $(this).find('input[name="quantity_dus"]').val();
                        var quantityPak = $(this).find('input[name="quantity_pak"]').val();
                        var quantityEceran = $(this).find('input[name="quantity_eceran"]').val();
                        var diskonDus = $(this).find('input[name="diskon_dus"]').val();
                        var diskonPak = $(this).find('input[name="diskon_pak"]').val();
                        var diskonEceran = $(this).find('input[name="diskon_eceran"]').val();
                        var unitDus = $(this).find('input[name="unit_dus"]').val();
                        var unitPak = $(this).find('input[name="unit_pak"]').val();
                        var unitEceran = $(this).find('input[name="unit_eceran"]').val();
                        var priceDus = $(this).find('input[name="price_dus"]').val();
                        var pricePak = $(this).find('input[name="price_pak"]').val();
                        var priceEceran = $(this).find('input[name="price_eceran"]').val();
                        var sellId = $(this).find('input[name="sell_id"]').val();

                        var inputRequest = {
                            product_id: productId,
                            quantity_dus: quantityDus,
                            quantity_pak: quantityPak,
                            quantity_eceran: quantityEceran,
                            diskon_dus: diskonDus,
                            diskon_pak: diskonPak,
                            diskon_eceran: diskonEceran,
                            unit_dus: unitDus,
                            unit_pak: unitPak,
                            unit_eceran: unitEceran,
                            price_dus: priceDus,
                            price_pak: pricePak,
                            price_eceran: priceEceran,
                            cashier_id: '{{ $sell->cashier_id }}',
                            sell_id: sellId
                        };

                        inputRequests.push(inputRequest);
                    });

                    console.log(inputRequests)

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('penjualan-draft.addCart') }}',
                        type: 'POST',
                        data: {
                            requests: inputRequests
                        }, // Ensure that 'requests' key is included
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // reload page
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
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

            return {
                init: function() {
                    table = document.querySelector('#kt_datatable_cart');

                    if (!table) {
                        return;
                    }

                    initDatatable();
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
