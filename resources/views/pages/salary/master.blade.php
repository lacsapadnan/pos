@extends('layouts.dashboard')

@section('title', 'Master Gaji Karyawan')
@section('menu-title', 'Master Gaji Karyawan')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@include('includes.datatable-pagination')

@section('content')
@include('components.alert')

<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-14"
                    placeholder="Cari karyawan">
            </div>
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <!-- Filters -->
            <div class="row g-3">
                <div class="col-md-6">
                    <select class="form-select" data-control="select2" data-placeholder="Pilih Cabang"
                        id="warehouseFilter">
                        <option value="">Semua Cabang</option>
                        @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="active">Sudah Diatur</option>
                        <option value="inactive">Belum Diatur</option>
                    </select>
                </div>
            </div>

            <!-- Add button -->
            @can('simpan gaji')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#salaryConfigModal">
                <i class="ki-duotone ki-plus fs-2"></i>
                Atur Gaji Karyawan
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body">
        <div class="mb-6 alert alert-info">
            <h5 class="alert-heading"><i class="ki-duotone ki-information-5 fs-3 me-2"></i>Master Gaji Karyawan</h5>
            <p class="mb-0">Halaman ini digunakan untuk mengatur gaji dasar karyawan yang akan digunakan dalam
                perhitungan gaji bulanan. Setiap karyawan dapat memiliki gaji harian, bulanan, atau keduanya.</p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                id="masterSalaryTable">
                <thead>
                    <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                        <th>No</th>
                        <th>Karyawan</th>
                        <th>Cabang</th>
                        <th>Gaji Harian</th>
                        <th>Gaji Bulanan</th>
                        <th>Status</th>
                        <th>Terakhir Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $index => $employee)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-4">
                                    <div class="symbol-label bg-light-primary text-primary fw-bold">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold">{{ $employee->name }}</span>
                                    <span class="text-muted">{{ $employee->user->email ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $employee->warehouse->name ?? '-' }}</td>
                        <td>
                            @if($employee->daily_salary)
                            <span class="badge badge-light-info">Rp {{ number_format($employee->daily_salary, 0, ',',
                                '.') }}</span>
                            @else
                            <span class="text-muted">Tidak diatur</span>
                            @endif
                        </td>
                        <td>
                            @if($employee->monthly_salary)
                            <span class="badge badge-light-success">Rp {{ number_format($employee->monthly_salary, 0,
                                ',', '.') }}</span>
                            @else
                            <span class="text-muted">Tidak diatur</span>
                            @endif
                        </td>
                        <td>
                            @if($employee->daily_salary || $employee->monthly_salary)
                            <span class="badge badge-success">Sudah Diatur</span>
                            @else
                            <span class="badge badge-secondary">Belum Diatur</span>
                            @endif
                        </td>
                        <td>{{ $employee->updated_at->format('d M Y H:i') }}</td>
                        <td>
                            @can('update gaji')
                            <button class="btn btn-sm btn-light-primary edit-salary" data-id="{{ $employee->id }}"
                                data-name="{{ $employee->name }}" data-daily="{{ $employee->daily_salary }}"
                                data-monthly="{{ $employee->monthly_salary }}" title="Edit Gaji">
                                <i class="ki-duotone ki-pencil fs-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Salary Configuration Modal -->
<div class="modal fade" id="salaryConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="salaryConfigModalTitle">Atur Gaji Karyawan</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <form id="salaryConfigForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="employeeId" name="employee_id">

                    <div class="mb-6 row">
                        <div class="col-md-12">
                            <label class="form-label">Karyawan</label>
                            <input type="text" class="form-control" id="employeeName" readonly>
                        </div>
                    </div>

                    <div class="mb-6 row">
                        <div class="col-md-6">
                            <label class="form-label">Gaji Harian</label>
                            <input type="number" class="form-control" id="dailySalary" name="daily_salary"
                                placeholder="Masukkan gaji harian" min="0" step="1000">
                            <div class="form-text">Kosongkan jika tidak menggunakan gaji harian</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gaji Bulanan</label>
                            <input type="number" class="form-control" id="monthlySalary" name="monthly_salary"
                                placeholder="Masukkan gaji bulanan" min="0" step="10000">
                            <div class="form-text">Kosongkan jika tidak menggunakan gaji bulanan</div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="ki-duotone ki-information-5 fs-3 me-2"></i>Informasi Penting:</h6>
                        <ul class="mb-0">
                            <li>Minimal satu jenis gaji (harian atau bulanan) harus diisi</li>
                            <li>Gaji harian akan dikalikan dengan jumlah hari hadir</li>
                            <li>Gaji bulanan akan digunakan sebagai gaji tetap</li>
                            <li>Jika keduanya diisi, sistem akan menggunakan yang lebih menguntungkan</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveSalaryConfig">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

<script>
    "use strict";

    var table;
    var datatable;

    $(document).ready(function() {
        // Initialize DataTable
        initDatatable();

        // Filter functionality - automatic filtering on change
        $('#warehouseFilter, #statusFilter').on('change', function() {
            applyFilters();
        });

        // Edit salary handler
        $(document).on('click', '.edit-salary', function() {
            var employeeId = $(this).data('id');
            var employeeName = $(this).data('name');
            var dailySalary = $(this).data('daily');
            var monthlySalary = $(this).data('monthly');

            $('#salaryConfigModalTitle').text('Edit Gaji Karyawan');
            $('#employeeId').val(employeeId);
            $('#employeeName').val(employeeName);
            $('#dailySalary').val(dailySalary);
            $('#monthlySalary').val(monthlySalary);

            $('#salaryConfigModal').modal('show');
        });

        // Reset modal on close
        $('#salaryConfigModal').on('hidden.bs.modal', function() {
            $('#salaryConfigForm')[0].reset();
            $('#salaryConfigModalTitle').text('Atur Gaji Karyawan');
            $('#employeeId').val('');
        });

        // Save salary configuration
        $('#saveSalaryConfig').click(function() {
            var employeeId = $('#employeeId').val();
            var dailySalary = $('#dailySalary').val();
            var monthlySalary = $('#monthlySalary').val();

            if (!employeeId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Data karyawan tidak valid'
                });
                return;
            }

            if (!dailySalary && !monthlySalary) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Minimal satu jenis gaji (harian atau bulanan) harus diisi'
                });
                return;
            }

            $.ajax({
                url: '/karyawan/' + employeeId,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    daily_salary: dailySalary,
                    monthly_salary: monthlySalary
                },
                success: function(response) {
                    $('#salaryConfigModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Konfigurasi gaji berhasil disimpan',
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    var message = 'Terjadi kesalahan sistem';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: message
                    });
                }
            });
        });
    });

    function initDatatable() {
        table = document.querySelector('#masterSalaryTable');
        datatable = $(table).DataTable({
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false, searchable: false }
            ]
        });

        // Search functionality
        document.querySelector('[data-kt-filter="search"]').addEventListener('keyup', function(e) {
            datatable.search(e.target.value).draw();
        });
    }

    function applyFilters() {
        var warehouseFilter = $('#warehouseFilter').val();
        var statusFilter = $('#statusFilter').val();

        // Clear existing search
        $.fn.dataTable.ext.search.pop();

        if (warehouseFilter || statusFilter) {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var warehouse = data[2]; // Warehouse column
                    var status = data[5]; // Status column

                    var warehouseMatch = true;
                    var statusMatch = true;

                    if (warehouseFilter) {
                        var warehouseText = $('#warehouseFilter option:selected').text();
                        warehouseMatch = warehouse.indexOf(warehouseText) !== -1;
                    }

                    if (statusFilter) {
                        if (statusFilter === 'active') {
                            statusMatch = status.indexOf('Sudah Diatur') !== -1;
                        } else if (statusFilter === 'inactive') {
                            statusMatch = status.indexOf('Belum Diatur') !== -1;
                        }
                    }

                    return warehouseMatch && statusMatch;
                }
            );
        }

        datatable.draw();
    }
</script>
@endpush
