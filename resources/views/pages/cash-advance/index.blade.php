@extends('layouts.dashboard')

@section('title', 'Kasbon Karyawan')
@section('menu-title', 'Kasbon Karyawan')

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
                    placeholder="Cari kasbon">
            </div>
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <!-- Filters -->
            <div class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" data-control="select2" data-placeholder="Pilih Cabang"
                        id="warehouseFilter">
                        <option value="">Semua Cabang</option>
                        @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" data-control="select2" data-placeholder="Pilih Karyawan"
                        id="employeeFilter">
                        <option value="">Semua Karyawan</option>
                        @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="typeFilter">
                        <option value="">Semua Tipe</option>
                        <option value="direct">Langsung</option>
                        <option value="installment">Cicilan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" id="filterBtn">
                        <i class="ki-duotone ki-funnel fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Filter
                    </button>
                </div>
            </div>

            <!-- Export and Add buttons -->
            <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-exit-down fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                Export Data
            </button>

            @can('simpan kasbon')
            <a href="{{ route('kasbon.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>
                Tambah Kasbon
            </a>
            @endcan

            <!-- Export Menu -->
            <div id="kt_datatable_example_export_menu"
                class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px"
                data-kt-menu="true">
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="copy">Copy to clipboard</a>
                </div>
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="excel">Export as Excel</a>
                </div>
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="csv">Export as CSV</a>
                </div>
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="pdf">Export as PDF</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                id="cashAdvanceTable">
                <thead>
                    <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                        <th>No</th>
                        <th>No. Kasbon</th>
                        <th>Karyawan</th>
                        <th>Cabang</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="approvalModalTitle">Setujui Kasbon</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <div id="approvalContent">
                    <p>Apakah Anda yakin ingin menyetujui kasbon ini?</p>
                </div>
                <div id="rejectionContent" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="3"
                            placeholder="Masukkan alasan penolakan"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="approveBtn">Setujui</button>
                <button type="button" class="btn btn-danger" id="rejectBtn" style="display: none;">Tolak</button>
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

        // Filter functionality
        $('#filterBtn').click(function() {
            datatable.ajax.reload();
        });

        // Reset filters
        $('.form-select, input[type="date"]').change(function() {
            if ($(this).val() === '') {
                datatable.ajax.reload();
            }
        });

        // Approval/Rejection handlers
        $('#approveBtn').click(function() {
            var cashAdvanceId = $(this).data('id');
            handleApproval(cashAdvanceId, 'approve');
        });

        $('#rejectBtn').click(function() {
            var cashAdvanceId = $(this).data('id');
            var rejectionReason = $('#rejectionReason').val().trim();

            if (!rejectionReason) {
                alert('Alasan penolakan harus diisi');
                return;
            }

            handleApproval(cashAdvanceId, 'reject', rejectionReason);
        });
    });

    function initDatatable() {
        table = document.querySelector('#cashAdvanceTable');
        if (!table) {
            console.log('Table element not found');
            return;
        }

        try {
            datatable = $(table).DataTable({
                "info": false,
                'order': [],
                'pageLength': 10,
                "dom": '<"top"lp>rt<"bottom"lp><"clear">',
                processing: true,
                serverSide: false,
                destroy: true,
                ajax: {
                    url: "{{ route('api.kasbon') }}",
                    type: 'GET',
                    data: function(d) {
                        d.warehouse_id = $('#warehouseFilter').val();
                        d.employee_id = $('#employeeFilter').val();
                        d.status = $('#statusFilter').val();
                        d.type = $('#typeFilter').val();
                    },
                    dataSrc: 'data',
                    error: function(xhr, error, code) {
                        console.log('DataTables AJAX Error:', xhr, error, code);
                        console.log('Response:', xhr.responseText);
                        alert('Error loading data: ' + error);
                    }
                },
            columns: [
                {
                    data: null,
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'advance_number' },
                {
                    data: 'employee',
                    render: function(data) {
                        if (data && data.name) {
                            return `<div>
                                <strong>${data.name}</strong><br>
                                <small class="text-muted">${data.user ? data.user.email : 'No user'}</small>
                            </div>`;
                        }
                        return 'N/A';
                    }
                },
                {
                    data: 'warehouse',
                    render: function(data) {
                        return data ? data.name : 'N/A';
                    }
                },
                {
                    data: 'amount',
                    render: function(data) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(data);
                    }
                },
                {
                    data: 'advance_date',
                    render: function(data) {
                        return new Date(data).toLocaleDateString('id-ID');
                    }
                },
                {
                    data: 'type',
                    render: function(data, type, row) {
                        if (data === 'installment') {
                            return `<span class="badge badge-info">Cicilan (${row.installment_count}x)</span>`;
                        }
                        return '<span class="badge badge-primary">Langsung</span>';
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        var badges = {
                            'pending': '<span class="badge badge-warning">Pending</span>',
                            'approved': '<span class="badge badge-success">Disetujui</span>',
                            'rejected': '<span class="badge badge-danger">Ditolak</span>',
                            'completed': '<span class="badge badge-info">Selesai</span>',
                            'cancelled': '<span class="badge badge-secondary">Dibatalkan</span>'
                        };
                        return badges[data] || data;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.type === 'installment') {
                            var percentage = ((row.paid_amount / row.amount) * 100).toFixed(1);
                            return `<div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: ${percentage}%"
                                     aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                                    ${percentage}%
                                </div>
                            </div>`;
                        }
                        return row.status === 'approved' ?
                            '<span class="badge badge-success">Selesai</span>' :
                            '<span class="badge badge-secondary">-</span>';
                    }
                },
                {
                    data: "id",
                    className: 'min-w-150px',
                    render: function(data, type, row) {
                        var actions = `
                            <a href="{{ route('kasbon.show', ':id') }}" class="btn btn-sm btn-info">
                                <i class="ki-solid ki-eye"></i> Detail
                            </a>
                        `.replace(':id', data);

                        if (row.status === 'pending') {
                            @can('update kasbon')
                            actions += `
                                <a href="{{ route('kasbon.edit', ':id') }}" class="btn btn-sm btn-warning">
                                    <i class="ki-solid ki-pencil"></i> Edit
                                </a>
                            `.replace(':id', data);
                            @endcan

                            @can('approve kasbon')
                            actions += `
                                <button type="button" class="btn btn-sm btn-success" onclick="showApprovalModal(':id', 'approve')">
                                    <i class="ki-solid ki-check"></i> Setujui
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showApprovalModal(':id', 'reject')">
                                    <i class="ki-solid ki-cross"></i> Tolak
                                </button>
                            `.replace(/:id/g, data);
                            @endcan

                            @can('hapus kasbon')
                            actions += `
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCashAdvance(':id')">
                                    <i class="ki-solid ki-trash"></i> Hapus
                                </button>
                            `.replace(':id', data);
                            @endcan
                        }

                        return actions;
                    }
                }
            ]
        });
        } catch (error) {
            console.log('DataTables initialization error:', error);
            alert('Error initializing data table: ' + error.message);
        }
    }

    function showApprovalModal(id, action) {
        var modal = $('#approvalModal');
        var title = action === 'approve' ? 'Setujui Kasbon' : 'Tolak Kasbon';

        $('#approvalModalTitle').text(title);

        if (action === 'approve') {
            $('#approvalContent').show();
            $('#rejectionContent').hide();
            $('#approveBtn').show().data('id', id);
            $('#rejectBtn').hide();
        } else {
            $('#approvalContent').hide();
            $('#rejectionContent').show();
            $('#approveBtn').hide();
            $('#rejectBtn').show().data('id', id);
            $('#rejectionReason').val('');
        }

        modal.modal('show');
    }

        function handleApproval(id, action, rejectionReason = null) {
        var url = action === 'approve' ?
            "{{ route('kasbon.approve', ':id') }}".replace(':id', id) :
            "{{ route('kasbon.reject', ':id') }}".replace(':id', id);

        var data = {
            _token: "{{ csrf_token() }}"
        };

        if (rejectionReason) {
            data.rejection_reason = rejectionReason;
        }

        $.post(url, data)
            .done(function(response) {
                if (response.success) {
                    $('#approvalModal').modal('hide');
                    // Redirect to reload page and show alert via session
                    window.location.reload();
                } else {
                    alert(response.message);
                }
            })
            .fail(function() {
                alert('Terjadi kesalahan sistem');
            });
    }

        function deleteCashAdvance(id) {
        if (confirm('Apakah Anda yakin ingin menghapus kasbon ini?')) {
            var url = "{{ route('kasbon.destroy', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        // Redirect to reload page and show alert via session
                        window.location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan sistem');
                }
            });
        }
    }
</script>
@endpush