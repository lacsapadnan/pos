@extends('layouts.dashboard')

@section('title', 'Mutasi Kas')
@section('menu-title', 'Mutasi Kas')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    {{-- session success --}}
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
                <!--begin::Export dropdown-->
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Export Data
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_1">
                    Tambah Data
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
                    <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_example">
                        <thead>
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                                <th>No.</th>
                                <th>Cabang Awal</th>
                                <th>Cabang Tujuan</th>
                                <th>Dana Kas Keluar</th>
                                <th>Dana Kas Masuk</th>
                                <th>Kasir Pengeluaran</th>
                                <th>Kasir Penerimaan</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                                <th>Tanggal</th>
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
    @includeIf('pages.mutation.modal')
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
                        url: '{{ route('api.mutasi-kas') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            "data": null,
                            "sortable": false,
                            "render": function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: "from_warehouse",
                            render: function(data, type, row) {
                                    return data.name;
                            }
                        },
                        {
                            data: "to_warehouse",
                            render: function(data, type, row) {
                                    return data.name;
                            }
                        },
                        {
                            data: 'from_treasury'
                        },
                        {
                            data: 'to_treasury'
                        },
                        {
                            data: 'output_cashier',
                            render: function(data, type, row) {
                                    return data.name;
                            }
                        },
                        {
                            data: 'input_cashier',
                            render: function(data, type, row) {
                                    return data.name;
                            }
                        },
                        {
                            data: "amount",
                            render: function(data, type, row) {
                                return 'Rp' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                                    ".");
                            }
                        },
                        {
                            data: "description",
                            render: function(data, type, row) {
                                if (data == null) {
                                    return "Tidak ada deskripsi";
                                } else {
                                    return data;
                                }
                            }
                        },
                        {
                            data: "date",
                            render: function(data, type, row) {
                                return moment(data).format('DD MMMM YYYY');
                            }
                        },
                        {
                            "data": null,
                            "sortable": false,
                            "render": function(data, type, row, meta) {
                                return `
                                    <form action="{{ url('mutasi-kas') }}/${row.id}" method="POST" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                `;
                            }
                        }
                    ],
                    columnDefs: [{
                        target: 7,
                        className: 'min-w-100px'
                    }],
                });
            }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Laporan Mutasi Kas';
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
                    handleSearchDatatable();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
    </script>
@endpush
