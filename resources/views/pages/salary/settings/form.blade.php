@extends('layouts.dashboard')

@section('title', isset($salarySetting) ? 'Edit Pengaturan Gaji' : 'Tambah Pengaturan Gaji')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="pt-6 border-0 card-header">
        <!--begin::Card title-->
        <div class="card-title">
            <h2 class="fw-bold">{{ isset($salarySetting) ? 'Edit Pengaturan Gaji' : 'Tambah Pengaturan Gaji' }}</h2>
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
                <h4 class="mb-1 text-info">Informasi Pengaturan Gaji</h4>
                <!--end::Title-->
                <!--begin::Content-->
                <span>Pengaturan gaji ini akan digunakan sebagai dasar perhitungan gaji karyawan:</span>
                <ul class="mb-0">
                    <li>Gaji harian digunakan untuk menghitung gaji berdasarkan kehadiran</li>
                    <li>Gaji bulanan digunakan untuk karyawan dengan gaji tetap bulanan</li>
                    <li>Setiap karyawan hanya dapat memiliki satu pengaturan gaji aktif</li>
                </ul>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Alert-->

        <!--begin::Form-->
        <form id="salary_setting_form" class="form"
            action="{{ isset($salarySetting) ? route('salary-settings.update', $salarySetting) : route('salary-settings.store') }}"
            method="POST">
            @csrf
            @if(isset($salarySetting))
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
                    <option value="{{ $employee->id }}" {{ (isset($salarySetting) && $salarySetting->employee_id ==
                        $employee->id) ||
                        old('employee_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
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
                    <option value="{{ $warehouse->id }}" {{ (isset($salarySetting) && $salarySetting->warehouse_id ==
                        $warehouse->id) ||
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
                <div class="col-md-6 fv-row">
                    <label class="mb-2 required fw-semibold fs-6">Gaji Harian</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="formatted_daily_salary" class="form-control form-control-solid"
                            value="{{ isset($salarySetting) ? number_format($salarySetting->daily_salary, 0, ',', '.') : old('daily_salary', 0) }}"
                            required />
                        <input type="hidden" name="daily_salary" id="actual_daily_salary"
                            value="{{ isset($salarySetting) ? $salarySetting->daily_salary : old('daily_salary', 0) }}" />
                    </div>
                    @error('daily_salary')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 fv-row">
                    <label class="mb-2 required fw-semibold fs-6">Gaji Bulanan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="formatted_monthly_salary" class="form-control form-control-solid"
                            value="{{ isset($salarySetting) ? number_format($salarySetting->monthly_salary, 0, ',', '.') : old('monthly_salary', 0) }}"
                            required />
                        <input type="hidden" name="monthly_salary" id="actual_monthly_salary"
                            value="{{ isset($salarySetting) ? $salarySetting->monthly_salary : old('monthly_salary', 0) }}" />
                    </div>
                    @error('monthly_salary')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="mb-7 fv-row">
                <label class="mb-2 fw-semibold fs-6">Catatan</label>
                <textarea name="notes" class="form-control form-control-solid" rows="3"
                    placeholder="Catatan tambahan (opsional)">{{ isset($salarySetting) ? $salarySetting->notes : old('notes') }}</textarea>
                @error('notes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <!--end::Input group-->

            <!--begin::Actions-->
            <div class="pt-10 text-center">
                <a href="{{ route('salary-settings.index') }}" class="btn btn-light me-3">Kembali</a>
                <button type="submit" class="btn btn-primary" id="submit_btn">
                    <span class="indicator-label">
                        {{ isset($salarySetting) ? 'Update' : 'Simpan' }}
                    </span>
                    <span class="indicator-progress">
                        Mohon tunggu... <span class="align-middle spinner-border spinner-border-sm ms-2"></span>
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

    // Class definition
    var KTSalarySettingForm = function () {
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

                // Ensure hidden fields have the correct values before validation
                const dailySalary = $('#formatted_daily_salary').val().replace(/[^\d]/g, '');
                const monthlySalary = $('#formatted_monthly_salary').val().replace(/[^\d]/g, '');

                $('#actual_daily_salary').val(dailySalary || '0');
                $('#actual_monthly_salary').val(monthlySalary || '0');

                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        form.submit();
                    }
                });
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

        // Format currency inputs
        function formatCurrency(input, hiddenInput) {
            $(input).on('input', function() {
                // Remove non-numeric characters
                let value = $(this).val().replace(/[^\d]/g, '');

                // Format with thousand separators
                if (value !== '') {
                    // Convert to number and format
                    const formattedValue = new Intl.NumberFormat('id-ID').format(value);
                    $(this).val(formattedValue);

                    // Update the hidden input with the actual numeric value
                    $(hiddenInput).val(value);
                } else {
                    $(hiddenInput).val('0');
                }
            });
        }

        // Initialize currency formatting
        formatCurrency('#formatted_daily_salary', '#actual_daily_salary');
        formatCurrency('#formatted_monthly_salary', '#actual_monthly_salary');
    });
</script>
@endpush