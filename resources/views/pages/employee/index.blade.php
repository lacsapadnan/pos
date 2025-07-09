@extends('layouts.dashboard')

@section('title', 'Karyawan')
@section('menu-title', 'Karyawan')

@push('addon-style')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@include('includes.datatable-pagination')

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
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th>No</th>
                            <th>Nama</th>
                            <th>Nickname</th>
                            <th>KTP</th>
                            <th>No. Telp</th>
                            <th>Cabang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('pages.employee.modal')

<!-- Modal for viewing KTP image -->
<div class="modal fade" id="kt_modal_view_ktp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Foto KTP</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="text-center modal-body">
                <img id="ktp-image-preview" src="" alt="KTP" class="img-fluid">
            </div>
        </div>
    </div>
</div>

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
                    "dom": '<"top"lp>rt<"bottom"lp><"clear">',
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
                            data: 'nickname',
                        },
                        {
                            data: 'ktp',
                            render: function(data, type, row) {
                                if (data) {
                                    return `<img src="{{ asset('storage/') }}/${data}" alt="KTP" class="img-thumbnail" style="max-width: 60px; max-height: 40px; cursor: pointer;" onclick="showImageModal('{{ asset('storage/') }}/${data}')">`;
                                } else {
                                    return '<span class="text-muted">Tidak ada foto</span>';
                                }
                            }
                        },
                        {
                            data: 'phone',
                        },
                        {
                            data: 'warehouse.name',
                        },
                        {
                            data: 'isActive',
                            render: function(data, type, row) {
                                const status = data ? 'checked' : '';
                                const label = data ? 'Aktif' : 'Non-aktif';
                                return `
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" ${status}
                                            onchange="toggleActive(${row.id}, this.checked)"
                                            id="activeSwitch_${row.id}">
                                        <label class="form-check-label" for="activeSwitch_${row.id}">${label}</label>
                                    </div>`;
                            }
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
                            <a href="{{ route('karyawan.index') }}/${data}/edit" class="btn btn-warning btn-sm">
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
                            title: 'Karyawan Data Report'
                        },
                        {
                            extend: 'excelHtml5',
                            title: 'Karyawan Data Report'
                        },
                        {
                            extend: 'csvHtml5',
                            title: 'Karyawan Data Report'
                        },
                        {
                            extend: 'pdfHtml5',
                            title: 'Karyawan Data Report'
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

        function showImageModal(imageSrc) {
            document.getElementById('ktp-image-preview').src = imageSrc;
            var modal = new bootstrap.Modal(document.getElementById('kt_modal_view_ktp'));
            modal.show();
        }
</script>
<script>
    function toggleActive(id, status) {
        $.ajax({
            url: `/karyawan/${id}/toggle-active`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                isActive: status
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Update the label immediately
                    const label = response.isActive ? 'Aktif' : 'Non-aktif';
                    $(`#activeSwitch_${id}`).next('label').text(label);
                } else {
                    toastr.error(response.message);
                    // Revert the toggle if failed
                    $(`#activeSwitch_${id}`).prop('checked', !status);
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan saat mengubah status');
                // Revert the toggle if failed
                $(`#activeSwitch_${id}`).prop('checked', !status);
            }
        });
    }
</script>
@endpush
