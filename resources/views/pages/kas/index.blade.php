@extends('layouts.dashboard')

@section('title', 'Kas')
@section('menu-title', 'Kas')

@push('addon-style')
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    {{-- session success --}}
    @if (session()->has('success'))
        <!--begin::Alert-->
        <div class="p-5 mb-10 alert alert-primary d-flex align-items-center">
            <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-primary">Sukses</h4>
                <span>{{ session()->get('success') }}</span>
            </div>
            <button type="button"
                class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-2x text-primary"><span class="path1"></span><span
                        class="path2"></span></i>
            </button>
        </div>
    @endif
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data customer">
                </div>
                <!--end::Search-->
            </div>
            <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
                <!--begin::Export dropdown-->
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Export Data
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_1">
                    Tambah Data
                </button>
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
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                                <th>No.</th>
                                <th>Faktur</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Keperluan</th>
                                <th>Jumlah</th>
                                <th>Deskripsi</th>
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
    @includeIf('pages.kas.modal')
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
                        url: '{{ route('api.kas') }}',
                        type: 'GET',
                        dataSrc: '',
                    },
                    "columns": [{
                            "data": null,
                            "sortable": false,
                            "render": function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: "invoice"
                        },
                        {
                            data: "date",
                            render: function(data, type, row) {
                                return moment(data).format('DD MMMM YYYY');
                            }
                        },
                        {
                            data: "type"
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                if (row.kas_income_item_id) {
                                    return row.kas_income_item.name;
                                } else if (row.kas_expense_item_id) {
                                    return row.kas_expense_item.name;
                                } else {
                                    return "";
                                }
                            }
                        },
                        {
                            data: "amount",
                            render: function(data, type, row) {
                                return 'Rp. ' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                                    ".");
                            }
                        },
                        {
                            data: "description",
                            render: function(data, type, row) {
                                if (data == null) {
                                    return "Tidak ada deskripsi";
                                } else {
                                    return data;
                                }
                            }
                        },
                        {
                            "data": null,
                            "sortable": false,
                            "render": function(data, type, row, meta) {
                                return `
                                    <form action="{{ url('kas') }}/${row.id}" method="POST" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="ki-solid ki-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                `;
                            }
                        }
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
       $(document).ready(function() {
    // Initialize the first Select2
    $('#typeSelect').select2({
        // Select2 options...
    });

    // Initialize the second Select2 with dynamic option creation
    $('#otherSelect').select2({
        tags: true,
        createTag: function(params) {
            return {
                id: params.term,
                text: params.term,
                isNew: true
            };
        }
    });

    // Listen for changes in the first Select2
    $('#typeSelect').on('change', function() {
        var selectedValue = $(this).val();

        // Show or hide the second Select2 based on the selected value
        if (selectedValue === 'Kas Masuk' || selectedValue === 'Kas Keluar') {
            $('#otherSelectContainer').show();

            // Populate options for the second Select2 based on the selected value
            populateOptions(selectedValue);
        } else {
            $('#otherSelectContainer').hide();
        }
    });

    // Listen for Select2 select event
    $('#otherSelect').on('select2:select', function(e) {
        var selectedValue = $('#typeSelect').val();
        var selectedOption = $(e.params.data.element);

        // Check if the selected option is a new tag
        if (selectedOption.data('isNew')) {
            // Make an AJAX request to save the new item
            $.ajax({
                url: '/kas', // Update the URL to your route/controller's store method
                method: 'POST',
                data: {
                    type: selectedValue,
                    other: selectedOption.val()
                },
                success: function(response) {
                    // Append the newly created option to the second Select2
                    var otherSelect = $('#otherSelect');
                    otherSelect.append($('<option></option>').attr('value', response.data.id).text(response.data.name));

                    // Select the newly created option
                    otherSelect.val(response.data.id).trigger('change');
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('Error:', errorThrown);
                }
            });
        }
    });

    // Function to populate options for the second Select2 based on the selected value
    function populateOptions(selectedValue) {
        var url = '/kas-income/api/data'; // Default URL for Kas Masuk
        if (selectedValue === 'Kas Keluar') {
            url = '/kas-expense/api/data'; // URL for Kas Keluar
        }

        // Make an AJAX request to retrieve the existing data for the Select2
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                var otherSelect = $('#otherSelect');

                // Clear existing options
                otherSelect.empty();

                // Append the retrieved options to the Select2
                response.forEach(function(option) {
                    otherSelect.append($('<option></option>').attr('value', option.id).text(option.name));
                });
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error:', errorThrown);
            }
        });
    }
});

    </script>
@endpush
