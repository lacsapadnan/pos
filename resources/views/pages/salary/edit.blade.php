@extends('layouts.dashboard')

@section('title')
Edit Gaji Karyawan
@endsection

@section('content')
<div class="toolbar" id="kt_toolbar">
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
        <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
            class="flex-wrap mb-5 page-title d-flex align-items-center me-3 mb-lg-0">
            <h1 class="my-1 d-flex text-dark fw-bolder fs-3 align-items-center">Edit Gaji Karyawan</h1>
            <span class="mx-4 border-gray-300 h-20px border-start"></span>
            <ul class="my-1 breadcrumb breadcrumb-separatorless fw-bold fs-7">
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bg-gray-300 bullet w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('gaji.index') }}" class="text-muted text-hover-primary">Master Gaji Karyawan</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bg-gray-300 bullet w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-dark">Edit Gaji</li>
            </ul>
        </div>
        <div class="gap-2 d-flex align-items-center gap-lg-3">
            <a href="{{ route('gaji.index') }}" class="btn btn-sm btn-primary">
                <span class="svg-icon svg-icon-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect opacity="0.5" x="6" y="11" width="12" height="2" rx="1" fill="currentColor" />
                        <path
                            d="M12.3721 10.7076L11.668 10.003C11.2775 9.61275 10.6443 9.61275 10.2538 10.003L9.54972 10.7076C9.54972 10.7076 9.54972 10.7076 9.54972 10.7076L8.13551 12.1218L12.3721 10.7076Z"
                            fill="currentColor" />
                    </svg>
                </span>
                Kembali
            </a>
        </div>
    </div>
</div>

<div id="kt_content_container" class="container-xxl">
    <div class="card">
        <div class="pt-6 border-0 card-header">
            <div class="card-title">
                <div class="my-1 d-flex align-items-center position-relative">
                    <span class="svg-icon svg-icon-1 position-absolute ms-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M21.7 18.9L18.6 15.8C17.9 16.9 16.9 17.9 15.8 18.6L18.9 21.7C19.3 22.1 19.9 22.1 20.3 21.7L21.7 20.3C22.1 19.9 22.1 19.3 21.7 18.9Z"
                                fill="currentColor" />
                            <path opacity="0.3"
                                d="M11 20C6 20 2 16 2 11C2 6 6 2 11 2C16 2 20 6 20 11C20 16 16 20 11 20ZM11 4C7.1 4 4 7.1 4 11C4 14.9 7.1 18 11 18C14.9 18 18 14.9 18 11C18 7.1 14.9 4 11 4ZM8 11C8 9.3 9.3 8 11 8C11.6 8 12 7.6 12 7S11.6 6 11 6C8.2 6 6 8.2 6 11C6 11.6 6.4 12 7 12S8 11.6 8 11Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    <h3 class="ps-12">Edit Data Gaji</h3>
                </div>
            </div>
        </div>

        <div class="py-4 card-body">
            @include('components.alert')

            <form action="{{ route('gaji.update', $salary->id) }}" method="POST" id="form-edit-gaji">
                @csrf
                @method('PUT')

                <div class="mb-6 row">
                    <div class="col-md-6">
                        <label class="mb-2 required fs-6 fw-bold">Karyawan</label>
                        <select class="form-select form-select-solid" data-control="select2"
                            data-placeholder="Pilih karyawan" name="employee_id" required>
                            <option></option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id', $salary->employee_id) ==
                                $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} - {{ $employee->user->email ?? 'N/A' }}
                            </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="mb-2 required fs-6 fw-bold">Cabang</label>
                        <select class="form-select form-select-solid" data-control="select2"
                            data-placeholder="Pilih cabang" name="warehouse_id" required>
                            <option></option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $salary->warehouse_id) ==
                                $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-6 row">
                    <div class="col-md-6">
                        <label class="mb-2 required fs-6 fw-bold">Tanggal Mulai Periode</label>
                        <input type="date" class="form-control form-control-solid" placeholder="Tanggal mulai periode"
                            name="period_start" value="{{ old('period_start', $salary->period_start) }}" required />
                        @error('period_start')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="mb-2 required fs-6 fw-bold">Tanggal Akhir Periode</label>
                        <input type="date" class="form-control form-control-solid" placeholder="Tanggal akhir periode"
                            name="period_end" value="{{ old('period_end', $salary->period_end) }}" required />
                        @error('period_end')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-6 row">
                    <div class="col-md-6">
                        <label class="mb-2 fs-6 fw-bold">Gaji Harian</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control form-control-solid" placeholder="0"
                                name="daily_salary" value="{{ old('daily_salary', $salary->daily_salary) }}" min="0"
                                step="0.01" />
                        </div>
                        <div class="form-text">Kosongkan jika tidak menggunakan gaji harian</div>
                        @error('daily_salary')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="mb-2 fs-6 fw-bold">Gaji Bulanan</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control form-control-solid" placeholder="0"
                                name="monthly_salary" value="{{ old('monthly_salary', $salary->monthly_salary) }}"
                                min="0" step="0.01" />
                        </div>
                        <div class="form-text">Kosongkan jika tidak menggunakan gaji bulanan</div>
                        @error('monthly_salary')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-6 row">
                    <div class="col-md-6">
                        <label class="mb-2 fs-6 fw-bold">Potongan Lain</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control form-control-solid" placeholder="0"
                                name="other_deductions" value="{{ old('other_deductions', $salary->other_deductions) }}"
                                min="0" step="0.01" />
                        </div>
                        <div class="form-text">Potongan selain kasbon (opsional)</div>
                        @error('other_deductions')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="mb-2 fs-6 fw-bold">Catatan</label>
                        <textarea class="form-control form-control-solid" rows="3" name="notes"
                            placeholder="Catatan tambahan (opsional)">{{ old('notes', $salary->notes) }}</textarea>
                        @error('notes')
                        <div class="text-danger fs-7">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if($errors->has('salary'))
                <div class="alert alert-danger">
                    {{ $errors->first('salary') }}
                </div>
                @endif

                @if($errors->has('period'))
                <div class="alert alert-danger">
                    {{ $errors->first('period') }}
                </div>
                @endif

                <!-- Information Alert -->
                <div class="p-5 mb-10 alert alert-primary d-flex align-items-center">
                    <span class="svg-icon svg-icon-2hx svg-icon-primary me-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path opacity="0.3"
                                d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48613 15.8404 4.4407 16.8889C5.26474 17.8069 6.33444 18.6696 7.51648 19.477C8.8037 20.3602 10.1799 21.1849 11.5164 21.7864C11.8246 21.9287 12.1754 21.9287 12.4837 21.7864C13.8201 21.1849 15.1963 20.3602 16.4835 19.477C17.6656 18.6696 18.7353 17.8069 19.5593 16.8889C20.5139 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8188 4.45258 20.5543 4.37824Z"
                                fill="currentColor" />
                            <path
                                d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8055 13.6747 11.451 13.6747 11.8383 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-primary">Informasi Penting:</h4>
                        <span>• Minimal satu jenis gaji (harian atau bulanan) harus diisi</span><br>
                        <span>• Gaji harian akan dikalikan dengan jumlah hari hadir</span><br>
                        <span>• Gaji bulanan akan digunakan sebagai gaji tetap</span><br>
                        <span>• Jika keduanya diisi, sistem akan menggunakan yang lebih menguntungkan</span>
                    </div>
                </div>

                <div class="text-center">
                    <button type="reset" class="btn btn-light me-3">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Simpan</span>
                        <span class="indicator-progress">Please wait...
                            <span class="align-middle spinner-border spinner-border-sm ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
    // Initialize form validation
    const form = document.getElementById('form-edit-gaji');
    const validator = FormValidation.formValidation(
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
                            message: 'Cabang harus dipilih'
                        }
                    }
                },
                'period_start': {
                    validators: {
                        notEmpty: {
                            message: 'Tanggal mulai periode harus diisi'
                        },
                        date: {
                            format: 'YYYY-MM-DD',
                            message: 'Format tanggal tidak valid'
                        }
                    }
                },
                'period_end': {
                    validators: {
                        notEmpty: {
                            message: 'Tanggal akhir periode harus diisi'
                        },
                        date: {
                            format: 'YYYY-MM-DD',
                            message: 'Format tanggal tidak valid'
                        },
                        callback: {
                            message: 'Tanggal akhir harus sama atau setelah tanggal mulai',
                            callback: function(input) {
                                const startDate = form.querySelector('[name="period_start"]').value;
                                const endDate = input.value;

                                if (startDate && endDate) {
                                    return new Date(endDate) >= new Date(startDate);
                                }
                                return true;
                            }
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

    // Submit form
    const submitButton = document.querySelector('[data-kt-users-modal-action="submit"]');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (validator) {
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');

                    // Disable button to avoid multiple click
                    submitButton.disabled = true;

                    // Submit form
                    form.submit();
                }
            });
        }
    });
});
</script>
@endsection