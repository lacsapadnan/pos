@extends('layouts.dashboard')

@section('title', 'Rekap Absensi')
@section('menu-title', 'Rekap Absensi')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <h3 class="m-0">Rekap Absensi</h3>
            </div>
            <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-down fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Export Data
                </button>
                <!--begin::Menu-->
                <div id="kt_datatable_example_export_menu"
                    class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px"
                    data-kt-menu="true">
                    <div class="px-3 menu-item">
                        <a href="#" class="px-3 menu-link" data-kt-export="copy">
                            Copy to clipboard
                        </a>
                    </div>
                    <div class="px-3 menu-item">
                        <a href="#" class="px-3 menu-link" data-kt-export="excel">
                            Export as Excel
                        </a>
                    </div>
                    <div class="px-3 menu-item">
                        <a href="#" class="px-3 menu-link" data-kt-export="csv">
                            Export as CSV
                        </a>
                    </div>
                    <div class="px-3 menu-item">
                        <a href="#" class="px-3 menu-link" data-kt-export="pdf">
                            Export as PDF
                        </a>
                    </div>
                </div>
                <div id="kt_datatable_example_buttons" class="d-none"></div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="mb-5 row">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai:</label>
                    <input type="date" class="form-control" id="from_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai:</label>
                    <input type="date" class="form-control" id="to_date" value="{{ date('Y-m-d') }}">
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
                    <select class="form-select" id="user_id">
                        <option value="">Semua Karyawan</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3 col-md-12">
                    <button type="button" class="btn btn-primary" id="filter-btn">
                        <i class="ki-duotone ki-funnel fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Filter Data
                    </button>
                    <button type="button" class="btn btn-secondary" id="reset-btn">
                        <i class="ki-duotone ki-arrows-circle fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Reset Filter
                    </button>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
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
                                <label class="form-label">Tanggal Keluar</label>
                                <input type="date" class="form-control" name="check_out_date" id="edit_check_out_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Keluar</label>
                                <input type="time" class="form-control" name="check_out_time" id="edit_check_out_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mulai Istirahat</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="break_start_date"
                                            id="edit_break_start_date">
                                    </div>
                                    <div class="col-6">
                                        <input type="time" class="form-control" name="break_start_time"
                                            id="edit_break_start_time">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Selesai Istirahat</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="break_end_date"
                                            id="edit_break_end_date">
                                    </div>
                                    <div class="col-6">
                                        <input type="time" class="form-control" name="break_end_time"
                                            id="edit_break_end_time">
                                    </div>
                                </div>
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
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    $(document).ready(function() {
    let table;
    let currentEditId = null;

    // Initialize DataTable
    initDataTable();

    // Load initial data
    loadAttendanceData();

    function initExportButtons() {
        const buttonsInstance = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: 'Rekap Absensi'
                },
                {
                    extend: 'excelHtml5',
                    title: 'Rekap Absensi'
                },
                {
                    extend: 'csvHtml5',
                    title: 'Rekap Absensi'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Rekap Absensi'
                }
            ]
        }).container().appendTo($('#kt_datatable_example_buttons'));

        // Hook export menu clicks
        const exportMenuButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
        exportMenuButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);
                target.click();
            });
        });
    }

    $('#filter-btn').click(function() {
        loadAttendanceData();
    });

    $('#reset-btn').click(function() {
        $('#from_date').val('{{ date("Y-m-d") }}');
        $('#to_date').val('{{ date("Y-m-d") }}');
        $('#warehouse').val('');
        $('#user_id').val('');
        loadAttendanceData();
    });

    // Edit form submission
    $('#editAttendanceForm').on('submit', function(e) {
        e.preventDefault();
        updateAttendance();
    });

    function initDataTable() {
        table = $('#attendance-table').DataTable({
            responsive: true,
            processing: true,
            dom: 'Bfrtip',
            buttons: [], // Initialize empty buttons array
            language: {
                processing: '<span class="fa-stack fa-lg">\n\
                    <i class="fa fa-spinner fa-spin fa-stack-2x fa-fw"></i>\n\
               </span>&emsp;Processing ...',
                searchPlaceholder: "Cari data...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                loadingRecords: "Loading...",
                zeroRecords: "Tidak ada data yang ditemukan",
                emptyTable: "Tidak ada data yang tersedia",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            },
            columns: [
                { data: null, orderable: false, searchable: false },
                { data: 'check_in' },
                { data: 'employee.name' },
                @if(auth()->user()->hasRole('master'))
                { data: 'warehouse.name' },
                @endif
                { data: 'check_in' },
                { data: 'check_out' },
                { data: null },
                { data: 'total_hours' },
                { data: 'status' },
                { data: null, orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    targets: 1,
                    render: function(data, type, row) {
                        return moment(data).format('DD/MM/YYYY');
                    }
                },
                {
                    targets: @if(auth()->user()->hasRole('master')) 4 @else 3 @endif,
                    render: function(data, type, row) {
                        return data ? moment(data).format('HH:mm') : '-';
                    }
                },
                {
                    targets: @if(auth()->user()->hasRole('master')) 5 @else 4 @endif,
                    render: function(data, type, row) {
                        return data ? moment(data).format('HH:mm') : '-';
                    }
                },
                {
                    targets: @if(auth()->user()->hasRole('master')) 6 @else 5 @endif,
                    render: function(data, type, row) {
                        if (row.break_start && row.break_end) {
                            let start = moment(row.break_start).format('HH:mm');
                            let end = moment(row.break_end).format('HH:mm');
                            return start + ' - ' + end;
                        } else if (row.break_start) {
                            return moment(row.break_start).format('HH:mm') + ' - ...';
                        }
                        return '-';
                    }
                },
                {
                    targets: @if(auth()->user()->hasRole('master')) 7 @else 6 @endif,
                    render: function(data, type, row) {
                        return data + ' jam';
                    }
                },
                {
                    targets: @if(auth()->user()->hasRole('master')) 8 @else 7 @endif,
                    render: function(data, type, row) {
                        let badgeClass = '';
                        let statusText = '';

                        switch(data) {
                            case 'checked_in':
                                badgeClass = 'badge-success';
                                statusText = 'Sedang Bekerja';
                                break;
                            case 'checked_out':
                                badgeClass = 'badge-secondary';
                                statusText = 'Sudah Pulang';
                                break;
                            case 'on_break':
                                badgeClass = 'badge-warning';
                                statusText = 'Sedang Istirahat';
                                break;
                            default:
                                badgeClass = 'badge-danger';
                                statusText = 'Unknown';
                        }

                        return '<span class="badge' + badgeClass + '">' + statusText + '</span>';
                    }
                },
                {
                    targets: @if(auth()->user()->hasRole('master')) 9 @else 8 @endif,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary btn-edit" data-id="${row.id}">
                                    <i class="ki-duotone ki-pencil fs-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                                    <i class="ki-duotone ki-trash fs-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                    </i>
                                </button>
                            </div>
                        `;
                    }
                }
            ]
        });

        // Initialize export buttons after table is created
        initExportButtons();

        // Handle edit button click
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            editAttendance(id);
        });

        // Handle delete button click
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            deleteAttendance(id);
        });
    }

    function loadAttendanceData() {
        let params = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            warehouse: $('#warehouse').val(),
            user_id: $('#user_id').val()
        };

        $.get('/absensi/data', params)
        .done(function(response) {
            table.clear();
            table.rows.add(response);
            table.draw();
        })
        .fail(function() {
            toastr.error('Gagal memuat data absensi');
        });
    }

    function editAttendance(id) {
        $.get('/absensi/' + id + '/edit')
        .done(function(response) {
            currentEditId = id;

            // Populate form
            $('#edit_user_name').val(response.user.name);
            $('#edit_warehouse_name').val(response.warehouse.name);

            if (response.check_in) {
                const checkIn = moment(response.check_in);
                $('#edit_check_in_date').val(checkIn.format('YYYY-MM-DD'));
                $('#edit_check_in_time').val(checkIn.format('HH:mm'));
            }

            if (response.check_out) {
                const checkOut = moment(response.check_out);
                $('#edit_check_out_date').val(checkOut.format('YYYY-MM-DD'));
                $('#edit_check_out_time').val(checkOut.format('HH:mm'));
            } else {
                $('#edit_check_out_date').val('');
                $('#edit_check_out_time').val('');
            }

            if (response.break_start) {
                const breakStart = moment(response.break_start);
                $('#edit_break_start_date').val(breakStart.format('YYYY-MM-DD'));
                $('#edit_break_start_time').val(breakStart.format('HH:mm'));
            } else {
                $('#edit_break_start_date').val('');
                $('#edit_break_start_time').val('');
            }

            if (response.break_end) {
                const breakEnd = moment(response.break_end);
                $('#edit_break_end_date').val(breakEnd.format('YYYY-MM-DD'));
                $('#edit_break_end_time').val(breakEnd.format('HH:mm'));
            } else {
                $('#edit_break_end_date').val('');
                $('#edit_break_end_time').val('');
            }

            $('#edit_status').val(response.status);
            $('#edit_notes').val(response.notes || '');

            $('#editAttendanceModal').modal('show');
        })
        .fail(function() {
            toastr.error('Gagal memuat data untuk diedit');
        });
    }

    function updateAttendance() {
        const formData = new FormData($('#editAttendanceForm')[0]);

        $.ajax({
            url: '/absensi/' + currentEditId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .done(function(response) {
            if (response.success) {
                $('#editAttendanceModal').modal('hide');
                loadAttendanceData();
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        })
        .fail(function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = '';
                for (const field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                toastr.error(errorMessage);
            } else {
                toastr.error(xhr.responseJSON.message || 'Gagal memperbarui data');
            }
        });
    }

    function deleteAttendance(id) {
        Swal.fire({
            text: "Apakah Anda yakin ingin menghapus data absensi ini?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Tidak, batal",
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/absensi/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        loadAttendanceData();
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "OK",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    } else {
                        Swal.fire({
                            text: response.message,
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "OK",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                })
                .fail(function(xhr) {
                    Swal.fire({
                        text: xhr.responseJSON.message || 'Gagal menghapus data',
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                });
            }
        });
    }
});
</script>
@endpush