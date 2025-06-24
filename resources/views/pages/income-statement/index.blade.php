@extends('layouts.dashboard')

@section('title', 'Laporan Laba Rugi')
@section('menu-title', 'Laporan Laba Rugi')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
<style>
    .income-statement-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .section-title {
        font-weight: bold;
        font-size: 1.2em;
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .financial-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .financial-item:last-child {
        border-bottom: none;
    }

    .total-line {
        font-weight: bold;
        font-size: 1.1em;
        border-top: 2px solid #2c3e50;
        padding-top: 10px;
        margin-top: 10px;
    }

    .profit-positive {
        color: #27ae60;
    }

    .profit-negative {
        color: #e74c3c;
    }

    .currency {
        font-family: monospace;
    }

    .detail-tables-container {
        display: flex;
        gap: 20px;
    }

    .detail-table-card {
        flex: 1;
        height: 600px;
        display: flex;
        flex-direction: column;
    }

    .detail-table-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .detail-table-card .table-responsive {
        flex: 1;
        overflow-y: auto;
    }

    .detail-table-card .dataTables_wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .detail-table-card .dataTables_scroll {
        flex: 1;
    }
</style>
@endpush

@section('content')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <h2>Laporan Laba Rugi</h2>
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <div class="row g-3">
                @can('lihat semua laba rugi')
                <div class="col-md-3">
                    <select class="form-select" data-control="select2" data-placeholder="Pilih Gudang"
                        id="warehouseFilter">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" data-control="select2" data-placeholder="Pilih User" id="userFilter">
                        <option value="">Semua User</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-md-3">
                    <input type="hidden" id="warehouseFilter" value="{{ auth()->user()->warehouse_id }}">
                    <input type="text" class="form-control" value="{{ auth()->user()->warehouse->name }}" disabled>
                </div>
                <div class="col-md-3">
                    <input type="hidden" id="userFilter" value="{{ auth()->id() }}">
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                </div>
                @endcan
                <div class="col-md-3">
                    <input type="date" id="fromDateFilter" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" id="toDateFilter" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary" id="generateReport">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-magnifier fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Generate Laporan
                        </span>
                        <span class="indicator-progress" style="display: none;">
                            Please wait...
                            <span class="align-middle spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                    <button type="button" class="btn btn-success" id="exportReport" style="display: none;">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-file-down fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Export Excel
                        </span>
                        <span class="indicator-progress" style="display: none;">
                            Exporting...
                            <span class="align-middle spinner-border spinner-border-sm ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body" id="reportContent" style="display: none;">
        <!-- Report Header -->
        <div class="mb-4 text-center">
            <h3 id="reportTitle">LAPORAN LABA RUGI</h3>
            <p id="reportPeriod" class="text-muted"></p>
            <p id="reportWarehouse" class="text-muted"></p>
        </div>

        <!-- Revenue Section -->
        <div class="income-statement-section">
            <div class="section-title">PENDAPATAN</div>
            <div id="revenueSection">
                <div class="financial-item">
                    <span>Penjualan</span>
                    <span class="currency" id="salesRevenue">Rp 0</span>
                </div>
                <div class="financial-item total-line">
                    <span><strong>Total Pendapatan</strong></span>
                    <span class="currency" id="totalRevenue"><strong>Rp 0</strong></span>
                </div>
            </div>
        </div>

        <!-- Cost of Goods Sold Section -->
        <div class="income-statement-section">
            <div class="section-title">HARGA POKOK PENJUALAN</div>
            <div id="cogsSection">
                <div class="financial-item">
                    <span>Harga Pokok Penjualan</span>
                    <span class="currency" id="totalCogs">Rp 0</span>
                </div>
                <div class="financial-item total-line">
                    <span><strong>Total Harga Pokok Penjualan</strong></span>
                    <span class="currency" id="totalCogsAmount"><strong>Rp 0</strong></span>
                </div>
            </div>
        </div>

        <!-- Gross Profit Section -->
        <div class="income-statement-section">
            <div class="financial-item total-line">
                <span><strong>LABA KOTOR</strong></span>
                <span class="currency" id="grossProfit"><strong>Rp 0</strong></span>
            </div>
        </div>

        <!-- Operating Expenses Section -->
        <div class="income-statement-section">
            <div class="section-title">BEBAN OPERASIONAL</div>
            <div id="expensesSection">
                <!-- Expenses will be populated here -->
            </div>
            <div class="financial-item total-line">
                <span><strong>Total Beban Operasional</strong></span>
                <span class="currency" id="totalExpenses"><strong>Rp 0</strong></span>
            </div>
        </div>

        <!-- Other Income Section -->
        <div class="income-statement-section">
            <div class="section-title">PENDAPATAN LAIN-LAIN</div>
            <div id="otherIncomeSection">
                <!-- Other income will be populated here -->
            </div>
            <div class="financial-item total-line">
                <span><strong>Total Pendapatan Lain-lain</strong></span>
                <span class="currency" id="totalOtherIncome"><strong>Rp 0</strong></span>
            </div>
        </div>

        <!-- Net Income Section -->
        <div class="income-statement-section" style="background: #2c3e50; color: white;">
            <div class="financial-item total-line" style="border-top: none; font-size: 1.3em;">
                <span><strong>LABA BERSIH</strong></span>
                <span class="currency" id="netIncome"><strong>Rp 0</strong></span>
            </div>
        </div>

        <!-- Detailed Tables -->
        <div class="mt-5 detail-tables-container">
            <div class="detail-table-card card">
                <div class="card-header">
                    <h5>Detail Penjualan per Produk</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-row-bordered gy-5 gs-7" id="salesDetailTable">
                            <thead>
                                <tr class="text-gray-800 fw-bold fs-6">
                                    <th>No</th>
                                    <th>Produk</th>
                                    <th>Qty Terjual</th>
                                    <th>Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="detail-table-card card">
                <div class="card-header">
                    <h5>Detail HPP per Produk</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-row-bordered gy-5 gs-7" id="cogsDetailTable">
                            <thead>
                                <tr class="text-gray-800 fw-bold fs-6">
                                    <th>No</th>
                                    <th>Produk</th>
                                    <th>Qty (Eceran)</th>
                                    <th>Harga Modal</th>
                                    <th>Total HPP</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    "use strict";

        let reportData = null;

        $(document).ready(function() {
            // Initialize Select2
            $('#warehouseFilter, #userFilter').select2();

            // Initialize DataTables
            initializeDataTables();

            // Generate report button click
            $('#generateReport').click(function() {
                generateReport();
            });

            // Export report button click
            $('#exportReport').click(function() {
                exportToExcel();
            });

            // Auto-generate report on page load
            generateReport();
        });

        function initializeDataTables() {
            // Initialize Sales Detail Table
            $('#salesDetailTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 0 }, // No column not sortable
                    { "className": "text-center", "targets": [0, 2] },
                    { "className": "text-end", "targets": 3 }
                ]
            });

            // Initialize COGS Detail Table
            $('#cogsDetailTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 0 }, // No column not sortable
                    { "className": "text-center", "targets": [0, 2] },
                    { "className": "text-end", "targets": [3, 4] }
                ]
            });
        }

        function generateReport() {
            const fromDate = $('#fromDateFilter').val();
            const toDate = $('#toDateFilter').val();
            const warehouse = $('#warehouseFilter').val();
            const user = $('#userFilter').val();

            if (!fromDate || !toDate) {
                alert('Silakan pilih tanggal mulai dan tanggal akhir');
                return;
            }

            // Show button loading state
            const generateBtn = $('#generateReport');
            generateBtn.attr('disabled', true);
            generateBtn.find('.indicator-label').hide();
            generateBtn.find('.indicator-progress').show();

            // Hide export button and report content
            $('#exportReport').hide();
            $('#reportContent').hide();

            $.ajax({
                url: '{{ route('api.income-statement') }}',
                type: 'GET',
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    warehouse: warehouse,
                    user_id: user
                },
                success: function(response) {
                    reportData = response;
                    populateReport(response);
                    $('#reportContent').show();
                    $('#exportReport').show();
                },
                error: function(xhr, status, error) {
                    console.error('Error generating report:', error);

                    let errorMessage = 'Terjadi kesalahan saat menggenerate laporan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Show error alert
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Reset button state
                    generateBtn.attr('disabled', false);
                    generateBtn.find('.indicator-progress').hide();
                    generateBtn.find('.indicator-label').show();
                }
            });
        }

        function populateReport(data) {
            // Format currency function
            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                }).format(amount).replace(',00', '');
            };

            // Update report header
            const fromDate = new Date($('#fromDateFilter').val()).toLocaleDateString('id-ID');
            const toDate = new Date($('#toDateFilter').val()).toLocaleDateString('id-ID');
            $('#reportPeriod').text(`Periode: ${fromDate} - ${toDate}`);
            $('#reportWarehouse').text(`Gudang: ${data.period.warehouse}`);

            // Update revenue section
            $('#salesRevenue').text(formatCurrency(data.sales_data.total_revenue));
            $('#totalRevenue').text(formatCurrency(data.sales_data.total_revenue));

            // Update COGS section
            $('#totalCogs').text(formatCurrency(data.cogs_data.total_cogs));
            $('#totalCogsAmount').text(formatCurrency(data.cogs_data.total_cogs));

            // Update gross profit
            const grossProfitClass = data.gross_profit >= 0 ? 'profit-positive' : 'profit-negative';
            $('#grossProfit').text(formatCurrency(data.gross_profit)).removeClass('profit-positive profit-negative').addClass(grossProfitClass);

            // Update expenses section
            let expensesHtml = '';
            data.operating_expenses.expenses_by_category.forEach(expense => {
                expensesHtml += `
                    <div class="financial-item">
                        <span>${expense.category}</span>
                        <span class="currency">${formatCurrency(expense.total_amount)}</span>
                    </div>
                `;
            });
            $('#expensesSection').html(expensesHtml);
            $('#totalExpenses').text(formatCurrency(data.operating_expenses.total_operating_expenses));

            // Update other income section
            let otherIncomeHtml = '';
            data.other_income.income_by_category.forEach(income => {
                otherIncomeHtml += `
                    <div class="financial-item">
                        <span>${income.category}</span>
                        <span class="currency">${formatCurrency(income.total_amount)}</span>
                    </div>
                `;
            });
            $('#otherIncomeSection').html(otherIncomeHtml);
            $('#totalOtherIncome').text(formatCurrency(data.other_income.total_other_income));

            // Update net income
            const netIncomeClass = data.net_income >= 0 ? 'profit-positive' : 'profit-negative';
            $('#netIncome').text(formatCurrency(data.net_income)).removeClass('profit-positive profit-negative').addClass(netIncomeClass);

            // Populate sales detail table with DataTables
            const salesTable = $('#salesDetailTable').DataTable();
            salesTable.clear();

            data.sales_data.sales_by_product.forEach((product, index) => {
                salesTable.row.add([
                    index + 1,
                    product.product_name,
                    product.quantity_sold,
                    formatCurrency(product.total_revenue)
                ]);
            });
            salesTable.draw();

            // Populate COGS detail table with DataTables
            const cogsTable = $('#cogsDetailTable').DataTable();
            cogsTable.clear();

            data.cogs_data.cogs_by_product.forEach((product, index) => {
                cogsTable.row.add([
                    index + 1,
                    product.product_name,
                    product.quantity_sold_eceran,
                    formatCurrency(product.cost_price),
                    formatCurrency(product.total_cogs)
                ]);
            });
            cogsTable.draw();
        }

        function exportToExcel() {
            if (!reportData) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Tidak ada data untuk diekspor. Silakan generate laporan terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Show export button loading state
            const exportBtn = $('#exportReport');
            exportBtn.attr('disabled', true);
            exportBtn.find('.indicator-label').hide();
            exportBtn.find('.indicator-progress').show();

            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                }).format(amount).replace(',00', '');
            };

            // Create workbook
            const wb = XLSX.utils.book_new();

            // Income Statement Sheet
            const incomeStatementData = [
                ['LAPORAN LABA RUGI'],
                [`Periode: ${$('#fromDateFilter').val()} - ${$('#toDateFilter').val()}`],
                [`Gudang: ${reportData.period.warehouse}`],
                [''],
                ['PENDAPATAN'],
                ['Penjualan', formatCurrency(reportData.sales_data.total_revenue)],
                ['Total Pendapatan', formatCurrency(reportData.sales_data.total_revenue)],
                [''],
                ['HARGA POKOK PENJUALAN'],
                ['Harga Pokok Penjualan', formatCurrency(reportData.cogs_data.total_cogs)],
                ['Total Harga Pokok Penjualan', formatCurrency(reportData.cogs_data.total_cogs)],
                [''],
                ['LABA KOTOR', formatCurrency(reportData.gross_profit)],
                [''],
                ['BEBAN OPERASIONAL']
            ];

            // Add expenses
            reportData.operating_expenses.expenses_by_category.forEach(expense => {
                incomeStatementData.push([expense.category, formatCurrency(expense.total_amount)]);
            });
            incomeStatementData.push(['Total Beban Operasional', formatCurrency(reportData.operating_expenses.total_operating_expenses)]);
            incomeStatementData.push(['']);
            incomeStatementData.push(['PENDAPATAN LAIN-LAIN']);

            // Add other income
            reportData.other_income.income_by_category.forEach(income => {
                incomeStatementData.push([income.category, formatCurrency(income.total_amount)]);
            });
            incomeStatementData.push(['Total Pendapatan Lain-lain', formatCurrency(reportData.other_income.total_other_income)]);
            incomeStatementData.push(['']);
            incomeStatementData.push(['LABA BERSIH', formatCurrency(reportData.net_income)]);

            const ws1 = XLSX.utils.aoa_to_sheet(incomeStatementData);
            XLSX.utils.book_append_sheet(wb, ws1, 'Laporan Laba Rugi');

            // Sales Detail Sheet
            const salesData = [
                ['Detail Penjualan per Produk'],
                ['No', 'Produk', 'Qty Terjual', 'Total Pendapatan']
            ];
            reportData.sales_data.sales_by_product.forEach((product, index) => {
                salesData.push([index + 1, product.product_name, product.quantity_sold, formatCurrency(product.total_revenue)]);
            });

            const ws2 = XLSX.utils.aoa_to_sheet(salesData);
            XLSX.utils.book_append_sheet(wb, ws2, 'Detail Penjualan');

            // COGS Detail Sheet
            const cogsData = [
                ['Detail HPP per Produk'],
                ['No', 'Produk', 'Qty (Eceran)', 'Harga Modal', 'Total HPP']
            ];
            reportData.cogs_data.cogs_by_product.forEach((product, index) => {
                cogsData.push([index + 1, product.product_name, product.quantity_sold_eceran, formatCurrency(product.cost_price), formatCurrency(product.total_cogs)]);
            });

            const ws3 = XLSX.utils.aoa_to_sheet(cogsData);
            XLSX.utils.book_append_sheet(wb, ws3, 'Detail HPP');

            // Export
            const filename = `Laporan_Laba_Rugi_${$('#fromDateFilter').val()}_${$('#toDateFilter').val()}.xlsx`;

            try {
                XLSX.writeFile(wb, filename);

                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: 'Laporan berhasil diekspor ke Excel',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('Export error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengekspor laporan',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset export button state
                const exportBtn = $('#exportReport');
                exportBtn.attr('disabled', false);
                exportBtn.find('.indicator-progress').hide();
                exportBtn.find('.indicator-label').show();
            }
        }
</script>
@endpush