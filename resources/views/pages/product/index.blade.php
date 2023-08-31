@extends('layouts.dashboard')

@section('title', 'Produk')
@section('menu-title', 'Produk')

@push('addon-style')
    <link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    {{-- session success --}}
    @if (session()->has('success'))
        <!--begin::Alert-->
        <div class="p-5 mb-10 alert alert-primary d-flex align-items-center">
            <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-primary">Sukses</h4>
                <span>{{ session()->get('success') }}</span>
            </div>
            <button type="button"
                class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-2x text-primary"><span class="path1"></span><span
                        class="path2"></span></i>
            </button>
        </div>
    @endif
    @if ($errors->any())
        <div class="p-5 mb-10 alert alert-dismissible bg-danger d-flex flex-column flex-sm-row">
            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                <h4 class="mb-2 text-light">Gagal Menyimpan data</h4>
                <span>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            </div>
            <button type="button"
                class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-light"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data produk"
                        id="searchInput">
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
                <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_2">
                    <i class="ki-duotone ki-exit-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Import Data Data
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
                                <th class="min-w-100px">Kelompok</th>
                                <th class="min-w-200px">Nama Barang</th>
                                <th>Barcode Dus</th>
                                <th>Barcode Pak</th>
                                <th>Barcode Ecer</th>
                                <th>Satuan Dus</th>
                                <th>Satuan Pak</th>
                                <th>Satuan Eceran</th>
                                <th>Jml. Dus ke Eceran</th>
                                <th>Jml. Pak ke Eceran</th>
                                <th>Harga Eceran Terakhir</th>
                                <th>Harga Jual Dus</th>
                                <th>Harga Jual Pak</th>
                                <th>Harga Jual Eceran</th>
                                <th>Hadiah</th>
                                <th class="min-w-200px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-900 fw-semibold">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @includeIf('pages.product.modal')
    @includeIf('pages.product.import')
@endsection

@push('addon-script')
    <script src="{{ URL::asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
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
                    order: [],
                    pageLength: 10,
                    scrollX: true,
                    fixedColumns: {
                        left: 2
                    },
                    deferRender: true,
                    processing: true,
                    serverSide: true,
                    "ajax": {
                        url: '{{ route('api.produk-search') }}',
                        type: 'GET',
                        data: function(d) {
                            d.searchQuery = $('#searchInput').val();
                            console.log(d.searchQuery);
                        }
                    },
                    "columns": [{
                            data: 'group'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'barcode_dus',
                            defaultContent: '-'
                        },
                        {
                            data: 'barcode_pak',
                            defaultContent: '-'
                        },
                        {
                            data: 'barcode_eceran',
                            defaultContent: '-'
                        },
                        {
                            data: 'unit_dus.name',
                            defaultContent: '-'
                        },
                        {
                            data: 'unit_pak.name',
                            defaultContent: '-'
                        },
                        {
                            data: 'unit_eceran.name',
                            defaultContent: '-'
                        },
                        {
                            data: 'dus_to_eceran',
                        },
                        {
                            data: 'pak_to_eceran'
                        },
                        {
                            data: 'lastest_price_eceran',
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
                            data: 'price_sell_dus',
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
                            data: 'price_sell_pak',
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
                            data: 'price_sell_eceran',
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
                            data: 'hadiah',
                            defaultContent: '-'
                        },
                        {
                            data: null
                        },
                    ],
                    "columnDefs": [{
                            className: 'min-w-100px',
                            targets: 0
                        },
                        {
                            className: 'min-w-200px',
                            targets: 1
                        },
                        {
                            className: 'min-w-100px',
                            targets: [11, 12, 13],
                        },
                        {
                            className: 'min-w-200px',
                            targets: 15,
                            render: function(data, type, row) {
                                var editUrl = "/produk/" + row.id + "/edit";
                                var deleteUrl = "/produk/" + row.id;

                                return '<a href="' + editUrl +
                                    '" type="button" class="btn btn-sm btn-warning me-2">Edit</a>' +
                                    '<form action="' + deleteUrl +
                                    '" method="POST" class="d-inline">' +
                                    '@csrf' +
                                    '@method('delete')' +
                                    '<button class="btn btn-sm btn-danger">Hapus</button>' +
                                    '</form>';
                            },


                        },
                    ]
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
        $(document).ready(function() {
            $('#otherSelect').select2({
                tags: true,
            });
        });
    </script>
@endpush
