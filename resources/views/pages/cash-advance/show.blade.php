@extends('layouts.dashboard')

@section('title', 'Detail Kasbon')
@section('menu-title', 'Detail Kasbon')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@section('content')

<div class="row g-5">
    <!-- Cash Advance Details -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Kasbon</h3>
                <div class="card-toolbar">
                    <a href="{{ route('kasbon.index') }}" class="btn btn-light">
                        <i class="ki-duotone ki-arrow-left fs-2"></i>
                        Kembali
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">No. Kasbon</span>
                            <span class="fs-5 fw-bold">{{ $cashAdvance->advance_number }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Status</span>
                            @if($cashAdvance->status == 'pending')
                            <span class="badge badge-warning fs-6">Pending</span>
                            @elseif($cashAdvance->status == 'approved')
                            <span class="badge badge-success fs-6">Disetujui</span>
                            @elseif($cashAdvance->status == 'rejected')
                            <span class="badge badge-danger fs-6">Ditolak</span>
                            @elseif($cashAdvance->status == 'completed')
                            <span class="badge badge-info fs-6">Selesai</span>
                            @elseif($cashAdvance->status == 'cancelled')
                            <span class="badge badge-secondary fs-6">Dibatalkan</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Karyawan</span>
                            <span class="fs-6 fw-bold">{{ $cashAdvance->employee->name ?? 'Data karyawan tidak
                                ditemukan' }}</span>
                            @if($cashAdvance->employee && $cashAdvance->employee->user)
                            <small class="text-muted">{{ $cashAdvance->employee->user->email }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Cabang</span>
                            <span class="fs-6 fw-bold">{{ $cashAdvance->warehouse->name }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Jumlah Kasbon</span>
                            <span class="fs-5 fw-bold text-primary">
                                {{ 'Rp ' . number_format($cashAdvance->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Tanggal Kasbon</span>
                            <span class="fs-6 fw-bold">{{ $cashAdvance->advance_date->format('d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Tipe Pembayaran</span>
                            @if($cashAdvance->type == 'direct')
                            <span class="badge badge-primary fs-6">Langsung</span>
                            @else
                            <span class="badge badge-info fs-6">Cicilan ({{ $cashAdvance->installment_count }}x)</span>
                            @endif
                        </div>
                    </div>
                    @if($cashAdvance->isInstallment())
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Jumlah per Cicilan</span>
                            <span class="fs-6 fw-bold">
                                {{ 'Rp ' . number_format($cashAdvance->installment_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>

                @if($cashAdvance->description)
                <div class="mb-7 d-flex flex-column">
                    <span class="mb-1 text-muted fw-semibold">Keterangan</span>
                    <span class="fs-6">{{ $cashAdvance->description }}</span>
                </div>
                @endif

                <!-- Approval Info -->
                @if($cashAdvance->approved_by)
                <div class="my-7 separator"></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Diproses oleh</span>
                            <span class="fs-6 fw-bold">{{ $cashAdvance->approvedBy->name }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-7 d-flex flex-column">
                            <span class="mb-1 text-muted fw-semibold">Tanggal Diproses</span>
                            <span class="fs-6 fw-bold">{{ $cashAdvance->approved_at->format('d F Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                @if($cashAdvance->status == 'rejected' && $cashAdvance->rejection_reason)
                <div class="mb-7 d-flex flex-column">
                    <span class="mb-1 text-muted fw-semibold">Alasan Penolakan</span>
                    <div class="alert alert-danger">
                        {{ $cashAdvance->rejection_reason }}
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Progress -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progress Pembayaran</h3>
            </div>
            <div class="card-body">
                @if($cashAdvance->isInstallment())
                <div class="mb-5">
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="fw-semibold">Progress</span>
                        <span class="fw-bold">{{ number_format($cashAdvance->getProgressPercentage(), 1) }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                            style="width: {{ $cashAdvance->getProgressPercentage() }}%"
                            aria-valuenow="{{ $cashAdvance->getProgressPercentage() }}" aria-valuemin="0"
                            aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <div class="mb-5 row">
                    <div class="col-6">
                        <div class="text-center">
                            <span class="text-muted fw-semibold">Terbayar</span>
                            <div class="fs-6 fw-bold text-success">
                                {{ 'Rp ' . number_format($cashAdvance->paid_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <span class="text-muted fw-semibold">Sisa</span>
                            <div class="fs-6 fw-bold text-warning">
                                {{ 'Rp ' . number_format($cashAdvance->remaining_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center">
                    @if($cashAdvance->status == 'approved')
                    <div class="mb-3">
                        <i class="ki-duotone ki-check-circle fs-3x text-success">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <span class="fw-bold text-success">Kasbon Langsung Diselesaikan</span>
                    @else
                    <span class="text-muted">Menunggu persetujuan</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        @if($cashAdvance->status == 'pending')
        <div class="mt-5 card">
            <div class="card-header">
                <h3 class="card-title">Aksi</h3>
            </div>
            <div class="card-body">
                @can('update kasbon')
                <a href="{{ route('kasbon.edit', $cashAdvance->id) }}" class="mb-3 btn btn-warning w-100">
                    <i class="ki-duotone ki-pencil fs-2"></i>
                    Edit Kasbon
                </a>
                @endcan

                @can('approve kasbon')
                <button type="button" class="mb-3 btn btn-success w-100"
                    onclick="showApprovalModal('{{ $cashAdvance->id }}', 'approve')">
                    <i class="ki-duotone ki-check fs-2"></i>
                    Setujui Kasbon
                </button>

                <button type="button" class="mb-3 btn btn-danger w-100"
                    onclick="showApprovalModal('{{ $cashAdvance->id }}', 'reject')">
                    <i class="ki-duotone ki-cross fs-2"></i>
                    Tolak Kasbon
                </button>
                @endcan

                @can('hapus kasbon')
                <button type="button" class="btn btn-danger w-100"
                    onclick="deleteCashAdvance('{{ $cashAdvance->id }}')">
                    <i class="ki-duotone ki-trash fs-2"></i>
                    Hapus Kasbon
                </button>
                @endcan
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Installment Payments Table -->
@if($cashAdvance->isInstallment() && $cashAdvance->payments->count() > 0)
<div class="mt-8 card">
    <div class="card-header">
        <h3 class="card-title">Jadwal Cicilan</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                        <th>Cicilan Ke-</th>
                        <th>Jumlah</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                        <th>Diproses oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashAdvance->payments as $payment)
                    <tr>
                        <td class="fw-bold">{{ $payment->installment_number }}</td>
                        <td>{{ 'Rp ' . number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($payment->due_date)
                            {{ $payment->due_date->format('d/m/Y') }}
                            @if($payment->isOverdue())
                            <span class="badge badge-danger ms-2">Terlambat</span>
                            @endif
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($payment->status == 'paid')
                            <span class="badge badge-success">Lunas</span>
                            @elseif($payment->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                            @elseif($payment->status == 'overdue')
                            <span class="badge badge-danger">Terlambat</span>
                            @endif
                        </td>
                        <td>
                            {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            {{ $payment->processedBy ? $payment->processedBy->name : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

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
<script>
    "use strict";

    $(document).ready(function() {
        // Approval/Rejection handlers
        $('#approveBtn').click(function() {
            var cashAdvanceId = $(this).data('id');
            handleApproval(cashAdvanceId, 'approve');
        });

        $('#rejectBtn').click(function() {
            var cashAdvanceId = $(this).data('id');
            var rejectionReason = $('#rejectionReason').val().trim();

            if (!rejectionReason) {
                // Show SweetAlert validation message
                Swal.fire({
                    text: 'Alasan penolakan harus diisi',
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, mengerti!",
                    customClass: {
                        confirmButton: "btn btn-warning"
                    }
                });
                return;
            }

            handleApproval(cashAdvanceId, 'reject', rejectionReason);
        });
    });

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
                    alert(response.message);
                    location.reload();
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
                        alert(response.message);
                        window.location.href = "{{ route('kasbon.index') }}";
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