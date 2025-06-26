@extends('layouts.dashboard')

@section('title', 'Master Data Gaji')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="pt-6 border-0 card-header">
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="my-1 d-flex align-items-center position-relative">
                <select id="warehouseFilter" class="form-select form-select-solid" style="max-width: 250px;">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <!--begin::Add user-->
                <a href="{{ route('salary-settings.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Tambah Data Gaji
                </a>
                <!--end::Add user-->
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="py-4 card-body">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="salary_settings_table">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>No</th>
                    <th>Karyawan</th>
                    <th>Gudang</th>
                    <th>Gaji Harian</th>
                    <th>Gaji Bulanan</th>
                    <th>Catatan</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
@endsection

@push('addon-style')
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('addon-script')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    "use strict";

    // Class definition
    var KTSalarySettings = function () {
        // Shared variables
        var table;
        var datatable;

        // Private functions
        var initDatatable = function () {
            datatable = $(table).DataTable({
                "processing": true,
                "serverSide": true,
                "ordering": false,
                "ajax": {
                    url: '{{ route('salary-settings.data') }}',
                    data: function (d) {
                        d.warehouse_id = $('#warehouseFilter').val();
                    }
                },
                "columns": [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'employee_name' },
                    { data: 'warehouse_name' },
                    {
                        data: 'daily_salary',
                        render: function(data) {
                            return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'monthly_salary',
                        render: function(data) {
                            return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                        }
                    },
                    { data: 'notes' },
                    { data: 'actions' }
                ],
            });
        }

        // Search Datatable
        var handleSearchDatatable = function () {
            $('#warehouseFilter').on('change', function () {
                datatable.ajax.reload();
            });
        }

        // Delete salary setting
        var handleDeleteRows = function () {
            // Delete button action
            table.addEventListener('click', function(e) {
                const target = e.target;

                // Check if clicked element is delete button
                if (target.classList.contains('btn-delete')) {
                    e.preventDefault();

                    // Get salary setting ID
                    const salarySettingId = target.getAttribute('data-id');
                    const salarySettingName = target.getAttribute('data-employee');

                    // SweetAlert2 popup
                    Swal.fire({
                        text: `Apakah anda yakin ingin menghapus data gaji ${salarySettingName}?`,
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Tidak, batal",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function (result) {
                        if (result.value) {
                            // Send delete request
                            $.ajax({
                                url: `/salary-settings/${salarySettingId}`,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(result) {
                                    Swal.fire({
                                        text: "Data gaji berhasil dihapus!",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, mengerti!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function() {
                                        // Refresh datatable
                                        datatable.ajax.reload();
                                    });
                                },
                                error: function(xhr, status, error) {
                                    // Show error message
                                    Swal.fire({
                                        text: "Terjadi kesalahan saat menghapus data gaji.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, mengerti!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        }

        // Public methods
        return {
            init: function () {
                table = document.querySelector('#salary_settings_table');

                if (!table) {
                    return;
                }

                initDatatable();
                handleSearchDatatable();
                handleDeleteRows();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function () {
        KTSalarySettings.init();
    });
</script>
@endpush