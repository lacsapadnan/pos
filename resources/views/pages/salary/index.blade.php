@extends('layouts.dashboard')

@section('title', 'Pembayaran Gaji')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center gap-2">
                <select id="warehouseFilter" class="form-select form-select-solid" style="max-width: 250px;">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>

                <select id="statusFilter" class="form-select form-select-solid" style="max-width: 250px;">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="calculated">Sudah Dihitung</option>
                    <option value="approved">Disetujui</option>
                    <option value="paid">Sudah Dibayar</option>
                </select>

                <select id="periodMonthFilter" class="form-select form-select-solid" style="max-width: 250px;">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                    @endforeach
                </select>

                <select id="periodYearFilter" class="form-select form-select-solid" style="max-width: 250px;">
                    <option value="">Semua Tahun</option>
                    @foreach(range(date('Y') - 2, date('Y')) as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end gap-2" data-kt-user-table-toolbar="base">
                <!--begin::Add user-->
                <a href="{{ route('salary-settings.index') }}" class="btn btn-info">
                    <i class="ki-duotone ki-gear fs-2"></i>
                    Master Data Gaji
                </a>
                <a href="{{ route('gaji.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Tambah Pembayaran
                </a>
                <!--end::Add user-->
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="salary_table">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>No</th>
                    <th>Karyawan</th>
                    <th>Gudang</th>
                    <th>Periode</th>
                    <th>Kehadiran</th>
                    <th>Gaji Kotor</th>
                    <th>Potongan</th>
                    <th>Gaji Bersih</th>
                    <th>Status</th>
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
    var KTSalaryList = function () {
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
                    url: '{{ route('api.gaji') }}',
                    data: function (d) {
                        d.warehouse_id = $('#warehouseFilter').val();
                        d.status = $('#statusFilter').val();
                        d.period_month = $('#periodMonthFilter').val();
                        d.period_year = $('#periodYearFilter').val();
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
                    { data: 'period' },
                    { data: 'attendance_percentage' },
                    {
                        data: 'gross_salary',
                        render: function(data) {
                            if (!data || data == 0) return '-';
                            return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const cashAdvance = parseFloat(data.cash_advance_deduction) || 0;
                            const otherDeductions = parseFloat(data.other_deductions) || 0;
                            const total = cashAdvance + otherDeductions;
                            return 'Rp ' + total.toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'net_salary',
                        render: function(data) {
                            if (!data || data == 0) return '-';
                            return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                        }
                    },
                    { data: 'status_label' },
                    { data: 'actions' }
                ],
            });
        }

        // Search Datatable - Apply filters automatically when changed
        var handleSearchDatatable = function () {
            $('#warehouseFilter, #statusFilter, #periodMonthFilter, #periodYearFilter').on('change', function () {
                datatable.ajax.reload();
            });
        }

        // Delete salary
        var handleDeleteRows = function () {
            // Delete button action
            table.addEventListener('click', function(e) {
                const target = e.target;

                // Check if clicked element is delete button
                if (target.classList.contains('btn-delete')) {
                    e.preventDefault();

                    // Get salary ID
                    const salaryId = target.getAttribute('data-id');
                    const employeeName = target.getAttribute('data-employee');

                    // SweetAlert2 popup
                    Swal.fire({
                        text: `Apakah anda yakin ingin menghapus data gaji ${employeeName}?`,
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
                                url: `/gaji/${salaryId}`,
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
                table = document.querySelector('#salary_table');

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
        KTSalaryList.init();
    });
</script>
@endpush
