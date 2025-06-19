@extends('layouts.dashboard')

@section('title', 'Laporan')
@section('menu-title', 'Laporan')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@section('content')
@include('components.alert')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <!--begin::Search-->
            <!-- Add user_id filter select -->
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                        class="path2"></span></i> <input type="text" data-kt-filter="search"
                    class="form-control form-control-solid w-250px ps-14" placeholder="Cari data produk"
                    id="searchInput">
            </div>
            <!--end::Search-->
            @role('master')
            <div class="ms-2">
                <select id="warehouseFilter" class="form-select" aria-label="Warehouse filter" data-control="select2">
                    <option value="">All Cabang</option>
                    @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="ms-2">
                <input type="text" id="warehouseFilter" class="form-control" value="{{ auth()->user()->warehouse_id }}"
                    disabled hidden>
                <input type="text" class="form-control" value="{{ auth()->user()->warehouse->name }}" disabled>
            </div>
            @endrole
            @role('master')
            <div class="ms-3">
                <select id="userFilter" class="form-select" aria-label="User filter" data-control="select2">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="ms-3">
                <input type="text" id="userFilter" class="form-control" value="{{ auth()->id() }}" disabled hidden>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
            </div>
            @endrole
            <div class="ms-3">
                <select id="forFilter" class="form-select" aria-label="For filter" data-control="select2">
                    <option value="">Masuk & Keluar</option>
                    <option value="MASUK">Masuk</option>
                    <option value="KELUAR">Keluar</option>
                </select>
            </div>
            <div class="ms-3">
                <select id="productFilter" class="form-select" aria-label="Product filter" data-control="select2">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-calendar fs-1 position-absolute ms-4"></i>
                <input type="date" id="fromDateFilter" class="form-control form-control-solid ms-2"
                    data-kt-filter="date" placeholder="Dari Tanggal">
                <input type="date" id="toDateFilter" class="form-control form-control-solid ms-2" data-kt-filter="date"
                    placeholder="Ke Tanggal">
            </div>
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <div id="kt_datatable_example_buttons" class="d-none"></div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h2>Jumlah Dus: <span id="totalDus">Calculating...</span></h2>
            </div>
            <div class="col-md-3">
                <h2>Jumlah Pak: <span id="totalPak">Calculating...</span></h2>
            </div>
            <div class="col-md-3">
                <h2>Jumlah Eceran: <span id="totalEceran">Calculating...</span></h2>
            </div>
            <div class="col-md-3">
                <h2>Total Nilai: <span id="totalNilai">Calculating...</span></h2>
            </div>
        </div>
        <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
            <div class="table-responsive">
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th>No</th>
                            <th>Produk</th>
                            <th>Satuan</th>
                            <th>Quantity</th>
                            <th>Harga</th>
                            <th>Total Harga</th>
                            <th>Kasir</th>
                            <th>Customer</th>
                            <th>Supplier</th>
                            <th>Untuk</th>
                            <th class="w-250px">Keterangan</th>
                            <th class="w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    "use strict";

        // Shared variables
        var table;
        var datatable;

        // Class definition
        var KTDatatablesExample = function() {

            // Private functions
            var initDatatable = function() {
                // Set date data order
                const tableRows = table.querySelectorAll('tbody tr');

                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    info: true,
                    order: [],
                    pageLength: 10,
                    ajax: {
                        url: '{{ route('api.laporan-produk') }}',
                        type: 'GET',
                        dataSrc: '',
                        success: function(response) {
                            const numberFormatter = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0,
                            });

                            // Update the elements with formatted values
                            $('#totalNilai').text(numberFormatter.format(response.totalNilai));
                            $('#totalDus').text(response.totalDus);
                            $('#totalPak').text(response.totalPak);
                            $('#totalEceran').text(response.totalEceran);

                            // Update your DataTable with cashflow data
                            datatable.clear().rows.add(response.report).draw();
                        },
                    },
                    columns: [{
                            data: null,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: "product.name"
                        },
                        {
                            data: "unit_type"
                        },
                        {
                            data: "total_qty"
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                var calculatedData = row.total_value / row.total_qty;
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(calculatedData);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            },
                        },
                        {
                            data: "total_value",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            },
                        },
                        {
                            data: "user.name",
                            defaultContent: '-'
                        },
                        {
                            data: "customer.name",
                            defaultContent: '-'
                        },
                        {
                            data: "supplier.name",
                            defaultContent: '-'
                        },
                        {
                            data: "type"
                        },
                        {
                            data: "description"
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(${row.id})">
                                        <i class="ki-duotone ki-trash fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                        Hapus
                                    </button>
                                `;
                            }
                        },
                    ],
                });

                $('#fromDateFilter, #toDateFilter, #warehouseFilter, #userFilter, #forFilter, #productFilter').on('change', function() {
                    // var selectedMonth = $('#selectedMonthFilter').val();
                    var fromDate = $('#fromDateFilter').val();
                    var toDate = $('#toDateFilter').val();
                    var warehouse_id = $('#warehouseFilter').val();
                    var user_id = $('#userFilter').val();
                    var forFilter = $('#forFilter').val();
                    var productFilter = $('#productFilter').val();

                    // Update the URL based on selected filters
                    var url = '{{ route('api.laporan-produk') }}';
                    var params = [];

                    if (fromDate) {
                        params.push('from_date=' + fromDate);
                    }

                    if (toDate) {
                        params.push('to_date=' + toDate);
                    }

                    if (warehouse_id) {
                        params.push('warehouse=' + warehouse_id);
                    }

                    if (user_id) {
                        params.push('user_id=' + user_id);
                    }

                    if (forFilter) {
                        params.push('for=' + forFilter);
                    }

                    if (productFilter) {
                        params.push('product=' + productFilter);
                    }

                    if (params.length > 0) {
                        url += '?' + params.join('&');
                    }

                    // Update the DataTable URL and reload the data
                    datatable.ajax.url(url).load();
                });
            }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Product Purchase Report';
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

        // Delete function
        function deleteRecord(id) {
            console.log(id);
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("produk.laporan.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Terhapus!',
                                    response.message,
                                    'success'
                                );
                                // Reload the datatable
                                datatable.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Terjadi kesalahan saat menghapus data';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire(
                                'Error!',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
</script>
@endpush