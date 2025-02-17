@extends('layouts.dashboard')

@section('title', 'Penjualan')
@section('menu-title', 'Penjualan')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@include('includes.datatable-pagination')

@section('content')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data penjualan">
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
                        <input type="text" id="warehouseFilter" class="form-control" value="{{ auth()->user()->warehouse_id }}" disabled hidden>
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
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-calendar fs-1 position-absolute ms-4"></i>
                    <input type="date" id="fromDateFilter" class="form-control form-control-solid ms-2"
                        data-kt-filter="date" placeholder="Dari Tanggal">
                    <input type="date" id="toDateFilter" class="form-control form-control-solid ms-2"
                        data-kt-filter="date" placeholder="Ke Tanggal">
                </div>
            </div>
            <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
                <!--begin::Export dropdown-->
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Export Data
                </button>
                @can('simpan penjualan')
                    <a href="{{ route('penjualan.create') }}" type="button" class="btn btn-primary">
                        Tambah Penjualan
                    </a>
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
                    <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_example">
                        <thead>
                            <tr class="text-start fw-bold fs-7 text-uppercase">
                                <th>No. Order</th>
                                <th>Kasir</th>
                                <th>Customer</th>
                                <th>Cabang</th>
                                <th>Metode Pembayaran</th>
                                <th>Cash</th>
                                <th>Transfer</th>
                                <th>Total Penjualan</th>
                                <th>Status</th>
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
    @includeIf('pages.sell.modal')
    @includeIf('pages.sell.modal-password')
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
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                    "ajax": {
                        url: '{{ route('api.penjualan') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "dom": '<"top"lp>rt<"bottom"lp><"clear">',
                    "columns": [{
                            "data": "order_number"
                        },
                        {
                            "data": "cashier.name",
                            render: function(data, type, row) {
                                if (data == null) {
                                    return `<span class="badge badge-light-danger">Tidak ada datas</span>`;
                                } else {
                                    return data;
                                }
                            }
                        },
                        {
                            "data": "customer.name"
                        },
                        {
                            "data": "warehouse.name"
                        },
                        {
                            "data": "payment_method"
                        },
                        {
                            "data": "cash",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            }
                        },
                        {
                            "data": "transfer",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            }
                        },
                        {
                            "data": "grand_total",
                            render: function(data, type, row) {
                                var formattedPrice = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(data);
                                formattedPrice = formattedPrice.replace(",00", "");
                                return formattedPrice;
                            }
                        },
                        {
                            "data": "status",
                            "render": function(data, type, row) {
                                if (data == 'piutang') {
                                    return `<span class="badge badge-light-danger">Piutang</span>`;
                                } else {
                                    return `<span class="badge badge-light-primary">Lunas</span>`;
                                }
                            }
                        },
                        {
                            "data": "id",
                            "render": function(data, type, row) {
                                return `
                                    <a href="#" class="btn btn-sm btn-primary" onclick="openModal(${data})">Detail</a>
                                    <button class="btn btn-sm btn-success" onclick="openPasswordModal(${data})">Print</button>
                                    @can('hapus penjualan')
                                        <form id="deleteForm_${data}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id" value="${data}">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(${data})">Delete</button>
                                        </form>
                                    @endcan
                                `;
                            }
                        }
                    ],
                });

                $('#fromDateFilter, #toDateFilter, #warehouseFilter, #userFilter').on('change', function() {
                    var fromDate = $('#fromDateFilter').val();
                    var toDate = $('#toDateFilter').val();
                    var warehouse_id = $('#warehouseFilter').val();
                    var user_id = $('#userFilter').val();

                    // Update the URL based on selected filters
                    var url = '{{ route('api.penjualan') }}';
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

                    if (params.length > 0) {
                        url += '?' + params.join('&');
                    }

                    // Load data with updated URL
                    datatable.ajax.url(url).load();
                });
            }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Customer Orders Report';
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
        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin menghapus data ini?',
                text: 'Data yang terhapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus data!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteRecord(id);
                    Swal.fire(
                        'Terhapus!',
                        'Data penjualan terhapus.',
                        'success'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                }
            });
        }

        function deleteRecord(id) {
            // Use AJAX for asynchronous delete request
            $.ajax({
                url: "{{ url('penjualan') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error(error);
                }
            });
        }

        function openPasswordModal(data) {
            $('#passwordModal').modal('show');
            $('#submitPasswordBtn').attr('onclick', `checkMasterUserPassword(${data})`);
        }

        function checkMasterUserPassword(data) {
            var userId = document.getElementById('user_master').value;
            var masterUserPassword = document.getElementById('masterUserPassword').value;

            // Make an AJAX request to validate the master user's password
            $.ajax({
                url: '/validate-master-password', // Update the URL to your Laravel route
                method: 'POST',
                data: {
                    user_id: userId,
                    password: masterUserPassword
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        var url = '/penjualan/print/' + data;
                        window.open(url, '_blank');
                    } else {
                        // Password is incorrect, show an error message
                        alert('Invalid Master User Password. Please try again.');
                    }
                },
                error: function(error) {
                    console.error('Error validating master user password:', error);
                    // Handle error (e.g., show an error message to the user)
                }
            });
        }
    </script>
    <script>
        var datatable;

        function openModal(id) {
            // Clear the table body
            $('#kt_datatable_detail tbody').empty();

            // Check if DataTable instance exists and destroy it
            if ($.fn.DataTable.isDataTable('#kt_datatable_detail')) {
                datatable.destroy();
            }

            // Send a request to fetch the sell details for the given ID
            $.ajax({
                url: '/penjualan/' + id,
                method: 'GET',
                success: function(response) {
                    // Initialize the DataTable on the table
                    datatable = $('#kt_datatable_detail').DataTable({
                        data: response,
                        columns: [{
                                data: 'product.name'
                            },
                            {
                                data: 'unit.name'
                            },
                            {
                                data: 'quantity'
                            },
                            {
                                data: 'convert_unit',
                                defaultContent: '-'
                            },
                            {
                                data: 'price',
                                render: function(data, type, row) {
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(data);
                                    formattedPrice = formattedPrice.replace(",00", "");
                                    return formattedPrice;
                                }
                            },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    var subtotal = data.quantity * data.price;
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(subtotal);
                                    formattedPrice = formattedPrice.replace(",00", "");
                                    return formattedPrice;
                                }
                            },
                            {
                                data: 'diskon',
                                render: function(data, type, row) {
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(data);
                                    formattedPrice = formattedPrice.replace(",00", "");
                                    return formattedPrice;
                                }
                            },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    var total = data.quantity * data.price - data.diskon;
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(total);
                                    formattedPrice = formattedPrice.replace(",00", "");
                                    return formattedPrice;
                                }
                            }
                        ]
                    });

                    // Calculate grand total
                    var grandTotal = response.reduce((acc, item) => {
                        return acc + (item.quantity * item.price - item.diskon);
                    }, 0);
                    var formattedGrandTotal = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(grandTotal).replace(",00", "");

                    // Display grand total
                    $('#kt_datatable_detail').after(`<h2>Total Penjualan: ${formattedGrandTotal}</h2>`);

                    // Open the modal
                    $('#kt_modal_1').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(error); // Handle the error appropriately
                }
            });
        }
    </script>
@endpush
