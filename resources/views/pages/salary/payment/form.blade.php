@extends('layouts.dashboard')

@section('title', isset($gaji) ? 'Edit Pembayaran Gaji' : 'Tambah Pembayaran Gaji')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="pt-6 border-0 card-header">
        <!--begin::Card title-->
        <div class="card-title">
            <h2 class="fw-bold">{{ isset($gaji) ? 'Edit Pembayaran Gaji' : 'Tambah Pembayaran Gaji' }}</h2>
        </div>
        <!--end::Card title-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="py-4 card-body">
        <!--begin::Alert-->
        <div class="p-5 mb-10 alert alert-info d-flex align-items-center">
            <!--begin::Icon-->
            <span class="svg-icon svg-icon-2hx svg-icon-info me-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path opacity="0.3"
                        d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48613 15.8404 4.4407 16.8889C5.26474 17.8069 6.33444 18.6696 7.51648 19.477C8.8037 20.3602 10.1799 21.1849 11.5164 21.7864C11.8246 21.9287 12.1754 21.9287 12.4837 21.7864C13.8201 21.1849 15.1963 20.3602 16.4835 19.477C17.6656 18.6696 18.7353 17.8069 19.5593 16.8889C20.5139 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8188 4.45258 20.5543 4.37824Z"
                        fill="currentColor" />
                    <path
                        d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8055 13.6747 11.451 13.6747 11.8383 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z"
                        fill="currentColor" />
                </svg>
            </span>
            <!--end::Icon-->
            <!--begin::Wrapper-->
            <div class="d-flex flex-column">
                <!--begin::Title-->
                <h4 class="mb-1 text-info">Informasi Perhitungan Gaji</h4>
                <!--end::Title-->
                <!--begin::Content-->
                <span>Gaji akan dihitung berdasarkan:</span>
                <ul class="mb-0">
                    <li>Kehadiran karyawan pada periode yang dipilih</li>
                    <li>Gaji dasar (harian/bulanan) dari pengaturan gaji</li>
                    <li>Potongan kasbon yang dipilih</li>
                </ul>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Alert-->

        <!--begin::Form-->
        <form id="salary_form" class="form"
            action="{{ isset($gaji) ? route('gaji.update', $gaji) : route('gaji.store') }}" method="POST">
            @csrf
            @if(isset($gaji))
            @method('PUT')
            @endif

            <!--begin::Input group-->
            <div class="mb-7 fv-row">
                <!--begin::Label-->
                <label class="mb-2 required fw-semibold fs-6">Karyawan</label>
                <!--end::Label-->
                <!--begin::Input-->
                <select name="employee_id" class="form-select form-select-solid" data-control="select2"
                    data-placeholder="Pilih Karyawan">
                    <option></option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (isset($gaji) && $gaji->employee_id == $employee->id) ||
                        old('employee_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                        @if($employee->salarySetting)
                        - ({{ $employee->salarySetting->monthly_salary > 0 ? 'Gaji Bulanan: Rp ' .
                        number_format($employee->salarySetting->monthly_salary, 0, ',', '.') : 'Gaji Harian: Rp ' .
                        number_format($employee->salarySetting->daily_salary, 0, ',', '.') }})
                        @else
                        - (Belum ada setting gaji)
                        @endif
                    </option>
                    @endforeach
                </select>
                <!--end::Input-->
                @error('employee_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="mb-7 fv-row">
                <!--begin::Label-->
                <label class="mb-2 required fw-semibold fs-6">Gudang</label>
                <!--end::Label-->
                <!--begin::Input-->
                <select name="warehouse_id" class="form-select form-select-solid" data-control="select2"
                    data-placeholder="Pilih Gudang">
                    <option></option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ (isset($gaji) && $gaji->warehouse_id == $warehouse->id) ||
                        old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                    @endforeach
                </select>
                <!--end::Input-->
                @error('warehouse_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="mb-7 row">
                <div class="col-md-6">
                    <label class="mb-2 required fw-semibold fs-6">Tanggal Mulai Periode</label>
                    <input type="date" name="period_start" class="form-control form-control-solid"
                        value="{{ isset($gaji) ? $gaji->period_start->format('Y-m-d') : old('period_start', date('Y-m-01')) }}"
                        required />
                    @error('period_start')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="mb-2 required fw-semibold fs-6">Tanggal Akhir Periode</label>
                    <input type="date" name="period_end" class="form-control form-control-solid"
                        value="{{ isset($gaji) ? $gaji->period_end->format('Y-m-d') : old('period_end', date('Y-m-t')) }}"
                        required />
                    @error('period_end')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="mb-7 fv-row">
                <label class="mb-2 fw-semibold fs-6">Potongan Kasbon</label>
                <div id="cash_advance_list" class="p-4 rounded border">
                    <div class="mb-3 text-muted">Pilih kasbon yang akan dipotong dari gaji periode ini:</div>
                    <div id="cash_advance_items">
                        <!-- Cash advance items will be loaded here dynamically -->
                        <div class="py-5 text-center" id="loading_cash_advances" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="py-5 text-center text-muted" id="no_cash_advances">
                            Pilih karyawan dan periode untuk melihat daftar kasbon
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="mb-7 fv-row">
                <label class="mb-2 fw-semibold fs-6">Catatan</label>
                <textarea name="notes" class="form-control form-control-solid" rows="3"
                    placeholder="Catatan tambahan (opsional)">{{ isset($gaji) ? $gaji->notes : old('notes') }}</textarea>
                @error('notes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <!--end::Input group-->

            <!--begin::Actions-->
            <div class="pt-10 text-center">
                <a href="{{ route('gaji.index') }}" class="btn btn-light me-3">Kembali</a>
                <button type="submit" class="btn btn-primary" id="submit_btn">
                    <span class="indicator-label">
                        {{ isset($gaji) ? 'Update & Hitung Ulang' : 'Simpan & Hitung' }}
                    </span>
                    <span class="indicator-progress">
                        Please wait... <span class="align-middle spinner-border spinner-border-sm ms-2"></span>
                    </span>
                </button>
            </div>
            <!--end::Actions-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
@endsection

@push('addon-script')


<script>
    "use strict";

    // Set up CSRF token for all AJAX requests
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }

    // Class definition
    var KTSalaryForm = function () {
        // Elements
        var form;
        var submitButton;
        var validator;

        // Load cash advances for selected employee and period
        function loadCashAdvances() {
            const employeeId = $('select[name="employee_id"]').val();
            const periodStart = $('input[name="period_start"]').val();
            const periodEnd = $('input[name="period_end"]').val();

            console.log('loadCashAdvances called with:', { employeeId, periodStart, periodEnd });

            if (!employeeId || !periodStart || !periodEnd) {
                console.log('Missing required fields');
                return;
            }

            // Get existing cash advance IDs for editing
            @if(isset($gaji) && $gaji->cash_advance_ids)
            const existingCashAdvanceIds = @json($gaji->cash_advance_ids);
            @else
            const existingCashAdvanceIds = [];
            @endif

            $('#no_cash_advances').hide();
            $('#loading_cash_advances').show();

            $.ajax({
                url: window.location.origin + '/cash-advances/api/data',
                method: 'GET',
                data: {
                    employee_id: employeeId,
                    period_start: periodStart,
                    period_end: periodEnd
                },
                beforeSend: function(xhr) {
                    console.log('Making AJAX request to:', window.location.origin + '/cash-advances/api/data');
                    console.log('Request headers:', xhr.getAllResponseHeaders());
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    $('#loading_cash_advances').hide();

                    if (!response.data || response.data.length === 0) {
                        $('#no_cash_advances').text('Tidak ada kasbon yang tersedia').show();
                        return;
                    }

                    let html = '';
                    response.data.forEach(function(item) {
                        const amountFormatted = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(item.amount);

                        let statusBadge = '';
                        if (item.is_overdue) {
                            statusBadge = '<span class="badge badge-danger ms-2">Terlambat</span>';
                        }

                        // Check if this item was previously selected (only for edit mode)
                        const isSelected = existingCashAdvanceIds.includes(item.id);

                        html += `
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="cash_advance_ids[]"
                                    value="${item.id}"
                                    id="cash_advance_${item.id}"
                                    ${isSelected ? 'checked' : ''}>
                                <label class="form-check-label" for="cash_advance_${item.id}">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">
                                            ${item.type === 'direct' ? 'Kasbon Langsung' : 'Cicilan ke-' + item.installment_number}:
                                            ${amountFormatted}
                                        </span>
                                        ${statusBadge}
                                    </div>
                                    <small class="text-muted d-block">
                                        ${item.type === 'direct' ?
                                            'Tanggal Pinjam: ' + item.advance_date :
                                            'Jatuh Tempo: ' + item.due_date}
                                    </small>
                                </label>
                            </div>
                        `;
                    });

                    $('#cash_advance_items').html(html);
                },
                error: function(xhr, status, error) {
                    $('#loading_cash_advances').hide();
                    let errorMessage = 'Gagal memuat data kasbon';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    } else if (xhr.status === 401) {
                        errorMessage += ': Silakan login ulang';
                    } else if (xhr.status === 403) {
                        errorMessage += ': Anda tidak memiliki akses';
                    }
                    $('#no_cash_advances').text(errorMessage).show();
                }
            });
        }

        // Public functions
        return {
            init: function () {

                form = document.querySelector('#salary_form');
                submitButton = document.querySelector('#submit_btn');

                // Handle employee change
                $('select[name="employee_id"]').on('change', function() {
                    loadCashAdvances();
                });

                // Handle period changes
                $('input[name="period_start"], input[name="period_end"]').on('change', function() {
                    loadCashAdvances();
                });

                // Load cash advances on page load if editing
                const selectedEmployee = $('select[name="employee_id"]').val();
                if (selectedEmployee) {
                    loadCashAdvances();
                }
            }
        };
    }();

    // On document ready
    $(document).ready(function() {
        KTSalaryForm.init();
    });

    // Also try with KTUtil
    KTUtil.onDOMContentLoaded(function () {
        KTSalaryForm.init();
    });
</script>
@endpush