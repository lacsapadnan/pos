@extends('layouts.dashboard')

@section('title', 'Inventori')
@section('menu-title', 'Inventori')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
<style>
    ::-webkit-scrollbar-thumb {
        -webkit-border-radius: 10px;
        border-radius: 10px;
        background: rgba(192, 192, 192, 0.3);
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
        background-color: #818B99;
    }

    .dataTables_scrollBody {
        transform: rotateX(180deg);
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 16px;
    }

    .dataTables_scrollBody table {
        transform: rotateX(180deg);
    }
</style>
@endpush

@include('includes.datatable-pagination')

@section('content')
@include('components.alert')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <!--begin::Search-->
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                        class="path2"></span></i> <input type="text" data-kt-filter="search"
                    class="form-control form-control-solid w-250px ps-14" placeholder="Cari data inventori">
            </div>
            <select id="categoryFilter" class="form-select ms-3" aria-label="Category filter" data-control="select2">
                <option value="">All Kelompok</option>
                @foreach ($categories as $category)
                <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @role('master')
            <select id="warehouseFilter" class="form-select ms-4" aria-label="Branch filter" data-control="select2">
                <option value="">All Cabangs</option>
                @foreach ($warehouse as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
            @endrole
            <!--end::Search-->
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <!--begin::Export dropdown-->
            <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export Data
            </button>
            @can('simpan inventory')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_1">
                Tambah Data
            </button>
            @endcan
            <!--begin::Menu-->
            <div id="kt_datatable_example_export_menu"
                class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px"
                data-kt-menu="true">
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="copy">
                        Copy to clipboard
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="excel">
                        Export as Excel
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="csv">
                        Export as CSV
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="pdf">
                        Export as PDF
                    </a>
                </div>
                <!--end::Menu item-->
            </div>
            <div id="kt_datatable_example_buttons" class="d-none"></div>
        </div>
    </div>
    <div class="card-body">
        <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
            <div class="table-responsive">
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th>Cabang</th>
                            <th>Kelompok</th>
                            <th>Nama Barang</th>
                            <th>Jml Per Dus</th>
                            <th>Jml Per Pak</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('pages.inventory.modal')
@includeIf('pages.inventory.edit')
@endsection

@push('addon-script')
<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    "use strict";

        // Class definition
        var KTDatatablesExample = function() {
            // Shared variables
            var table;
            var datatable;

            // Private functions
            var initDatatable = function() {
                // Set date data order
                const tableRows = table.querySelectorAll('tbody tr');

                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    serverSide: true,     // <-- PENTING
                    processing: true,     // <-- Tambah ini untuk spinner
                    order: [],
                    pageLength: 10,
                    "dom": '<"top"lp>rt<"bottom"lp><"clear">',
                    ajax: {
                        url: '{{ route('api.inventori') }}',
                        type: 'GET',
                    },
                    columns: [
                        { data: 'warehouse.name', name: 'warehouse.name' },
                        { data: 'product.group', name: 'product.group' },
                        { data: 'product.name', name: 'product.name' },
                        { data: 'product.dus_to_eceran', name: 'product.dus_to_eceran' },
                        { data: 'product.pak_to_eceran', name: 'product.pak_to_eceran' },
                        { data: 'quantity', name: 'quantity' },
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
                                    @can('update inventory')
                                        <button type="button" class="btn btn-primary edit-button" data-id="${data}" data-toggle="modal" data-target="#editModal">Edit</button>
                                    @endcan
                                    @can('hapus inventory')
                                        <form action="/inventori/${data}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    @endcan
                                `;
                            }
                        }
                    ],
                });

                $('#categoryFilter').on('change', function() {
                    var category = this.value;
                    var warehouseId = $('#warehouseFilter').val();
                    datatable.ajax.url('{{ route('api.inventori') }}?category=' + category + '&warehouse_id=' + warehouseId).load();
                });

                $('#warehouseFilter').on('change', function() {
                    var warehouseId = this.value;
                    var category = $('#categoryFilter').val();
                    datatable.ajax.url('{{ route('api.inventori') }}?category=' + category + '&warehouse_id=' + warehouseId).load();
                });
            }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Inventory Data Report';

                // Hook dropdown menu click event to custom export functions
                const exportButtons = document.querySelectorAll(
                    '#kt_datatable_example_export_menu [data-kt-export]');
                exportButtons.forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();

                        // Get clicked export value
                        const exportValue = e.target.getAttribute('data-kt-export');

                        // Get current filter values
                        const category = $('#categoryFilter').val();
                        const warehouseId = $('#warehouseFilter').val();

                        // Call custom export function with all data
                        exportAllData(exportValue, category, warehouseId, documentTitle);
                    });
                });
            }

                        // Function to export all data
            var exportAllData = (exportType, category, warehouseId, title) => {
                // Build URL with current filters
                let url = '{{ route('api.inventori.export') }}';
                const params = new URLSearchParams();
                if (category) params.append('category', category);
                if (warehouseId) params.append('warehouse_id', warehouseId);
                if (params.toString()) url += '?' + params.toString();

                // Fetch all data
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        if (exportType === 'copy') {
                            copyToClipboard(response.data, title);
                        } else if (exportType === 'excel') {
                            exportToExcel(response.data, title);
                        } else if (exportType === 'csv') {
                            exportToCSV(response.data, title);
                        } else if (exportType === 'pdf') {
                            exportToPDF(response.data, title);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Export failed:', error);
                        alert('Export failed. Please try again.');
                    }
                });
            }

            // Copy to clipboard function
            var copyToClipboard = (data, title) => {
                let text = 'Cabang\tKelompok\tNama Barang\tJml Per Dus\tJml Per Pak\tStok\n';
                data.forEach(function(row) {
                    text += `${row.warehouse}\t${row.category}\t${row.product_name}\t${row.dus_to_eceran}\t${row.pak_to_eceran}\t${row.quantity}\n`;
                });

                navigator.clipboard.writeText(text).then(function() {
                    alert('Data copied to clipboard!');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                    alert('Failed to copy to clipboard.');
                });
            }

                        // Export to Excel function
            var exportToExcel = (data, title) => {
                // Create workbook and worksheet
                const headers = ['Cabang', 'Kelompok', 'Nama Barang', 'Jml Per Dus', 'Jml Per Pak', 'Stok'];

                // Create HTML table for Excel
                let htmlTable = '<table border="1">';

                // Add header row
                htmlTable += '<thead><tr>';
                headers.forEach(header => {
                    htmlTable += `<th style="background-color: #f2f2f2; font-weight: bold;">${header}</th>`;
                });
                htmlTable += '</tr></thead>';

                // Add data rows
                htmlTable += '<tbody>';
                data.forEach(row => {
                    htmlTable += '<tr>';
                    htmlTable += `<td>${row.warehouse || ''}</td>`;
                    htmlTable += `<td>${row.category || ''}</td>`;
                    htmlTable += `<td>${row.product_name || ''}</td>`;
                    htmlTable += `<td>${row.dus_to_eceran || ''}</td>`;
                    htmlTable += `<td>${row.pak_to_eceran || ''}</td>`;
                    htmlTable += `<td>${row.quantity || 0}</td>`;
                    htmlTable += '</tr>';
                });
                htmlTable += '</tbody></table>';

                // Create Excel file using HTML table format
                const excelContent = `
                    <html xmlns:o="urn:schemas-microsoft-com:office:office"
                          xmlns:x="urn:schemas-microsoft-com:office:excel"
                          xmlns="http://www.w3.org/TR/REC-html40">
                    <head>
                        <meta charset="utf-8">
                        <!--[if gte mso 9]>
                        <xml>
                            <x:ExcelWorkbook>
                                <x:ExcelWorksheets>
                                    <x:ExcelWorksheet>
                                        <x:Name>${title}</x:Name>
                                        <x:WorksheetOptions>
                                            <x:DisplayGridlines/>
                                        </x:WorksheetOptions>
                                    </x:ExcelWorksheet>
                                </x:ExcelWorksheets>
                            </x:ExcelWorkbook>
                        </xml>
                        <![endif]-->
                        <style>
                            table { border-collapse: collapse; width: 100%; }
                            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; font-weight: bold; }
                        </style>
                    </head>
                    <body>${htmlTable}</body>
                    </html>
                `;

                const blob = new Blob([excelContent], {
                    type: 'application/vnd.ms-excel;charset=utf-8;'
                });

                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = title.replace(/ /g, '_').toLowerCase() + '.xls';
                link.click();
                URL.revokeObjectURL(link.href);
            }

            // Export to CSV function
            var exportToCSV = (data, title) => {
                const headers = ['Cabang', 'Kelompok', 'Nama Barang', 'Jml Per Dus', 'Jml Per Pak', 'Stok'];
                const csvContent = [
                    headers.join(','),
                    ...data.map(row => [
                        `"${row.warehouse}"`,
                        `"${row.category}"`,
                        `"${row.product_name}"`,
                        `"${row.dus_to_eceran}"`,
                        `"${row.pak_to_eceran}"`,
                        `"${row.quantity}"`
                    ].join(','))
                ].join('\n');

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = title.replace(/ /g, '_').toLowerCase() + '.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }

            // Export to PDF function (simple table format)
            var exportToPDF = (data, title) => {
                // Create a simple HTML table for PDF export
                let htmlContent = `
                    <html>
                    <head>
                        <title>${title}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            h1 { text-align: center; margin-bottom: 30px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; font-weight: bold; }
                            tr:nth-child(even) { background-color: #f9f9f9; }
                        </style>
                    </head>
                    <body>
                        <h1>${title}</h1>
                        <table>
                            <thead>
                                <tr>
                                    <th>Cabang</th>
                                    <th>Kelompok</th>
                                    <th>Nama Barang</th>
                                    <th>Jml Per Dus</th>
                                    <th>Jml Per Pak</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.forEach(function(row) {
                    htmlContent += `
                        <tr>
                            <td>${row.warehouse}</td>
                            <td>${row.category}</td>
                            <td>${row.product_name}</td>
                            <td>${row.dus_to_eceran}</td>
                            <td>${row.pak_to_eceran}</td>
                            <td>${row.quantity}</td>
                        </tr>
                    `;
                });

                htmlContent += `
                            </tbody>
                        </table>
                    </body>
                    </html>
                `;

                // Open in new window for printing/saving as PDF
                const printWindow = window.open('', '_blank');
                printWindow.document.write(htmlContent);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            }

            // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
            var handleSearchDatatable = () => {
                const filterSearch = document.querySelector('[data-kt-filter="search"]');
                filterSearch.addEventListener('keyup', function(e) {
                    datatable.search(e.target.value).draw();
                });
            }

            // Public methods
            return {
                init: function() {
                    table = document.querySelector('#kt_datatable_example');

                    if (!table) {
                        return;
                    }

                    initDatatable();
                    exportButtons();
                    handleSearchDatatable();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
</script>
<script>
    $(document).on('click', '.edit-button', function() {
            var id = $(this).data('id');
            // Make an AJAX request to fetch the data based on the id
            $.ajax({
                url: '/inventori/' + id +
                    '/edit', // Update the URL to your Laravel route that fetches the data
                method: 'GET',
                success: function(response) {
                    console.log(response);
                    $('#editInventoryId').val(response.id);
                    $('#productInput').val(response.product_id).trigger(
                        'change'); // Update select form1 value and trigger change event
                    $('#cabangInput').val(response.warehouse_id).trigger(
                        'change'); // Update select form2 value and trigger change event
                    $('#quantityInput').val(response.quantity);

                    $('#editForm').attr('action', '/inventori/' + id);
                    $('#editModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        });
</script>
@endpush