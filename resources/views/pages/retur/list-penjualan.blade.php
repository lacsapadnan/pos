@extends('layouts.dashboard')

@section('title', 'Retur')
@section('menu-title', 'Retur Barang')

@push('addon-style')
    <link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

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
                                <th>Customer</th>
                                <th>Cabang</th>
                                <th>Total Pembelian</th>
                                <th class="min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('addon-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

    {{-- Datatables --}}
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
                    "columns": [{
                            "data": "order_number"
                        },
                        {
                            "data": "customer.name"
                        },
                        {
                            "data": "warehouse.name"
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
                            "data": "id",
                            "render": function(data, type, row) {
                                return `
                                <a href="/retur/${data}" class="btn btn-sm btn-warning">Retur</a>
                                `;
                            }
                        },

                    ],
                });

                $(table).on('click', '.btn-submit', function() {
                    var rowData = datatable.row($(this).closest('tr')).data();
                    var productId = $(this).data('product-id');
                    var quantityDus = $(this).closest('tr').find('input[name="quantity_dus"]').val();
                    var quantityPak = $(this).closest('tr').find('input[name="quantity_pak"]').val();
                    var quantityEceran = $(this).closest('tr').find('input[name="quantity_eceran"]').val();
                    var diskonDus = $(this).closest('tr').find('input[name="diskon_dus"]').val();
                    var diskonPak = $(this).closest('tr').find('input[name="diskon_pak"]').val();
                    var diskonEceran = $(this).closest('tr').find('input[name="diskon_eceran"]').val();

                    var inputRequest = {
                        product_id: productId,
                        quantity_dus: quantityDus,
                        quantity_pak: quantityPak,
                        quantity_eceran: quantityEceran,
                        diskon_dus: diskonDus,
                        diskon_pak: diskonPak,
                        diskon_eceran: diskonEceran,
                    };

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route('penjualan.addCart') }}',
                        type: 'POST',
                        data: inputRequest,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // reload page
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                        }
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
                    handleSearchDatatable();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });
    </script>
@endpush
