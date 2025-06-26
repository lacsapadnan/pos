@extends('layouts.dashboard')

@section('title', 'Tambah Gaji Karyawan')
@section('menu-title', 'Tambah Gaji Karyawan')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@section('content')
@include('components.alert')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Gaji Karyawan</h3>
        <div class="card-toolbar">
            <a href="{{ route('gaji.index') }}" class="btn btn-light">
                <i class="ki-duotone ki-arrow-left fs-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('gaji.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label required">Karyawan</label>
                        <select class="form-select" data-control="select2" data-placeholder="Pilih Karyawan"
                            name="employee_id" required>
                            <option value="">Pilih Karyawan</option>
                            @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id')==$employee->id ? 'selected' : ''
                                }}>
                                {{ $employee->name }}
                                @if($employee->user)
                                - {{ $employee->user->email }}
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label required">Cabang</label>
                        <select class="form-select" data-control="select2" data-placeholder="Pilih Cabang"
                            name="warehouse_id" required>
                            <option value="">Pilih Cabang</option>
                            @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id')==$warehouse->id ? 'selected' :
                                '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label required">Tanggal Mulai Periode</label>
                        <input type="date" class="form-control" name="period_start"
                            value="{{ old('period_start', date('Y-m-01')) }}" required>
                        @error('period_start')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label required">Tanggal Akhir Periode</label>
                        <input type="date" class="form-control" name="period_end"
                            value="{{ old('period_end', date('Y-m-t')) }}" required>
                        @error('period_end')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="my-10 separator separator-dashed"></div>
            <h4 class="mb-6 text-gray-900 fw-bold">Pengaturan Gaji</h4>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label">Gaji Harian</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="daily_salary"
                                placeholder="Masukkan gaji harian" value="{{ old('daily_salary') }}" min="0"
                                max="999999999.99" step="0.01" id="dailySalary">
                        </div>
                        <div class="form-text">Kosongkan jika menggunakan gaji bulanan</div>
                        @error('daily_salary')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label">Gaji Bulanan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="monthly_salary"
                                placeholder="Masukkan gaji bulanan" value="{{ old('monthly_salary') }}" min="0"
                                max="999999999.99" step="0.01" id="monthlySalary">
                        </div>
                        <div class="form-text">Kosongkan jika menggunakan gaji harian</div>
                        @error('monthly_salary')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label">Potongan Lain-lain</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="other_deductions"
                                placeholder="Masukkan potongan lain" value="{{ old('other_deductions', 0) }}" min="0"
                                max="999999999.99" step="0.01">
                        </div>
                        <div class="form-text">Potongan selain kasbon (opsional)</div>
                        @error('other_deductions')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-8">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" rows="4"
                            placeholder="Masukkan catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Salary Type Alert -->
            <div id="salaryTypeAlert" class="alert alert-warning" style="display: none;">
                <i class="ki-duotone ki-information fs-2hx text-warning me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Peringatan!</h4>
                    <span>Minimal satu jenis gaji (harian atau bulanan) harus diisi.</span>
                </div>
            </div>

            <!-- Calculation Info -->
            <div class="alert alert-info">
                <i class="ki-duotone ki-information-5 fs-2hx text-info me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-info">Informasi Perhitungan</h4>
                    <span>• Gaji akan dihitung berdasarkan data kehadiran dalam periode yang ditentukan</span>
                    <span>• Potongan kasbon akan diambil dari data kasbon yang disetujui dalam periode tersebut</span>
                    <span>• Jika gaji bulanan diisi, maka akan digunakan sebagai gaji pokok</span>
                    <span>• Jika gaji harian diisi, maka akan dikalikan dengan jumlah hari hadir</span>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-light me-3" onclick="window.history.back()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="ki-duotone ki-check fs-2"></i>
                    Simpan Gaji
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('addon-script')
<script>
    "use strict";

    $(document).ready(function() {
        // Validate salary inputs
        $('#dailySalary, #monthlySalary').on('input', function() {
            validateSalaryInputs();
        });

        function validateSalaryInputs() {
            var dailySalary = parseFloat($('#dailySalary').val()) || 0;
            var monthlySalary = parseFloat($('#monthlySalary').val()) || 0;

            if (dailySalary === 0 && monthlySalary === 0) {
                $('#salaryTypeAlert').show();
                $('#submitBtn').prop('disabled', true);
            } else {
                $('#salaryTypeAlert').hide();
                $('#submitBtn').prop('disabled', false);
            }
        }

        // Set default period dates
        $('#period_start, #period_end').change(function() {
            var startDate = $('#period_start').val();
            var endDate = $('#period_end').val();

            if (startDate && endDate && startDate > endDate) {
                alert('Tanggal akhir periode harus sama atau setelah tanggal mulai periode');
                $(this).focus();
            }
        });

        // Auto-fill period end when start changes
        $('input[name="period_start"]').change(function() {
            var startDate = new Date($(this).val());
            if (startDate && !$('input[name="period_end"]').val()) {
                var endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0);
                $('input[name="period_end"]').val(endDate.toISOString().split('T')[0]);
            }
        });

        // Initial validation
        validateSalaryInputs();
    });
</script>
@endpush