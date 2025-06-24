@extends('layouts.dashboard')

@section('title', 'Tambah Kasbon')
@section('menu-title', 'Tambah Kasbon')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@section('content')
@include('components.alert')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Kasbon</h3>
        <div class="card-toolbar">
            <a href="{{ route('kasbon.index') }}" class="btn btn-light">
                <i class="ki-duotone ki-arrow-left fs-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('kasbon.store') }}" method="POST">
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
                        <label class="form-label required">Jumlah Kasbon</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="amount" placeholder="Masukkan jumlah kasbon"
                                value="{{ old('amount') }}" min="1" max="999999999.99" step="0.01" required>
                        </div>
                        @error('amount')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label required">Tanggal Kasbon</label>
                        <input type="date" class="form-control" name="advance_date"
                            value="{{ old('advance_date', date('Y-m-d')) }}" required>
                        @error('advance_date')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-8">
                        <label class="form-label required">Tipe Pembayaran</label>
                        <select class="form-select" name="type" id="paymentType" required>
                            <option value="">Pilih Tipe Pembayaran</option>
                            <option value="direct" {{ old('type')=='direct' ? 'selected' : '' }}>Langsung</option>
                            <option value="installment" {{ old('type')=='installment' ? 'selected' : '' }}>Cicilan
                            </option>
                        </select>
                        @error('type')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6" id="installmentCountContainer" style="display: none;">
                    <div class="mb-8">
                        <label class="form-label required">Jumlah Cicilan</label>
                        <select class="form-select" name="installment_count" id="installmentCount">
                            <option value="">Pilih Jumlah Cicilan</option>
                            @for ($i = 2; $i <= 36; $i++) <option value="{{ $i }}" {{ old('installment_count')==$i
                                ? 'selected' : '' }}>
                                {{ $i }} bulan
                                </option>
                                @endfor
                        </select>
                        @error('installment_count')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-8">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="description" rows="4"
                            placeholder="Masukkan keterangan kasbon (opsional)">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Installment Preview -->
            <div id="installmentPreview" style="display: none;">
                <div class="alert alert-info">
                    <h5 class="alert-heading">Preview Cicilan</h5>
                    <div id="installmentDetails"></div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-light me-3" onclick="window.history.back()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-duotone ki-check fs-2"></i>
                    Simpan Kasbon
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
        // Handle payment type change
        $('#paymentType').change(function() {
            var type = $(this).val();

            if (type === 'installment') {
                $('#installmentCountContainer').show();
                $('#installmentCount').prop('required', true);
            } else {
                $('#installmentCountContainer').hide();
                $('#installmentCount').prop('required', false);
                $('#installmentPreview').hide();
            }
        });

        // Handle installment count change and amount change for preview
        $('#installmentCount, input[name="amount"]').on('change keyup', function() {
            updateInstallmentPreview();
        });

        // Initialize on page load
        var currentType = $('#paymentType').val();
        if (currentType === 'installment') {
            $('#installmentCountContainer').show();
            $('#installmentCount').prop('required', true);
            updateInstallmentPreview();
        }
    });

    function updateInstallmentPreview() {
        var amount = parseFloat($('input[name="amount"]').val()) || 0;
        var installmentCount = parseInt($('#installmentCount').val()) || 0;
        var advanceDate = $('input[name="advance_date"]').val();

        if (amount > 0 && installmentCount > 0 && advanceDate) {
            var installmentAmount = amount / installmentCount;
            var startDate = new Date(advanceDate);
            startDate.setMonth(startDate.getMonth() + 1); // First installment due next month

            var previewHtml = '<strong>Jumlah per cicilan: ' +
                             new Intl.NumberFormat('id-ID', {
                                 style: 'currency',
                                 currency: 'IDR',
                                 minimumFractionDigits: 0,
                                 maximumFractionDigits: 0,
                             }).format(installmentAmount) + '</strong><br>';

            previewHtml += '<small class="text-muted">Jadwal pembayaran:</small><ul class="mt-2">';

            for (var i = 1; i <= installmentCount; i++) {
                var dueDate = new Date(startDate);
                dueDate.setMonth(dueDate.getMonth() + (i - 1));

                previewHtml += '<li>Cicilan ke-' + i + ': ' +
                              dueDate.toLocaleDateString('id-ID', {
                                  day: 'numeric',
                                  month: 'long',
                                  year: 'numeric'
                              }) + '</li>';
            }

            previewHtml += '</ul>';

            $('#installmentDetails').html(previewHtml);
            $('#installmentPreview').show();
        } else {
            $('#installmentPreview').hide();
        }
    }

    // Format currency input
    $('input[name="amount"]').on('input', function() {
        var value = $(this).val().replace(/[^0-9.]/g, '');
        $(this).val(value);
    });
</script>
@endpush