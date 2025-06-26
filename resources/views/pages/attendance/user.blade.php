@extends('layouts.dashboard')

@section('title', 'Status Absensi Saya')
@section('menu-title', 'Status Absensi Saya')

@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3>Status Absensi Hari Ini</h3>
                <p class="text-muted">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="text-center card-body">
                            <h4 class="mb-4">Informasi Absensi</h4>

                            @if(!$todayAttendance)
                            <div class="alert alert-warning">
                                <i class="ki-duotone ki-information fs-1 text-warning">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <h5 class="mt-3">Belum Ada Absensi</h5>
                                <p class="mb-0">Anda belum diabsen hari ini. Silakan hubungi supervisor/admin untuk
                                    absensi.</p>
                            </div>
                            @else
                            <div class="alert alert-success">
                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <h5 class="mt-3">Sudah Diabsen</h5>
                                <p class="mb-0">Data absensi Anda sudah tersedia untuk hari ini.</p>
                            </div>
                            @endif

                            <div class="mt-5">
                                <small class="text-muted">
                                    <i class="ki-duotone ki-information-5 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Absensi dikelola oleh supervisor/admin. Untuk pertanyaan tentang absensi,
                                    silakan hubungi supervisor Anda.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-primary">
                        <div class="text-white card-body">
                            <h4 class="mb-4 text-white">Detail Absensi Hari Ini</h4>

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
                                <label class="text-white-75">Waktu Istirahat:</label>
                                <div class="fw-bold fs-6" id="break-time">
                                    @if($todayAttendance && $todayAttendance->break_start)
                                    {{ $todayAttendance->break_start->format('H:i') }}
                                    @if($todayAttendance->break_end)
                                    - {{ $todayAttendance->break_end->format('H:i') }}
                                    @endif
                                    @else
                                    -
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-white-75">Total Jam Kerja:</label>
                                <div class="fw-bold fs-4" id="total-hours">
                                    {{ $todayAttendance ? $todayAttendance->getTotalWorkHours() : 0 }} jam
                                </div>
                            </div>

                            @if($todayAttendance && $todayAttendance->notes)
                            <div class="mb-3">
                                <label class="text-white-75">Catatan:</label>
                                <div class="fw-bold fs-6">
                                    {{ $todayAttendance->notes }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance History -->
            <div class="mt-8">
                <h4>Riwayat Absensi (7 Hari Terakhir)</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-row-bordered gy-5 gs-7" id="recent-attendance-table">
                        <thead>
                            <tr class="text-gray-800 fw-bold fs-6">
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Istirahat</th>
                                <th>Total Jam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script>
    $(document).ready(function() {
    let table = $('#recent-attendance-table').DataTable({
        responsive: true,
        searching: false,
        paging: false,
        info: false,
        order: [[0, 'desc']]
    });

    // Load recent attendance data
    loadRecentAttendance();

    function loadRecentAttendance() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 7);

        $.get('/absensi/data', {
            from_date: startDate.toISOString().split('T')[0],
            to_date: endDate.toISOString().split('T')[0],
            user_id: {{ auth()->id() }}
        })
        .done(function(data) {
            table.clear();

            data.forEach(function(attendance) {
                let breakTime = '';
                if (attendance.break_start) {
                    breakTime = new Date(attendance.break_start).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                    if (attendance.break_end) {
                        breakTime += ' - ' + new Date(attendance.break_end).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
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

                let row = [
                    new Date(attendance.check_in).toLocaleDateString('id-ID'),
                    new Date(attendance.check_in).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}),
                    attendance.check_out ? new Date(attendance.check_out).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : '-',
                    breakTime || '-',
                    attendance.total_hours + ' jam',
                    statusBadge
                ];

                table.row.add(row);
            });

            table.draw();
        });
    }

    // Auto refresh every 30 seconds
    setInterval(function() {
        loadRecentAttendance();
    }, 30000);
});
</script>
@endpush
