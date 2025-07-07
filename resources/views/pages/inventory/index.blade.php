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
                var buttons = new $.fn.dataTable.Buttons(table, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: documentTitle
                        },
                        {
                            extend: 'excelHtml5',
                            title: documentTitle
                        },
                        {
                            extend: 'csvHtml5',
                            title: documentTitle
                        },
                        {
                            extend: 'pdfHtml5',
                            title: documentTitle
                        }
                    ]
                }).container().appendTo($('#kt_datatable_example_buttons'));

                // Hook dropdown menu click event to datatable export buttons
                const exportButtons = document.querySelectorAll(
                    '#kt_datatable_example_export_menu [data-kt-export]');
                exportButtons.forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();

                        // Get clicked export value
                        const exportValue = e.target.getAttribute('data-kt-export');
                        const target = document.querySelector('.dt-buttons .buttons-' +
                            exportValue);

                        // Trigger click event on hidden datatable export buttons
                        target.click();
                    });
                });
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
