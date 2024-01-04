@extends('layouts.dashboard')

@section('title', 'View Retur')
@section('menu-title', 'View Retur')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="card-body">
            <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
                <div class="table-responsive">
                    <table class="table align-middle border rounded table-row-dashed fs-6 g-5 dataTable no-footer"
                        id="kt_datatable_example">
                        <thead>
                            <tr class="text-start fw-bold fs-7 text-uppercase">
                                <th>#</th>
                                <th>No. Order Penjualan</th>
                                <th>Cabang</th>
                                <th>Kasir</th>
                                <th>Pembeli</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-900 fw-semibold">
                        </tbody>
                    </table>
                    <button id="saveButton" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    @includeIf('pages.retur.modal')
@endsection

@push('addon-script')
    <script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>


    <script>
        $('#saveButton').on('click', function() {
            saveSelectedIds();
        });
        var selectedIds = [];

        function saveSelectedIds() {
            $('.row-checkbox').each(function() {
                if ($(this).prop('checked')) {
                    selectedIds.push($(this).data('id'));
                }
            });
            var sell_id = new URLSearchParams(window.location.search).get('sell_id');
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{ route('konfirmReturn') }}', // Use Blade templating to get the route URL
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                data: {
                    sell_id: sell_id,
                    selectedIds: selectedIds
                },
                success: function(response) {
                    console.log('Save successful:', response);
                    alert('Return confirmed successfully');
                    location.reload();
                },
                error: function(error) {
                    // Handle the error response
                    console.error('Save error:', error);
                }
            });

            selectedIds = [];
        }
    </script>
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
                var sellIdParam = new URLSearchParams(window.location.search).get('sell_id');
                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    "info": false,
                    'order': [],
                    'pageLength': 10,
                    "ajax": {
                        url: '{{ route('api.retur.byorder', ['id' => '__sell_id__']) }}'.replace(
                            '__sell_id__', sellIdParam),
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            "data": null,
                            "render": function(data, type, row) {
                                var isChecked = row.remark === 'verify';
                                return '<input type="checkbox" class="row-checkbox" data-id="' + row
                                    .id + '" ' + (isChecked ? 'checked' : '') + ' ' + (isChecked ?
                                        'disabled' : '') + '>';
                            }
                        },
                        {
                            "data": "sell.order_number"
                        },
                        {
                            "data": "warehouse.name"
                        },
                        {
                            "data": "user.name",
                            defaultContent: '-'
                        },
                        {
                            "data": "sell.customer.name"
                        },
                        {
                            "data": "created_at",
                            "render": function(data, type, row) {
                                return moment(data).format('DD MMMM YYYY');
                            }
                        },
                        {
                            "data": "id",
                            "render": function(data, type, row) {
                                return `
                                <a href="#" class="btn btn-sm btn-primary" onclick="openModal(${data})">Detail</a>
                                `;
                            }
                        },
                    ],
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
                url: '/penjualan-retur/api/data-detail/' + id,
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
                                data: 'qty'
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
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(data.qty * data.price);
                                    formattedPrice = formattedPrice.replace(",00", "");
                                    return formattedPrice;
                                }
                            }
                        ]
                    });

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
