@extends('layouts.dashboard')

@section('title', 'Absensi')
@section('menu-title', 'Absensi')

@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <p class="text-muted">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="text-center card-body">
                            <h4 class="mb-4">Status Absensi</h4>

                            <!-- Check In Button -->
                            <div class="mb-3" id="check-in-section">
                                <button type="button" class="btn btn-success btn-lg w-100" id="check-in-btn" {{
                                    $todayAttendance ? 'disabled' : '' }}>
                                    <i class="ki-duotone ki-entrance-right fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Absen Masuk
                                </button>
                            </div>

                            <!-- Check Out Button -->
                            <div class="mb-3" id="check-out-section"
                                style="{{ !$todayAttendance || $todayAttendance->check_out ? 'display: none;' : '' }}">
                                <button type="button" class="btn btn-danger btn-lg w-100" id="check-out-btn">
                                    <i class="ki-duotone ki-entrance-left fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Absen Keluar
                                </button>
                                <small class="mt-2 text-muted d-block">
                                    Minimal durasi kerja: 1 jam
                                </small>
                            </div>

                            <!-- Break Buttons -->
                            <div class="row" id="break-section"
                                style="{{ !$todayAttendance || $todayAttendance->check_out ? 'display: none;' : '' }}">
                                <div class="col-6">
                                    <button type="button" class="btn btn-warning w-100" id="start-break-btn">
                                        <i class="ki-duotone ki-time fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Mulai Istirahat
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-info w-100" id="end-break-btn"
                                        style="display: none;">
                                        <i class="ki-duotone ki-time fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Selesai Istirahat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-primary">
                        <div class="text-white card-body">
                            <h4 class="mb-4 text-white">Informasi Hari Ini</h4>

                            <div class="mb-3">
                                <label class="text-white-75">Jam Masuk:</label>
                                <div class="fw-bold fs-4" id="check-in-time">
                                    {{ $todayAttendance && $todayAttendance->check_in ?
                                    $todayAttendance->check_in->format('H:i') : '-' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-white-75">Jam Keluar:</label>
                                <div class="fw-bold fs-4" id="check-out-time">
                                    {{ $todayAttendance && $todayAttendance->check_out ?
                                    $todayAttendance->check_out->format('H:i') : '-' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-white-75">Status:</label>
                                <div class="fw-bold fs-4" id="status">
                                    @if($todayAttendance)
                                    @if($todayAttendance->status === 'checked_out')
                                    Sudah Pulang
                                    @elseif($todayAttendance->status === 'on_break')
                                    Sedang Istirahat
                                    @else
                                    Sedang Bekerja
                                    @endif
                                    @else
                                    Belum Absen
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-white-75">Total Jam Kerja:</label>
                                <div class="fw-bold fs-4" id="total-hours">
                                    {{ $todayAttendance ? $todayAttendance->getTotalWorkHours() : 0 }} jam
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script>
    $(document).ready(function() {
    let todayAttendance = @json($todayAttendance);

    // Update UI based on current status
    updateAttendanceUI();

    // Auto refresh status every 30 seconds
    setInterval(function() {
        refreshAttendanceStatus();
    }, 30000);

    $('#check-in-btn').click(function() {
        if ($(this).prop('disabled')) return;

        $.post('/absensi/check-in', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                todayAttendance = response.attendance;
                updateAttendanceUI();
                showToast('success', response.message);
            } else {
                showToast('error', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Terjadi kesalahan sistem');
        });
    });

    $('#check-out-btn').click(function() {
        $.post('/absensi/check-out', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                todayAttendance = response.attendance;
                updateAttendanceUI();
                showToast('success', response.message);
            } else {
                showToast('error', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Terjadi kesalahan sistem');
        });
    });

    $('#start-break-btn').click(function() {
        $.post('/absensi/start-break', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                todayAttendance = response.attendance;
                updateAttendanceUI();
                showToast('success', response.message);
            } else {
                showToast('error', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Terjadi kesalahan sistem');
        });
    });

    $('#end-break-btn').click(function() {
        $.post('/absensi/end-break', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                todayAttendance = response.attendance;
                updateAttendanceUI();
                showToast('success', response.message);
            } else {
                showToast('error', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Terjadi kesalahan sistem');
        });
    });

    function updateAttendanceUI() {
        if (todayAttendance) {
            // Update times
            $('#check-in-time').text(todayAttendance.check_in ? moment(todayAttendance.check_in).format('HH:mm') : '-');
            $('#check-out-time').text(todayAttendance.check_out ? moment(todayAttendance.check_out).format('HH:mm') : '-');

            // Update status
            let status = '';
            if (todayAttendance.status === 'checked_out') {
                status = 'Sudah Pulang';
            } else if (todayAttendance.status === 'on_break') {
                status = 'Sedang Istirahat';
            } else {
                status = 'Sedang Bekerja';
            }
            $('#status').text(status);

            // Hide/show sections
            $('#check-in-btn').prop('disabled', true);

            if (todayAttendance.check_out) {
                $('#check-out-section').hide();
                $('#break-section').hide();
            } else {
                $('#check-out-section').show();
                $('#break-section').show();

                // Handle break buttons
                if (todayAttendance.status === 'on_break') {
                    $('#start-break-btn').hide();
                    $('#end-break-btn').show();
                } else {
                    if (todayAttendance.break_start) {
                        $('#start-break-btn').prop('disabled', true).text('Istirahat Sudah Digunakan');
                    }
                    $('#end-break-btn').hide();
                }
            }
        }
    }

    function refreshAttendanceStatus() {
        $.get('/absensi/today-status')
        .done(function(response) {
            todayAttendance = response.attendance;
            updateAttendanceUI();

            // Update total hours
            if (todayAttendance) {
                $('#total-hours').text(calculateWorkHours() + ' jam');
            }
        });
    }

    function calculateWorkHours() {
        if (!todayAttendance || !todayAttendance.check_in) return 0;

        let checkIn = moment(todayAttendance.check_in);
        let checkOut = todayAttendance.check_out ? moment(todayAttendance.check_out) : moment();
        let totalMinutes = checkOut.diff(checkIn, 'minutes');

        // Subtract break time if any
        if (todayAttendance.break_start && todayAttendance.break_end) {
            let breakStart = moment(todayAttendance.break_start);
            let breakEnd = moment(todayAttendance.break_end);
            let breakMinutes = breakEnd.diff(breakStart, 'minutes');
            totalMinutes -= breakMinutes;
        }

        return Math.round((totalMinutes / 60) * 100) / 100;
    }

    function showToast(type, message) {
        toastr[type](message);
    }
});
</script>
@endpush
