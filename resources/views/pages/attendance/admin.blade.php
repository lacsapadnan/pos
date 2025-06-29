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
                    data-bs-target="#addAttendanceModal" onclick="setTimeout(() => handleStatusChange('add'), 100)">
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
                            <th>Istirahat</th>
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
                                    <option value="on_break">Sedang Istirahat</option>
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
                                    required>
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
                                <input type="time" class="form-control" name="check_out_time" id="add_check_out_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mulai Istirahat</label>
                                <input type="time" class="form-control" name="break_start_time"
                                    id="add_break_start_time">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Selesai Istirahat</label>
                                <input type="time" class="form-control" name="break_end_time" id="add_break_end_time">
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
                                    required>
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
                                <input type="time" class="form-control" name="check_out_time" id="edit_check_out_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mulai Istirahat</label>
                                <input type="time" class="form-control" name="break_start_time"
                                    id="edit_break_start_time">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Selesai Istirahat</label>
                                <input type="time" class="form-control" name="break_end_time" id="edit_break_end_time">
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
                                    <option value="on_break">Sedang Istirahat</option>
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

    // Handle status change in Add form
    $('#add_status').on('change', function() {
        handleStatusChange('add');
    });

    // Handle status change in Edit form
    $('#edit_status').on('change', function() {
        handleStatusChange('edit');
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
                let breakTime = '';
                if (attendance.break_start) {
                    breakTime = attendance.break_start;
                    if (attendance.break_end) {
                        breakTime += ' - ' + attendance.break_end;
                    }
                }

                let statusBadge = '';
                switch(attendance.status) {
                    case 'checked_in':
                        statusBadge = '<span class="badge badge-success">Sedang Bekerja</span>';
                        break;
                    case 'checked_out':
                        statusBadge = '<span class="badge badge-primary">Sudah Pulang</span>';
                        break;
                    case 'on_break':
                        statusBadge = '<span class="badge badge-warning">Sedang Istirahat</span>';
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
                    new Date(attendance.check_in).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}),
                    attendance.check_out ? new Date(attendance.check_out).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '-',
                    breakTime || '-',
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

        // Validate required fields based on status
        const status = $('#add_status').val();
        if (status === 'checked_out') {
            const checkOutDate = $('#add_check_out_date').val();
            const checkOutTime = $('#add_check_out_time').val();

            if (!checkOutDate || !checkOutTime) {
                showToast('error', 'Tanggal Keluar dan Jam Keluar harus diisi ketika status "Sudah Pulang"');
                return;
            }
        }

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

        $.get(`/absensi/${id}/edit`)
        .done(function(attendance) {
            $('#edit_user_name').val(attendance.employee.name);
            $('#edit_warehouse_name').val(attendance.warehouse ? attendance.warehouse.name : '-');

            let checkInDate = new Date(attendance.check_in);
            $('#edit_check_in_date').val(checkInDate.toISOString().split('T')[0]);
            $('#edit_check_in_time').val(checkInDate.toTimeString().split(' ')[0].substring(0, 5));

            if (attendance.check_out) {
                let checkOutDate = new Date(attendance.check_out);
                $('#edit_check_out_date').val(checkOutDate.toISOString().split('T')[0]);
                $('#edit_check_out_time').val(checkOutDate.toTimeString().split(' ')[0].substring(0, 5));
            } else {
                $('#edit_check_out_date').val('');
                $('#edit_check_out_time').val('');
            }

            // Handle break times as simple time strings
            $('#edit_break_start_time').val(attendance.break_start || '');
            $('#edit_break_end_time').val(attendance.break_end || '');

            $('#edit_status').val(attendance.status);
            $('#edit_notes').val(attendance.notes || '');

            $('#editAttendanceForm').data('id', id);
            $('#editAttendanceModal').modal('show');

            // Initialize status-based validation for edit form
            handleStatusChange('edit');
        });
    });

    // Edit form submit
    $('#editAttendanceForm').on('submit', function(e) {
        e.preventDefault();

        // Validate required fields based on status
        const status = $('#edit_status').val();
        if (status === 'checked_out') {
            const checkOutDate = $('#edit_check_out_date').val();
            const checkOutTime = $('#edit_check_out_time').val();

            if (!checkOutDate || !checkOutTime) {
                showToast('error', 'Tanggal Keluar dan Jam Keluar harus diisi ketika status "Sudah Pulang"');
                return;
            }
        }

        let id = $(this).data('id');

        $.ajax({
            url: `/absensi/${id}`,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                ...Object.fromEntries(new FormData(this))
            }
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

        Swal.fire({
            title: 'Hapus Data Absensi?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/absensi/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        loadAttendanceData();
                    } else {
                        showToast('error', response.message);
                    }
                })
                .fail(function() {
                    showToast('error', 'Terjadi kesalahan sistem');
                });
            }
        });
    });

    function handleStatusChange(formType) {
        const status = $('#' + formType + '_status').val();
        const checkOutDateField = $('#' + formType + '_check_out_date');
        const checkOutTimeField = $('#' + formType + '_check_out_time');
        const checkOutDateLabel = $('label[for="' + formType + '_check_out_date"]');
        const checkOutTimeLabel = $('label[for="' + formType + '_check_out_time"]');

        if (status === 'checked_out') {
            // Make check out fields required
            checkOutDateField.prop('required', true);
            checkOutTimeField.prop('required', true);

            // Add required indicator to labels
            if (!checkOutDateLabel.find('.text-danger').length) {
                checkOutDateLabel.append(' <span class="text-danger">*</span>');
            }
            if (!checkOutTimeLabel.find('.text-danger').length) {
                checkOutTimeLabel.append(' <span class="text-danger">*</span>');
            }

            // Set current date and time if fields are empty
            if (!checkOutDateField.val()) {
                checkOutDateField.val(new Date().toISOString().split('T')[0]);
            }
            if (!checkOutTimeField.val()) {
                const now = new Date();
                const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                                 now.getMinutes().toString().padStart(2, '0');
                checkOutTimeField.val(timeString);
            }
        } else {
            // Remove required attribute
            checkOutDateField.prop('required', false);
            checkOutTimeField.prop('required', false);

            // Remove required indicator from labels
            checkOutDateLabel.find('.text-danger').remove();
            checkOutTimeLabel.find('.text-danger').remove();
        }
    }

    function showToast(type, message) {
        toastr[type](message);
    }
});
</script>
@endpush
