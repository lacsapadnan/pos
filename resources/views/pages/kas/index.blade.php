@extends('layouts.dashboard')

@section('title', 'Kas')
@section('menu-title', 'Kas')

@push('addon-style')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endpush

@include('includes.datatable-pagination')

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
    <button type="button" class="top-0 m-2 position-absolute position-sm-relative m-sm-0 end-0 btn btn-icon ms-sm-auto"
        data-bs-dismiss="alert">
        <i class="ki-duotone ki-cross fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
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
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th>No.</th>
                            <th>Faktur</th>
                            <th>Cabang</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Keperluan</th>
                            <th>Jumlah</th>
                            <th>Deskripsi</th>
                            @canany(['update kas', 'hapus kas'])
                            <th>Aksi</th>
                            @endcanany
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
                    processing: true,
                    serverSide: true,
                    info: false,
                    order: [],
                    pageLength: 10,
                    ajax: {
                        url: '{{ route('api.kas') }}',
                        type: 'GET',
                        cache: false
                    },
                    "dom": '<"top"lp>rt<"bottom"lp><"clear">',
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
                            data: "warehouse.name"
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
                        @canany(['update kas', 'hapus kas'])
                        {
                            "data": null,
                            "sortable": false,
                            "render": function(data, type, row, meta) {
                                let actions = `<div class="gap-2 d-flex">`;

                                @can('update kas')
                                actions += `
                                    <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="${row.id}">
                                        <i class="ki-solid ki-pencil"></i>
                                        Edit
                                    </button>
                                `;
                                @endcan

                                @can('hapus kas')
                                actions += `
                                    <form action="{{ url('kas') }}/${row.id}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('delete')
                                        <button type="button" class="btn btn-danger btn-sm delete-btn">
                                            <i class="ki-solid ki-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                `;
                                @endcan

                                actions += `</div>`;
                                return actions;
                            }
                        }
                        @endcanany
                    ],
                });
            }

            // Hook export buttons
            var exportButtons = () => {
                const documentTitle = 'Kas Data Report';
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

            // SweetAlert Delete Confirmation
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('.delete-form');

                Swal.fire({
                    text: "Apakah Anda yakin ingin menghapus data kas ini?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Tidak, batal",
                    customClass: {
                        confirmButton: "btn btn-danger",
                        cancelButton: "btn btn-secondary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Handle kas type selection to show/hide item containers
            $(document).on('change', '#typeSelect', function() {
                const selectedType = $(this).val();
                const incomeContainer = $('#incomeItemContainer');
                const expenseContainer = $('#expenseItemContainer');
                const incomeSelect = $('#kas_income_item_id');
                const expenseSelect = $('#kas_expense_item_id');

                if (selectedType === 'Kas Masuk') {
                    incomeContainer.show();
                    expenseContainer.hide();
                    expenseSelect.val('').trigger('change');
                    // Make income item required and remove required from expense item
                    incomeSelect.attr('required', true);
                    expenseSelect.removeAttr('required');
                } else if (selectedType === 'Kas Keluar') {
                    incomeContainer.hide();
                    expenseContainer.show();
                    incomeSelect.val('').trigger('change');
                    // Make expense item required and remove required from income item
                    expenseSelect.attr('required', true);
                    incomeSelect.removeAttr('required');
                } else {
                    incomeContainer.hide();
                    expenseContainer.hide();
                    incomeSelect.val('').trigger('change');
                    expenseSelect.val('').trigger('change');
                    // Remove required from both when no type is selected
                    incomeSelect.removeAttr('required');
                    expenseSelect.removeAttr('required');
                }
            });

            // Form validation on submit
            $('#kas-form').on('submit', function(e) {
                const selectedType = $('#typeSelect').val();
                const incomeItem = $('#kas_income_item_id').val();
                const expenseItem = $('#kas_expense_item_id').val();

                // Validate based on selected type
                if (selectedType === 'Kas Masuk' && !incomeItem) {
                    e.preventDefault();
                    Swal.fire({
                        text: "Silakan pilih Item Pendapatan terlebih dahulu!",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    return false;
                } else if (selectedType === 'Kas Keluar' && !expenseItem) {
                    e.preventDefault();
                    Swal.fire({
                        text: "Silakan pilih Item Pengeluaran terlebih dahulu!",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    return false;
                }
            });

            // Reset form when modal is closed
            $('#kt_modal_1').on('hidden.bs.modal', function() {
                $('#typeSelect').val('').trigger('change');
                $('#incomeItemContainer').hide();
                $('#expenseItemContainer').hide();
                // Remove required attributes when modal is closed
                $('#kas_income_item_id').removeAttr('required');
                $('#kas_expense_item_id').removeAttr('required');
            });

            // Initialize containers as hidden when modal opens
            $('#kt_modal_1').on('shown.bs.modal', function() {
                $('#incomeItemContainer').hide();
                $('#expenseItemContainer').hide();
            });

                        // Handle edit button click
            $(document).on('click', '.edit-btn', function() {
                const kasId = $(this).data('id');

                // Get kas data via AJAX
                $.get(`{{ url('kas') }}/${kasId}/edit`, function(response) {
                    const kas = response.kas;

                    // Update modal title and form action
                    $('#modal-title').text('Edit data kas');
                    $('#kas-form').attr('action', `{{ url('kas') }}/${kasId}`);
                    $('#method-field').val('PUT').attr('name', '_method');
                    $('#kas-id').val(kasId);

                    // Populate form fields
                    $('input[name="date"]').val(kas.date);
                    $('input[name="invoice"]').val(kas.invoice);
                    $('input[name="amount"]').val(kas.amount);
                    $('input[name="description"]').val(kas.description || '');

                    // Set warehouse if user is master
                    if (response.warehouses && response.warehouses.length > 0) {
                        $('select[name="warehouse_id"]').val(kas.warehouse_id).trigger('change');
                    }

                    // Set type and manually show/hide containers to avoid clearing values
                    $('select[name="type"]').val(kas.type);

                    // Manually handle container visibility and set values
                    if (kas.type === 'Kas Masuk') {
                        $('#incomeItemContainer').show();
                        $('#expenseItemContainer').hide();
                        $('#kas_income_item_id').val(kas.kas_income_item_id).trigger('change');
                        $('#kas_expense_item_id').val('').trigger('change');
                        // Set required attributes for edit mode
                        $('#kas_income_item_id').attr('required', true);
                        $('#kas_expense_item_id').removeAttr('required');
                    } else if (kas.type === 'Kas Keluar') {
                        $('#incomeItemContainer').hide();
                        $('#expenseItemContainer').show();
                        $('#kas_expense_item_id').val(kas.kas_expense_item_id).trigger('change');
                        $('#kas_income_item_id').val('').trigger('change');
                        // Set required attributes for edit mode
                        $('#kas_expense_item_id').attr('required', true);
                        $('#kas_income_item_id').removeAttr('required');
                    }

                    // Show modal
                    $('#kt_modal_1').modal('show');
                });
            });

                        // Reset form when adding new data
            $('button[data-bs-target="#kt_modal_1"]').on('click', function() {
                // Reset form for create mode
                $('#modal-title').text('Tambah data kas');
                $('#kas-form').attr('action', '{{ route('simpan-kas') }}');
                $('#method-field').val('').removeAttr('name');
                $('#kas-id').val('');
                $('#kas-form')[0].reset();

                // Reset dropdowns and containers
                $('#typeSelect').val('').trigger('change');
                $('#incomeItemContainer').hide();
                $('#expenseItemContainer').hide();
                $('#kas_income_item_id').val('').trigger('change');
                $('#kas_expense_item_id').val('').trigger('change');

                // Remove required attributes when creating new data
                $('#kas_income_item_id').removeAttr('required');
                $('#kas_expense_item_id').removeAttr('required');

                // Reset warehouse dropdown if it exists
                if ($('select[name="warehouse_id"]').length) {
                    $('select[name="warehouse_id"]').val('').trigger('change');
                }
            });
        });
</script>
@endpush
