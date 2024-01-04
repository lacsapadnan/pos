@extends('layouts.dashboard')

@section('title', 'Buat Settlement')
@section('menu-title', 'Buat Settlement')

@push('addon-style')
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
                <button id="submitBtn" class="btn btn-primary">Simpan</button>
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
                    <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_example">
                        <thead>
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                                <th>No</th>
                                <th>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true"
                                            data-kt-check-target="#kt_datatable_example_1 .form-check-input"
                                            value="1" />
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
                // Set date data order
                const tableRows = table.querySelectorAll('tbody tr');

                function fetchAdditionalData(mutationId, callback) {
                    $.ajax({
                        url: '/settlement/api/data',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            mutation_id: mutationId
                        },
                        success: function(response) {
                            if (response.length > 0) {
                                totalReceivedData[mutationId] = response[0].total_received || 0;
                                console.log('Total Received for Mutation ID ' + mutationId + ': ' +
                                    totalReceivedData[mutationId]);
                                callback(response[0].total_received, response[0].outstanding);
                            } else {
                                totalReceivedData[mutationId] = 0;
                                console.log('Total Received for Mutation ID ' + mutationId +
                                    ' not found. Defaulting to 0.');
                                callback(0, 0);
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching data from API:', error);
                            totalReceivedData[mutationId] =
                                0; // Provide default value in case of an error
                            callback(0, 0); // Provide default values
                        }
                    });
                }


                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                    scrollX: true,
                    "ajax": {
                        url: '{{ route('api.combined-data') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            data: "id",
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            orderable: false,
                            render: function(data) {
                                return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
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
                                    <input type="number" name="total_recieved" class="form-control" value="${formattedTotalReceived}">
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
                            render: function(data, type, row) {
                                return `
                                    <button class="btn btn-primary btn-submit" hidden data-mutation-id="${row.id}">Simpan</button>
                                    <button class="btn btn-primary btn-modal btn-open-modal"
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
                        selector: 'td:first-child input[type="checkbox"]',
                        className: 'row-selected'
                    },
                });

                $(document).on('click', '#submitBtn', function() {
                    var rows = $(table).find('tbody tr');
                    var inputRequests = [];

                    rows.each(function() {
                        var rowData = datatable.row($(this)).data();
                        var mutationId = $(this).find('.btn-submit').data('mutation-id');
                        var totalRecieved = $(this).find('input[name="total_recieved"]').val();
                        var isChecked = $(this).find('.form-check-input').prop('checked');

                        if (isChecked) {
                            var inputRequest = {
                                mutation_id: mutationId,
                                total_recieved: totalRecieved,
                            };

                            inputRequests.push(inputRequest);
                        }
                    });

                    console.log('Input requests:', inputRequests);

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('settlement.store') }}',
                        type: 'POST',
                        data: {
                            requests: inputRequests
                        }, // Ensure that 'requests' key is included
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // return to route settlement.index
                            window.location.href = '{{ route('settlement.index') }}';
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                            console.log('Request data:', inputRequests);
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

            console.log('Amount:', formattedAmount);

            // Set the values in the modal fields
            $('#kt_modal_1').find('input[name="amountData"]').val(formattedAmount).prop('disabled', true);
            $('#kt_modal_1').find('input[name="outstandingData"]').val(formattedOutstanding).prop('disabled', true);
            $('#kt_modal_1').find('input[name="mutation_id"]').val(mutationId);
        });
    </script>
@endpush
