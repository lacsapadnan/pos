@extends('layouts.dashboard')

@section('title', 'Pembelian')
@section('menu-title', 'Pembelian')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data pembelian">
                </div>
                <!--end::Search-->
            </div>
            <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
                <!--begin::Export dropdown-->
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Export Data
                </button>
                <a href="{{ route('pembelian.create') }}" type="button" class="btn btn-primary">
                    Tambah Pembelian
                </a>
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
                    <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_example">
                        <thead>
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                                <th>Faktur Supplier</th>
                                <th>tanggal Terima</th>
                                <th>Supplier</th>
                                <th>kas</th>
                                <th>Cabang</th>
                                <th>Subtotal</th>
                                <th>Diskon</th>
                                <th>Grand Total</th>
                                <th>Bayar</th>
                                <th>Deskripsi</th>
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
    @includeIf('pages.purchase.modal')
@endsection

@push('addon-script')
    <script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
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
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                    "ajax": {
                        url: '{{ route('api.pembelian') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            "data": "invoice"
                        },
                        {
                            "data": "reciept_date",
                        },
                        {
                            "data": "supplier.name"
                        },
                        {
                            "data": "treasury.name"
                        },
                        {
                            "data": "warehouse.name"
                        },
                        {
                            "data": "subtotal",
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
                            "data": "discount",
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
                            "data": "description",
                            "render": function(data, type, row) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": "id",
                            "render": function(data, type, row) {
                                return `<a href="#" class="btn btn-sm btn-primary" onclick="openModal(${data})">Detail</a>`;
                            }
                        },
                    ],
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
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
    </script>
    <script>
        var datatable;

        function openModal(id) {
            // Clear the table body
            $('#kt_datatable_detail tbody').empty();

            // Check if DataTable instance exists and destroy it
            if ($.fn.DataTable.isDataTable('#kt_datatable_detail')) {
                datatable.destroy();
            }

            // Send a request to fetch the sell details for the given ID
            $.ajax({
                url: '/pembelian/' + id,
                method: 'GET',
                success: function(response) {
                    // Initialize the DataTable on the table
                    datatable = $('#kt_datatable_detail').DataTable({
                        data: response,
                        columns: [{
                                data: 'product.name'
                            },
                            {
                                data: 'unit.name'
                            },
                            {
                                data: 'quantity'
                            },
                            {
                                data: 'price',
                                render: function(data, type, row) {
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(data);
                                    formattedPrice = formattedPrice.replace(",00", "");
                                    return formattedPrice;
                                }
                            },
                        ]
                    });

                    // Open the modal
                    $('#kt_modal_1').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(error); // Handle the error appropriately
                }
            });
        }
    </script>
@endpush
