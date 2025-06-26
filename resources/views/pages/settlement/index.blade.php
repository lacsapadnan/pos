@extends('layouts.dashboard')

@section('title', 'Buat Settlement')
@section('menu-title', 'Buat Settlement')

@push('addon-style')
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
</style>
@endpush

@section('content')
@include('components.alert')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <!--begin::Search-->
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                        class="path2"></span></i> <input type="text" data-kt-filter="search"
                    class="form-control form-control-solid w-250px ps-14" placeholder="Cari data mutasi">
            </div>
            <!--end::Search-->
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <button id="submitBtn" class="btn btn-success">Simpan</button>
            <a class="btn btn-primary" href="{{ route('settlement.create') }}">Lihat Settlement</a>
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
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th>No</th>
                            <th>
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                                        data-kt-check-target="#kt_datatable_example .form-check-input" value="1" />
                                </div>
                            </th>
                            <th>Tgl Mutasi</th>
                            <th>Dari Pos</th>
                            <th>Kas Pengirim</th>
                            <th>Total</th>
                            <th>Kasir Penerima</th>
                            <th>Total Telah Diterima</th>
                            <th>Outstanding</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('pages.settlement.modal')
@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    "use strict";

        // Class definition
        var KTDatatablesExample = function() {
            // Shared variables
            var table;
            var datatable;
            var totalReceivedData = {};

            // Private functions
            var initDatatable = function() {
                // Init datatable with server-side processing
                datatable = $(table).DataTable({
                    "info": true,
                    'order': [],
                    'pageLength': 10,
                    scrollX: true,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        url: '{{ route('settlement.serverside') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        error: function(xhr, error, code) {
                            console.log('Ajax error:', xhr, error, code);
                        }
                    },
                    "ordering": false,
                    "columns": [{
                            data: "id",
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${row.id}" />
                            </div>`;
                            }
                        },
                        {
                            data: "input_date",
                            render: function(data, type, row) {
                                return moment(data).format('DD MMMM YYYY');
                            }
                        },
                        {
                            data: "from_warehouse"
                        },
                        {
                            data: "from_treasury"
                        },
                        {
                            data: "amount",
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
                            data: "output_cashier"
                        },
                        {
                            data: "total_received", // Make sure this matches the property name in your data source
                            render: function(data, type, row) {
                                var totalReceivedValue = data || 0;
                                var formattedTotalReceived = totalReceivedValue !== 0 ?
                                    new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(totalReceivedValue).replace(",00", "") :
                                    totalReceivedValue;

                                return `
                                    <input type="number" name="total_received" class="form-control" value="${formattedTotalReceived}">
                                    <input type="hidden" name="mutation_id" value="${row.id}">
                                `;
                            }
                        },
                        {
                            data: "outstanding",
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
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <button class="btn btn-success btn-open-modal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_1"
                                        data-amount="${row.amount}"
                                        data-mutation-id="${row.id}"
                                        data-outstanding="${row.outstanding}">Aksi</button>
                                `;
                            }
                        }
                    ],
                    columnDefs: [{
                        target: [5, 9],
                        className: 'min-w-100px'
                    }],
                    select: {
                        style: 'multi',
                        selector: 'td:nth-child(2) input[type="checkbox"]',
                        className: 'row-selected'
                    },
                });
                                $(document).on('click', '#submitBtn', function() {
                    var inputRequests = [];

                    // Get checked rows from current DataTable data
                    datatable.rows().every(function(rowIdx, tableLoop, rowLoop) {
                        var $row = $(this.node());
                        var isChecked = $row.find('.form-check-input').prop('checked');

                        if (isChecked) {
                            var rowData = this.data();
                            var totalReceived = $row.find('input[name="total_received"]').val();

                            // Clean the value - remove currency formatting
                            if (typeof totalReceived === 'string') {
                                totalReceived = totalReceived.replace(/[^0-9.]/g, '');
                            }

                            // Validate that we have required data
                            if (rowData && rowData.id && totalReceived && parseFloat(totalReceived) > 0) {
                                var inputRequest = {
                                    mutation_id: rowData.id,
                                    total_received: parseFloat(totalReceived) || 0,
                                };

                                inputRequests.push(inputRequest);
                            }
                        }
                    });

                    if (inputRequests.length === 0) {
                        alert('Please select at least one row and enter an amount greater than 0.');
                        return;
                    }

                    console.log('Input requests:', inputRequests);

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('settlement.store') }}',
                        type: 'POST',
                        data: {
                            requests: inputRequests
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            alert('Settlements created successfully!');
                            datatable.draw(false); // Reload table data without resetting pagination
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', xhr.responseText);
                            var errorMessage = 'An error occurred while processing the request.';

                            try {
                                var errorResponse = JSON.parse(xhr.responseText);
                                errorMessage = errorResponse.error || errorMessage;
                            } catch (e) {
                                // Use default message
                            }

                            alert('Error: ' + errorMessage);
                        }
                    });
                });

              }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Settlement Report';
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
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
</script>
<script>
    $(document).on('click', '.btn-open-modal', function() {
            var amount = $(this).data('amount');
            var outstanding = $(this).data('outstanding');
            var mutationId = $(this).data('mutation-id');

            var formattedAmount = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);

            var formattedOutstanding = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(outstanding);

            formattedAmount = formattedAmount.replace(",00", "");
            formattedOutstanding = formattedOutstanding.replace(",00", "");

            // Set the values in the modal fields
            $('#kt_modal_1').find('input[name="amountData"]').val(formattedAmount).prop('disabled', true);
            $('#kt_modal_1').find('input[name="outstandingData"]').val(formattedOutstanding).prop('disabled', true);
            $('#kt_modal_1').find('input[name="mutation_id"]').val(mutationId);
        });
</script>
@endpush