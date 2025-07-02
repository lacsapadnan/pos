@extends('layouts.dashboard')

@section('title', 'Kelola Absensi')
@section('menu-title', 'Kelola Absensi')

@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <div class="card-title d-flex justify-content-between align-items-center w-100">
                <h3>Kelola Absensi Karyawan</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addAttendanceModal">
                    <i class="ki-duotone ki-plus fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Tambah Absensi
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="mb-5 row">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai:</label>
                    <input type="date" class="form-control" id="from_date" value="{{ $today }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai:</label>
                    <input type="date" class="form-control" id="to_date" value="{{ $today }}">
                </div>
                @if(auth()->user()->hasRole('master'))
                <div class="col-md-3">
                    <label class="form-label">Cabang:</label>
                    <select class="form-select" id="warehouse">
                        <option value="">Semua Cabang</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">Karyawan:</label>
                    <select class="form-select" id="employee_id">
                        <option value="">Semua Karyawan</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->warehouse->name ?? 'No
                            Warehouse' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5 gs-7" id="attendance-table">
                    <thead>
                        <tr class="text-gray-800 fw-bold fs-6">
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            @if(auth()->user()->hasRole('master'))
                            <th>Cabang</th>
                            @endif
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Total Jam</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Attendance Modal -->
<div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-labelledby="addAttendanceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAttendanceModalLabel">Tambah Data Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAttendanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                                <select class="form-select" name="employee_id" id="add_employee_id" required
                                    data-dropdown-parent="#addAttendanceModal" data-control="select2">
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} - {{
                                        $employee->warehouse->name ?? 'No
                                        Warehouse' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="add_status" required>
                                    <option value="checked_in">Sedang Bekerja</option>
                                    <option value="checked_out">Sudah Pulang</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="check_in_date" id="add_check_in_date"
                                    value="{{ $today }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="check_in_time" id="add_check_in_time"
                                    required step="1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="add_check_out_date">Tanggal Keluar</label>
                                <input type="date" class="form-control" name="check_out_date" id="add_check_out_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="add_check_out_time">Jam Keluar</label>
                                <input type="time" class="form-control" name="check_out_time" id="add_check_out_time"
                                    step="1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" id="add_notes" rows="3"
                                    placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAttendanceModalLabel">Edit Data Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAttendanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Karyawan</label>
                                <input type="text" class="form-control" id="edit_user_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cabang</label>
                                <input type="text" class="form-control" id="edit_warehouse_name" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="check_in_date" id="edit_check_in_date"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="check_in_time" id="edit_check_in_time"
                                    required step="1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="edit_check_out_date">Tanggal Keluar</label>
                                <input type="date" class="form-control" name="check_out_date" id="edit_check_out_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="edit_check_out_time">Jam Keluar</label>
                                <input type="time" class="form-control" name="check_out_time" id="edit_check_out_time"
                                    step="1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="edit_status" required>
                                    <option value="checked_in">Sedang Bekerja</option>
                                    <option value="checked_out">Sudah Pulang</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('addon-style')
<style>
    /* Ensure action buttons display properly */
    .dataTables_wrapper .btn-group {
        white-space: nowrap;
    }

    .dataTables_wrapper .btn {
        margin-right: 4px;
    }

    /* Fix icon display in buttons */
    .btn .ki-duotone {
        display: inline-block;
    }
</style>
@endpush

@push('addon-script')
<script>
    $(document).ready(function() {
    let table = $('#attendance-table').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });

    // Load data initially
    loadAttendanceData();

    // Auto-reload when filters change
    $('#from_date, #to_date, #warehouse, #employee_id').on('change', function() {
        loadAttendanceData();
    });

    // Set default time values with seconds for time inputs
    $('input[type="time"]').each(function() {
        if (!$(this).val()) {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                             now.getMinutes().toString().padStart(2, '0') + ':' +
                             now.getSeconds().toString().padStart(2, '0');
            $(this).val(timeString);
        }
    });

    function loadAttendanceData() {
        $.get('/absensi/data', {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            warehouse: $('#warehouse').val(),
            employee_id: $('#employee_id').val()
        })
        .done(function(data) {
            table.clear();

            data.forEach(function(attendance, index) {
                let statusBadge = '';
                switch(attendance.status) {
                    case 'checked_in':
                        statusBadge = '<span class="badge badge-success">Sedang Bekerja</span>';
                        break;
                    case 'checked_out':
                        statusBadge = '<span class="badge badge-primary">Sudah Pulang</span>';
                        break;
                }

                let actions = `
                    <div class="gap-2 d-flex">
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${attendance.id}" title="Edit">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${attendance.id}" title="Hapus">
                            Hapus
                        </button>
                    </div>
                `;

                let row = [
                    index + 1,
                    new Date(attendance.check_in).toLocaleDateString('id-ID'),
                    attendance.employee.name,
                ];

                @if(auth()->user()->hasRole('master'))
                row.push(attendance.warehouse ? attendance.warehouse.name : '-');
                @endif

                row.push(
                    new Date(attendance.check_in).toLocaleTimeString('id-ID'),
                    attendance.check_out ? new Date(attendance.check_out).toLocaleTimeString('id-ID') : '-',
                    attendance.total_hours + ' jam',
                    statusBadge,
                    attendance.notes || '-',
                    actions
                );

                table.row.add(row);
            });

            table.draw();
        });
    }

    // Add attendance form
    $('#addAttendanceForm').on('submit', function(e) {
        e.preventDefault();

        $.post('/absensi/create', {
            _token: '{{ csrf_token() }}',
            ...Object.fromEntries(new FormData(this))
        })
        .done(function(response) {
            if (response.success) {
                $('#addAttendanceModal').modal('hide');
                showToast('success', response.message);
                loadAttendanceData();
                $('#addAttendanceForm')[0].reset();
                // Set default time values after reset
                $('input[type="time"]').each(function() {
                    const now = new Date();
                    const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                                     now.getMinutes().toString().padStart(2, '0') + ':' +
                                     now.getSeconds().toString().padStart(2, '0');
                    $(this).val(timeString);
                });
            } else {
                showToast('error', response.message);
            }
        })
        .fail(function(xhr) {
            let message = 'Terjadi kesalahan sistem';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showToast('error', message);
        });
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        let id = $(this).data('id');

        $.get(`/absensi/edit/${id}`)
            .done(function(attendance) {
                $('#edit_user_name').val(attendance.employee.name);
                $('#edit_warehouse_name').val(attendance.warehouse ? attendance.warehouse.name : '-');

                // Set check-in date and time
                let checkIn = new Date(attendance.check_in);
                $('#edit_check_in_date').val(checkIn.toISOString().split('T')[0]);
                $('#edit_check_in_time').val(checkIn.toTimeString().slice(0,8));

                // Set check-out date and time if exists
                if (attendance.check_out) {
                    let checkOut = new Date(attendance.check_out);
                    $('#edit_check_out_date').val(checkOut.toISOString().split('T')[0]);
                    $('#edit_check_out_time').val(checkOut.toTimeString().slice(0,8));
                }

                $('#edit_status').val(attendance.status);
                $('#edit_notes').val(attendance.notes);

                // Store attendance ID for update
                $('#editAttendanceForm').data('id', attendance.id);

                $('#editAttendanceModal').modal('show');
            })
            .fail(function(xhr) {
                showToast('error', 'Gagal memuat data absensi');
            });
    });

    // Edit form submission
    $('#editAttendanceForm').on('submit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.post(`/absensi/update/${id}`, {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            ...Object.fromEntries(new FormData(this))
        })
        .done(function(response) {
            if (response.success) {
                $('#editAttendanceModal').modal('hide');
                showToast('success', response.message);
                loadAttendanceData();
            } else {
                showToast('error', response.message);
            }
        })
        .fail(function(xhr) {
            let message = 'Terjadi kesalahan sistem';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showToast('error', message);
        });
    });

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');

        if (confirm('Apakah Anda yakin ingin menghapus data absensi ini?')) {
            $.post(`/absensi/${id}`, {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            })
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    loadAttendanceData();
                } else {
                    showToast('error', response.message);
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Gagal menghapus data absensi');
            });
        }
    });
});

function showToast(type, message) {
    toastr[type](message);
}
</script>
@endpush
