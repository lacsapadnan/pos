@extends('layouts.dashboard')

@section('title', 'Piutang')
@section('menu-title', 'Piutang')

@push('addon-style')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@include('includes.datatable-pagination')

@section('content')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <!--begin::Search-->
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                        class="path2"></span></i> <input type="text" data-kt-filter="search"
                    class="form-control form-control-solid w-250px ps-14" placeholder="Cari data piutang">
            </div>
            <!--end::Search-->
            @role('master')
            <div class="ms-2">
                <select id="warehouseFilter" class="form-select" aria-label="Warehouse filter" data-control="select2">
                    <option value="">All Cabang</option>
                    @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ms-3">
                <select id="userFilter" class="form-select" aria-label="User filter" data-control="select2">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-calendar fs-1 position-absolute ms-4"></i>
                <input type="date" id="fromDateFilter" class="form-control form-control-solid ms-2"
                    data-kt-filter="date" placeholder="Dari Tanggal">
                <input type="date" id="toDateFilter" class="form-control form-control-solid ms-2" data-kt-filter="date"
                    placeholder="Ke Tanggal">
            </div>
            @endrole
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <!--begin::Export dropdown-->
            <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export Data
            </button>
            <!--begin::Menu-->
            <div id="kt_datatable_example_export_menu"
                class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px"
                data-kt-menu="true">
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="copy">
                        Copy to clipboard
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="excel">
                        Export as Excel
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="csv">
                        Export as CSV
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="pdf">
                        Export as PDF
                    </a>
                </div>
                <!--end::Menu item-->
            </div>
            <div id="kt_datatable_example_buttons" class="d-none"></div>
        </div>
    </div>
    <div class="card-body">
        <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
            <div class="table-responsive">
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-start fw-bold fs-7 text-uppercase">
                            <th>No. Order</th>
                            <th>Customer</th>
                            <th>Kasir</th>
                            <th>Cabang</th>
                            <th>Total Pembelian</th>
                            <th>Terbayar</th>
                            <th>Sisa Piutang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('pages.sell.modal')

<!-- Modal Pembayaran Piutang -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Pembayaran Piutang</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <div class="mt-2 form-group">
                        <label class="form-label" for="payment_method">Metode Pembayaran:</label>
                        <select class="form-select" id="payment_method" name="payment">
                            <option value="">Pilih Pembayaran</option>
                            <option value="transfer">Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="split">Split</option>
                        </select>
                    </div>
                    <div class="mt-2 form-group">
                        <label class="form-label" for="total_piutang">Total Piutang:</label>
                        <input type="text" class="form-control" id="total_piutang" name="total_piutang" readonly>
                    </div>
                    <div class="mt-2 form-group">
                        <label class="form-label" for="discount">Potongan:</label>
                        <input type="text" class="form-control" id="discount" name="discount"
                            oninput="updateTotalPiutang()">
                    </div>

                    <div class="mt-2 form-group" id="payCreditGroup" style="display: none;">
                        <label class="form-label" for="pay_credit">Jumlah Pembayaran:</label>
                        <input type="text" class="form-control" id="pay_credit" name="pay_credit"
                            oninput="formatNumber(this)">
                    </div>
                    <div class="mt-2 form-group" id="splitPaymentFields" style="display: none;">
                        <label class="form-label" for="pay_credit_cash">Jumlah Pembayaran (Cash):</label>
                        <input type="text" class="form-control" id="pay_credit_cash" name="pay_credit_cash"
                            oninput="formatNumber(this)">
                        <label class="form-label" for="pay_credit_transfer">Jumlah Pembayaran (Transfer):</label>
                        <input type="text" class="form-control" id="pay_credit_transfer" name="pay_credit_transfer"
                            oninput="formatNumber(this)">
                    </div>
                    <div class="mt-2 form-group">
                        <label class="form-label" for="keterangan">Keterangan:</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                    <input type="hidden" id="sell_id" name="sell_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="submitPayment">Submit Pembayaran</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    document.getElementById('payment_method').addEventListener('change', function() {
            var paymentMethod = this.value;
            var payCreditGroup = document.getElementById('payCreditGroup');
            var splitPaymentFields = document.getElementById('splitPaymentFields');

            if (paymentMethod === 'split') {
                payCreditGroup.style.display = 'none';
                splitPaymentFields.style.display = 'block';
            } else if (paymentMethod) {
                payCreditGroup.style.display = 'block';
                splitPaymentFields.style.display = 'none';
            } else {
                payCreditGroup.style.display = 'none';
                splitPaymentFields.style.display = 'none';
            }
        });
</script>
<script>
    "use strict";

        function formatNumber(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');

            // Tambahkan separator ribuan
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            // Set nilai input dengan format yang baru
            input.value = value;
        }
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
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                    "dom": '<"top"lp>rt<"bottom"lp><"clear">',
                    "ajax": {
                        url: '{{ route('api.piutang') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            "data": "order_number"
                        },
                        {
                            "data": "customer.name"
                        },
                        {
                            "data": "cashier.name"
                        },
                        {
                            "data": "warehouse.name"
                        },
                        {
                            "data": "grand_total",
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
                            "data": "pay",
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
                            "data": null,
                            "render": function(data, type, row) {
                                // grand total - paid
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data.grand_total - data.pay);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            }
                        },
                        {
                            data: "id",
                            "render": function(data, type, row) {
                                return `<button class="btn btn-sm btn-primary" onclick="openPaymentModal(${data}, ${row.grand_total - row.pay})">Terima</button>`;
                            }
                        },
                    ],
                });

                $('#fromDateFilter, #toDateFilter, #warehouseFilter, #userFilter').on('change', function() {
                    var fromDate = $('#fromDateFilter').val();
                    var toDate = $('#toDateFilter').val();
                    var warehouse_id = $('#warehouseFilter').val();
                    var user_id = $('#userFilter').val();

                    // Update the URL based on selected filters
                    var url = '{{ route('api.piutang') }}';
                    var params = [];

                    if (fromDate) {
                        params.push('from_date=' + fromDate);
                    }

                    if (toDate) {
                        params.push('to_date=' + toDate);
                    }

                    if (warehouse_id) {
                        params.push('warehouse=' + warehouse_id);
                    }

                    if (user_id) {
                        params.push('user_id=' + user_id);
                    }

                    if (params.length > 0) {
                        url += '?' + params.join('&');
                    }

                    // Load data with updated URL
                    datatable.ajax.url(url).load();
                });

                $(table).on('click', '.btn-submit', function() {
                    var rowData = datatable.row($(this).closest('tr')).data();
                    var sellId = rowData.id;
                    var payCredit = $(this).closest('tr').find('input[name="pay_credit"]').val();
                    var selectedPayment = $(this).closest('tr').find('select[name="payment"]').val();

                    var inputRequest = {
                        sell_id: sellId,
                        pay: payCredit,
                        payment: selectedPayment,
                    };

                    // Send AJAX request with the POST method
                    $.ajax({
                        url: '{{ route('bayar-piutang') }}',
                        type: 'POST',
                        data: inputRequest,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message,
                                });
                            }
                        },
                    });
                });
            }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Customer Orders Report';
                var buttons = new $.fn.dataTable.Buttons(table, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: documentTitle
                        },
                        {
                            extend: 'excelHtml5',
                            title: documentTitle
                        },
                        {
                            extend: 'csvHtml5',
                            title: documentTitle
                        },
                        {
                            extend: 'pdfHtml5',
                            title: documentTitle
                        }
                    ]
                }).container().appendTo($('#kt_datatable_example_buttons'));

                // Hook dropdown menu click event to datatable export buttons
                const exportButtons = document.querySelectorAll(
                    '#kt_datatable_example_export_menu [data-kt-export]');
                exportButtons.forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();

                        // Get clicked export value
                        const exportValue = e.target.getAttribute('data-kt-export');
                        const target = document.querySelector('.dt-buttons .buttons-' +
                            exportValue);

                        // Trigger click event on hidden datatable export buttons
                        target.click();
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
                    exportButtons();
                    handleSearchDatatable();

                    $(table).on('keydown', 'input[name^="pay_credit"]', function(event) {
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

        let originalTotalPiutang = 0;

        function openPaymentModal(sellId, remaining) {
            $('#sell_id').val(sellId);
            $('#pay_credit').val(remaining);
            $('#total_piutang').val(new Intl.NumberFormat('id-ID', {
                style: 'decimal',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(remaining));
            originalTotalPiutang = remaining; // Simpan nilai total piutang asli
            $('#paymentModal').modal('show');
        }

        function updateTotalPiutang() {
            const totalPiutangInput = document.getElementById('total_piutang');
            const discountInput = document.getElementById('discount');

            // Ambil nilai potongan
            const discount = parseFloat(discountInput.value.replace(/[^0-9.-]+/g, "")) || 0;

            // Hitung total piutang baru
            const newTotal = originalTotalPiutang - discount;

            // Format dan set nilai total piutang
            totalPiutangInput.value = new Intl.NumberFormat('id-ID', {
                style: 'decimal',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(newTotal >= 0 ? newTotal : 0); // Pastikan tidak negatif
        }

        $('#paymentModal').on('hidden.bs.modal', function() {
            // Reset semua nilai dalam form modal
            $('#paymentForm')[0].reset();
            $('#payCreditGroup').hide();
            $('#splitPaymentFields').hide();
        });

        $('#submitPayment').click(function() {
            var paymentMethod = $('#payment_method').val();
            var formData = {
                sell_id: $('#sell_id').val(),
                potongan: $('#discount').val(),
                payment: paymentMethod,
                keterangan: $('#keterangan').val(),
            };

            if (paymentMethod === 'split') {
                formData.pay_credit_cash = $('#pay_credit_cash').val();
                formData.pay_credit_transfer = $('#pay_credit_transfer').val();
            } else {
                formData.pay = $('#pay_credit').val();
            }

            $.ajax({
                url: '{{ route('bayar-piutang') }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                        }).then(() => {
                            $('#paymentModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
            });
        });
</script>
@endpush
