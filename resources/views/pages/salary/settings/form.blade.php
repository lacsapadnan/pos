@extends('layouts.dashboard')

@section('title', isset($gaji) ? 'Edit Pembayaran Gaji' : 'Tambah Pembayaran Gaji')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h2 class="fw-bold">{{ isset($gaji) ? 'Edit Pembayaran Gaji' : 'Tambah Pembayaran Gaji' }}</h2>
        </div>
        <!--end::Card title-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Alert-->
        <div class="alert alert-info d-flex align-items-center p-5 mb-10">
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
                <span>Gaji akan dihitung otomatis berdasarkan:</span>
                <ul class="mb-0">
                    <li>Data master gaji karyawan (gaji harian/bulanan)</li>
                    <li>Kehadiran karyawan pada periode yang dipilih</li>
                    <li>Potongan kasbon yang jatuh tempo pada periode tersebut</li>
                </ul>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Alert-->

        <!--begin::Form-->
        <form id="salary_form" class="form"
            action="{{ isset($gaji) ? route('gaji.update', $gaji) : route('gaji.store') }}"
            method="POST">
            @csrf
            @if(isset($gaji))
            @method('PUT')
            @endif

            <!--begin::Input group-->
            <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fw-semibold fs-6 mb-2">Karyawan</label>
                <!--end::Label-->
                <!--begin::Input-->
                <select name="employee_id" class="form-select form-select-solid" data-control="select2"
                    data-placeholder="Pilih Karyawan">
                    <option></option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}"
                        {{ (isset($gaji) && $gaji->employee_id == $employee->id) || old('employee_id') == $employee->id ? 'selected' : '' }}
                        data-daily-salary="{{ $employee->salarySetting->daily_salary ?? 0 }}"
                        data-monthly-salary="{{ $employee->salarySetting->monthly_salary ?? 0 }}">
                        {{ $employee->name }}
                        @if($employee->salarySetting)
                        - ({{ $employee->salarySetting->monthly_salary > 0 ? 'Gaji Bulanan: Rp ' . number_format($employee->salarySetting->monthly_salary, 0, ',', '.') : 'Gaji Harian: Rp ' . number_format($employee->salarySetting->daily_salary, 0, ',', '.') }})
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
            <div class="fv-row mb-7">
                <!--begin::Label-->
                <label class="required fw-semibold fs-6 mb-2">Gudang</label>
                <!--end::Label-->
                <!--begin::Input-->
                <select name="warehouse_id" class="form-select form-select-solid" data-control="select2"
                    data-placeholder="Pilih Gudang">
                    <option></option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ (isset($gaji) && $gaji->warehouse_id == $warehouse->id) || old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
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
            <div class="row mb-7">
                <div class="col-md-6">
                    <label class="required fw-semibold fs-6 mb-2">Tanggal Mulai Periode</label>
                    <input type="date" name="period_start" class="form-control form-control-solid"
                        value="{{ isset($gaji) ? $gaji->period_start->format('Y-m-d') : old('period_start', date('Y-m-01')) }}" required />
                    @error('period_start')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="required fw-semibold fs-6 mb-2">Tanggal Akhir Periode</label>
                    <input type="date" name="period_end" class="form-control form-control-solid"
                        value="{{ isset($gaji) ? $gaji->period_end->format('Y-m-d') : old('period_end', date('Y-m-t')) }}" required />
                    @error('period_end')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Catatan</label>
                <textarea name="notes" class="form-control form-control-solid" rows="3"
                    placeholder="Catatan tambahan (opsional)">{{ isset($gaji) ? $gaji->notes : old('notes') }}</textarea>
                @error('notes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <!--end::Input group-->

            <!--begin::Actions-->
            <div class="text-center pt-10">
                <a href="{{ route('gaji.index') }}" class="btn btn-light me-3">Kembali</a>
                <button type="submit" class="btn btn-primary" id="submit_btn">
                    <span class="indicator-label">
                        {{ isset($gaji) ? 'Update & Hitung Ulang' : 'Simpan & Hitung' }}
                    </span>
                    <span class="indicator-progress">
                        Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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

@push('scripts')
<script>
    "use strict";

    // Class definition
    var KTSalaryForm = function () {
        // Elements
        var form;
        var submitButton;
        var validator;

        // Handle form
        var handleForm = function(e) {
            validator = FormValidation.formValidation(
                form,
                {
                    fields: {
                        'employee_id': {
                            validators: {
                                notEmpty: {
                                    message: 'Karyawan harus dipilih'
                                }
                            }
                        },
                        'warehouse_id': {
                            validators: {
                                notEmpty: {
                                    message: 'Gudang harus dipilih'
                                }
                            }
                        },
                        'daily_salary': {
                            validators: {
                                notEmpty: {
                                    message: 'Gaji harian harus diisi'
                                },
                                numeric: {
                                    message: 'Gaji harian harus berupa angka'
                                },
                                greaterThan: {
                                    min: 0,
                                    message: 'Gaji harian harus lebih besar dari 0'
                                }
                            }
                        },
                        'monthly_salary': {
                            validators: {
                                notEmpty: {
                                    message: 'Gaji bulanan harus diisi'
                                },
                                numeric: {
                                    message: 'Gaji bulanan harus berupa angka'
                                },
                                greaterThan: {
                                    min: 0,
                                    message: 'Gaji bulanan harus lebih besar dari 0'
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: ''
                        })
                    }
                }
            );

            // Handle form submit
            submitButton.addEventListener('click', function (e) {
                e.preventDefault();

                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        form.submit();
                    }
                });
            });

            // Handle period date changes
            const startDate = form.querySelector('[name="period_start"]');
            const endDate = form.querySelector('[name="period_end"]');

            startDate.addEventListener('change', function(e) {
                if (!endDate.value) {
                    // Set end date to last day of the month
                    const date = new Date(e.target.value);
                    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                    endDate.value = lastDay.toISOString().split('T')[0];
                }
            });

            endDate.addEventListener('change', function(e) {
                if (startDate.value && e.target.value < startDate.value) {
                    Swal.fire({
                        text: "Tanggal akhir periode harus setelah tanggal mulai periode",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, mengerti!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    e.target.value = '';
                }
            });
        }

        // Public functions
        return {
            // Initialization
            init: function () {
                form = document.querySelector('#salary_setting_form');
                submitButton = document.querySelector('#submit_btn');

                handleForm();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function () {
        KTSalarySettingForm.init();
    });
</script>
@endpush
