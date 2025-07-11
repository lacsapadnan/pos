@extends('layouts.dashboard')

@section('title', 'Pembelian')
@section('menu-title', 'Pembelian Barang')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
<style>
    ::-webkit-scrollbar-thumb {
        -webkit-border-radius: 10px;
        border-radius: 10px;
        background: rgba(192, 192, 192, 0.3);
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
        background-color: #818B99;
    }

    .dataTables_scrollBody {
        transform: rotateX(180deg) !important;
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 16px !important;
        ;
    }

    .dataTables_scrollBody table {
        transform: rotateX(180deg) !important;
        ;
    }
</style>
@endpush

@section('content')
@include('components.alert')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="mt-3">
        <form id="form1">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3 row align-items-center">
                        <label for="inputEmail3" class="col-form-label">Tanggal Terima</label>
                        <div class="input-group" id="kt_td_picker_date_only" data-td-target-input="nearest"
                            data-td-target-toggle="nearest">
                            <input id="kt_td_picker_date_only_input" type="text" class="form-control"
                                data-td-target="#kt_td_picker_date_only" name="reciept_date" value="{{ date('Y-m-d') }}"
                                disabled />
                            <span class="input-group-text" data-td-target="#kt_td_picker_date_only"
                                data-td-toggle="datetimepicker">
                                <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span
                                        class="path2"></span></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3 row align-items-center me-1">
                        <label for="inputEmail3" class="col-form-label">No. Order</label>
                        <input id="order_number" type="text" name="order_number" class="form-control"
                            value="{{ $orderNumber }}" readonly />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3 row align-items-center">
                        <label for="inputEmail3" class="col-form-label">No. Faktur Supplier</label>
                        <input id="invoice" type="text" name="invoice" class="form-control"
                            placeholder="Masukan nomor faktur" value="{{ old('invoice') }}" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3 row align-items-center">
                        <label for="inputEmail3" class="col-form-label">Supplier</label>
                        <select id="supplier_id" class="form-select" name="supplier_id" data-control="select2"
                            data-placeholder="Pilih Supplier" data-allow-clear="true">
                            <option value="{{ old('supplier_id') }}"></option>
                            @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
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
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th class="min-w-100px">Nama Barang</th>
                            <th>Stok</th>
                            <th>Jml Per Dus</th>
                            <th>Jml Per Pak</th>
                            <th>Jml Beli Dus</th>
                            <th>Harga Dus</th>
                            <th>Diskon Fix</th>
                            <th>Diskon Persen</th>
                            <th>Jml Beli Pak</th>
                            <th>Harga Pak</th>
                            <th>Diskon Fix</th>
                            <th>Diskon Persen</th>
                            <th>Jml Beli Eceran</th>
                            <th>Harga Eceran</th>
                            <th>Diskon Fix</th>
                            <th>Diskon Persen</th>
                            <th class="min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 row">
        <div class="col-md-8">
            <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
                <div class="table-responsive">
                    <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_cart">
                        <thead>
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                                <th>No</th>
                                <th>Kelompok</th>
                                <th class="min-w-100px">Nama Barang</th>
                                <th>Jml Beli</th>
                                <th>Hrg Satuan</th>
                                <th>Unit</th>
                                <th>Subtotal</th>
                                <th>Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-900 fw-semibold">
                            @foreach ($cart as $cart)
                            <tr class="odd">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cart->product->group }}</td>
                                <td>{{ $cart->product->name }}</td>
                                <td>{{ $cart->quantity }}</td>
                                <td>{{ number_format($cart->price_unit) }}</td>
                                <td>{{ $cart->unit->name }}</td>
                                <td>
                                    {{ number_format($cart->total_price) }}
                                </td>
                                <td>
                                    <form action="{{ route('pembelian.destroyCart', $cart->id) }}" method="POST">
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
            <form id="form2" action="{{ route('pembelian.store') }}" method="post">
                @csrf
                <input type="hidden" name="reciept_date" id="reciept_date_form2">
                <input type="hidden" name="invoice" id="invoice_form2">
                <input type="hidden" name="supplier_id" id="supplier_id_form2">
                <input type="hidden" name="order_number" id="order_number_form2">
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label for="subtotal" class="col-form-label">Subtotal</label>
                            <input type="text" name="subtotal" class="form-control" id="subtotal"
                                value="{{ number_format($subtotal) }}" readonly />
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label for="ppn" class="col-form-label">PPN</label>
                            <input type="text" name="tax" class="form-control" id="ppn" value="0"
                                oninput="calculateTotal()" />
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <div class="col">
                        <div class="mb-1">
                            <label for="potongan" class="col-form-label">Potongan</label>
                            <input type="text" name="potongan" class="form-control" id="potongan"
                                oninput="formatNumber(this); calculateTotal()" value="0" />
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <div class="col">
                        <div class="mb-1">
                            <label for="bayar" class="col-form-label">Bayar</label>
                            <input type="text" name="pay" class="form-control" id="bayar"
                                oninput="formatNumber(this); calculateTotal()" value="0" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label for="grandTotal" class="col-form-label">Grand Total</label>
                            <input type="text" name="grand_total" class="form-control" id="grandTotal" readonly />
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label for="sisa" class="col-form-label">Sisa</label>
                            <input type="text" name="remaint" class="form-control" id="sisa" readonly />
                        </div>
                    </div>
                </div>

                <div class="mt-5 row">
                    <div class="mb-1">
                        <label for="inputEmail3" class="col-form-label">Metode Bayar</label>
                        <select name="treasury_id" class="form-select" aria-label="Select example">
                            @forelse ($treasuries as $treasury)
                            <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                            @empty
                            <option value="">Tidak ada metode bayar</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-1">
                        <label for="inputEmail3" class="col-form-label">Keterangan</label>
                        <input name="description" type="text" class="form-control" />
                    </div>
                </div>
                <div class="mt-5 row">
                    <button type="submit" onclick="submitForms()" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@includeIf('pages.purchase.modal')
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

        function calculateSisa(grandTotal, bayar) {
            return Math.max(bayar - grandTotal, 0);
        }

        function calculateTotal() {
            var subtotal = parseFloat(document.getElementById('subtotal').value.replace(/[^0-9.-]+/g, '')) || 0;
            var tax = parseFloat(document.getElementById('ppn').value.replace(/[^0-9.-]+/g, '')) || 0;
            var bayar = parseFloat(document.getElementById('bayar').value.replace(/[^0-9.-]+/g, '')) || 0;
            var potongan = parseFloat(document.getElementById('potongan').value.replace(/[^0-9.-]+/g, '')) || 0;

            // If tax and bayar are not provided, set them to 0
            tax = isNaN(tax) ? 0 : tax;
            bayar = isNaN(bayar) ? 0 : bayar;
            potongan = isNaN(potongan) ? 0 : potongan;

            var grandTotal = subtotal + (subtotal * (tax / 100)) - potongan;

            var sisa = calculateSisa(grandTotal, bayar);

            document.getElementById('ppn').value = tax.toFixed(0);
            document.getElementById('bayar').value = new Intl.NumberFormat('id-ID').format(bayar);
            document.getElementById('grandTotal').value = new Intl.NumberFormat('id-ID').format(grandTotal);
            document.getElementById('sisa').value = new Intl.NumberFormat('id-ID').format(sisa);
            document.getElementById('potongan').value = potongan.toFixed(0);
        }

        calculateTotal();

        function submitForms() {
            // Get the values from form1
            var recieptDate = $('#kt_td_picker_date_only_input').val();
            var invoice = $('#invoice').val();
            var supplierId = $('#supplier_id').val();

            // Assign the values to the hidden input fields in form2
            $('#reciept_date_form2').val(recieptDate);
            $('#invoice_form2').val(invoice);
            $('#supplier_id_form2').val(supplierId);
            document.getElementById('order_number_form2').value = document.getElementById('order_number').value;

            // Submit both forms
            document.getElementById('form1').submit();
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
                    },
                    "ajax": {
                        url: '{{ route('api.data-all') }}',
                        type: 'GET',
                        data: function(d) {
                            d.searchQuery = $('#searchInput').val();
                        }
                    },
                    "columns": [{
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
                                `;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="price_dus" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="discount_fix_dus" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="discount_percent_dus" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `
                                    <input type="text" name="quantity_pak" class="form-control">
                                    <input type="hidden" name="unit_pak" value="${row.product.unit_pak}">
                                `;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="price_pak" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="discount_fix_pak" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="discount_percent_pak" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `
                                <input type="text" name="quantity_eceran" class="form-control">
                                <input type="hidden" name="unit_eceran" value="${row.product.unit_eceran}">
                            `;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="price_eceran" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="discount_fix_eceran" class="form-control price-input">`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<input type="text" name="discount_percent_eceran" class="form-control price-input">`;
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
                            target: 4,
                            className: 'min-w-80px',
                        },
                        {
                            target: 5,
                            className: 'min-w-150px',
                        },
                        {
                            target: 6,
                            className: 'min-w-100px',
                        },
                        {
                            target: 7,
                            className: 'min-w-50px',
                        },
                        {
                            target: 8,
                            className: 'min-w-100px',
                        },
                        {
                            target: 9,
                            className: 'min-w-150px',
                        },
                        {
                            target: 10,
                            className: 'min-w-100px',
                        },
                        {
                            target: 12,
                            className: 'min-w-80px',
                        },
                        {
                            target: 13,
                            className: 'min-w-150px',
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
                        var unitDus = $(this).find('input[name="unit_dus"]').val();
                        var unitPak = $(this).find('input[name="unit_pak"]').val();
                        var unitEceran = $(this).find('input[name="unit_eceran"]').val();
                        var priceDus = $(this).find('input[name="price_dus"]').val();
                        var pricePak = $(this).find('input[name="price_pak"]').val();
                        var priceEceran = $(this).find('input[name="price_eceran"]').val();
                        var diskonFixDus = $(this).find('input[name="discount_fix_dus"]').val();
                        var diskonFixPak = $(this).find('input[name="discount_fix_pak"]').val();
                        var diskonFixEceran = $(this).find('input[name="discount_fix_eceran"]')
                            .val();
                        var diskonPersenDus = $(this).find('input[name="discount_percent_dus"]')
                            .val();
                        var diskonPersenPak = $(this).find('input[name="discount_percent_pak"]')
                            .val();
                        var diskonPersenEceran = $(this).find(
                            'input[name="discount_percent_eceran"]').val();

                        var inputRequest = {
                            product_id: productId,
                            quantity_dus: quantityDus,
                            quantity_pak: quantityPak,
                            quantity_eceran: quantityEceran,
                            unit_dus: unitDus,
                            unit_pak: unitPak,
                            unit_eceran: unitEceran,
                            price_dus: priceDus,
                            price_pak: pricePak,
                            price_eceran: priceEceran,
                            discount_fix_dus: diskonFixDus,
                            discount_fix_pak: diskonFixPak,
                            discount_fix_eceran: diskonFixEceran,
                            discount_percent_dus: diskonPersenDus,
                            discount_percent_pak: diskonPersenPak,
                            discount_percent_eceran: diskonPersenEceran,
                        };

                        inputRequests.push(inputRequest);
                    });

                    console.log(inputRequests);

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('pembelian.addCart') }}',
                        type: 'POST',
                        data: {
                            requests: inputRequests // Pass the array of objects as "requests"
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            // Handle error response
                            console.log(xhr.responseText);
                            console.log('Request data:', inputRequests);
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
                    $(table).on('keydown',
                        'input[name^="quantity_"], input[name^="diskon_"], input[name^="price_"]',
                        function(event) {
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

{{-- Modal --}}
<script>
    function closeModal() {
            const modal = new bootstrap.Modal(document.getElementById('kt_modal_1'));
            modal.hide();

            // Remove the .modal-open class from the body element
            document.body.classList.remove('modal-open');

            // Remove the modal backdrop element
            const modalBackdrop = document.querySelector('.modal-backdrop');
            if (modalBackdrop) {
                modalBackdrop.remove();
            }
        }

        const buttons = document.getElementsByClassName('btn-open-modal');
        Array.from(buttons).forEach(button => {
            button.addEventListener('click', openModal);
        });

        const modalCloseButton = document.querySelector('.modal .close');
        if (modalCloseButton) {
            modalCloseButton.addEventListener('click', closeModal);
        }
</script>
@endpush