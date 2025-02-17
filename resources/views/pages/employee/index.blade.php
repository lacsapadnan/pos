@extends('layouts.dashboard')

@section('title', 'Karyawan')
@section('menu-title', 'Karyawan')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
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
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data cabang">
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
                @can('simpan karyawan')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_1">
                        Tambah Data
                    </button>
                @endcan
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
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. Telp</th>
                                <th>Cabang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @includeIf('pages.employee.modal')
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
                // Ensure table is not null
                table = document.querySelector('#kt_datatable_example');
                if (!table) return;

                // Check if DataTable is already initialized
                if ($.fn.dataTable.isDataTable(table)) {
                    datatable = $(table).DataTable();
                    return; // If already initialized, skip the initialization
                }

                // Init DataTable with the proper configuration
                datatable = $(table).DataTable({
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                    ajax: {
                        url: "{{ route('api.karyawan') }}",
                        type: 'GET',
                        dataSrc: ''
                    },
                    columns: [{
                            "data": null,
                            "sortable": false,
                            "render": function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'email',
                        },
                        {
                            data: 'phone',
                        },
                        {
                            data: 'warehouse.name',
                        },
                        {
                            data: "id",
                            className: 'min-w-150px',
                            render: function(data, type, row) {
                                var routeUrl = "{{ route('karyawan.destroy', ':id') }}";
                                routeUrl = routeUrl.replace(':id', data);
                                return `
                            @can('hapus karyawan')
                            <button type="button" onclick="deleteEmployee('${routeUrl}')" class="btn btn-sm btn-danger"><i class="ki-solid ki-trash"></i>Hapus</button>
                            @endcan
                            @can('update karyawan')
                            <a href="{{ route('karyawan.index') }}/${data}/edit" class="btn btn-warning btn-sm mt-2">
                                <i class="ki-solid ki-pencil"></i>
                                Edit
                            </a>
                            @endcan
                        `;
                            }
                        },
                    ]
                });

                // Ensure export buttons are initialized after the DataTable is created
                exportButtons();
            };

            // Hook export buttons
            var exportButtons = function() {
                var buttons = new $.fn.dataTable.Buttons(table, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: 'Customer Orders Report'
                        },
                        {
                            extend: 'excelHtml5',
                            title: 'Customer Orders Report'
                        },
                        {
                            extend: 'csvHtml5',
                            title: 'Customer Orders Report'
                        },
                        {
                            extend: 'pdfHtml5',
                            title: 'Customer Orders Report'
                        }
                    ]
                }).container().appendTo($('#kt_datatable_example_buttons'));

                // Ensure export menu triggers work
                const exportButtons = document.querySelectorAll(
                    '#kt_datatable_example_export_menu [data-kt-export]');
                exportButtons.forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();
                        const exportValue = e.target.getAttribute('data-kt-export');
                        const target = document.querySelector('.dt-buttons .buttons-' +
                        exportValue);
                        target.click();
                    });
                });
            };

            var handleSearchDatatable = () => {
                const filterSearch = document.querySelector('[data-kt-filter="search"]');
                filterSearch.addEventListener('keyup', function(e) {
                    datatable.search(e.target.value).draw();
                });
            }

            // Public methods
            return {
                init: function() {
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
    <script>
        function deleteEmployee(url) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.action = url;
                    form.method = 'POST';
                    form.innerHTML = '@csrf @method('delete')';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endpush
