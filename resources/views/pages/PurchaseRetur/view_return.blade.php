@extends('layouts.dashboard')

@section('title', 'View Retur Pembelian')
@section('menu-title', 'View Retur Pembelian')

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
                                <th>Invoice Penjualan</th>
                                <th>Cabang</th>
                                <th>Kasir</th>
                                <th>Supplier</th>
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
    @includeIf('pages.PurchaseRetur.modal')
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
                // Periksa apakah checkbox tidak disabled sebelum menambahkan data-id
                if (!$(this).prop('disabled') && $(this).prop('checked')) {
                    selectedIds.push($(this).data('id'));
                }
            });

            if (selectedIds.length === 0) {
                alert('Please select at least one item before saving.');
                return; // Tidak melanjutkan proses jika array kosong
            }

            var purchase_id = new URLSearchParams(window.location.search).get('purchase_id');
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            console.log(selectedIds);
            $.ajax({
                url: '{{ route('konfirmReturnPembelian') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                data: {
                    purchase_id: purchase_id,
                    selectedIds: selectedIds
                },
                success: function(response) {
                    alert('Return confirmed successfully');
                    location.reload();
                },
                error: function(error) {
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
                var purchaseParam = new URLSearchParams(window.location.search).get('purchase_id');

                // Init datatable --- more info on datatables: https://datatables.net/manual/
                datatable = $(table).DataTable({
                    "info": true,
                    'order': [],
                    'pageLength': 10,
                    "ajax": {
                        url: '{{ route('api.returPurchase.byorder', ['id' => '__purchase_id__']) }}'
                            .replace(
                                '__purchase_id__', purchaseParam),
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
                            "data": "purchase.invoice"
                        },
                        {
                            "data": "warehouse.name"
                        },
                        {
                            "data": "user.name",
                            defaultContent: '-'
                        },
                        {
                            "data": "purchase.supplier.name"
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

            // Public methods
            return {
                init: function() {
                    table = document.querySelector('#kt_datatable_example');

                    if (!table) {
                        return;
                    }

                    initDatatable();
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
                url: '/pembelian-retur/api/data-detail/' + id,
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
                                data: 'qty'
                            },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    var formattedPrice = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(data.price * data.qty);
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
