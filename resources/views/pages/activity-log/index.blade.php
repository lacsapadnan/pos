@extends('layouts.dashboard')

@section('title', 'Activity Log')
@section('menu-title', 'Activity Log')

@push('addon-style')
<link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@endpush

@include('includes.datatable-pagination')

@section('content')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <!--begin::Search-->
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                        class="path2"></span></i>
                <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-14"
                    placeholder="Search activity log">
            </div>
            <!--end::Search-->

            <div class="ms-2">
                <select id="logNameFilter" class="form-select" data-control="select2"
                    data-placeholder="Select Category">
                    <option value="">All Categories</option>
                </select>
            </div>

            <div class="ms-2">
                <select id="userFilter" class="form-select" data-control="select2" data-placeholder="Select User">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ms-2">
                <select id="subjectTypeFilter" class="form-select" data-control="select2"
                    data-placeholder="Select Type">
                    <option value="">All Types</option>
                </select>
            </div>

            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-calendar fs-1 position-absolute ms-4"></i>
                <input type="date" id="fromDateFilter" class="form-control form-control-solid ms-2"
                    data-kt-filter="date" placeholder="From Date">
                <input type="date" id="toDateFilter" class="form-control form-control-solid ms-2" data-kt-filter="date"
                    placeholder="To Date">
            </div>
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            <!--begin::Export dropdown-->
            <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                Export Data
            </button>
            <!--begin::Menu-->
            <div id="kt_datatable_example_export_menu"
                class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px"
                data-kt-menu="true">
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <a href="#" class="px-3 menu-link" data-kt-export="excel">
                        Export as Excel
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
                <table class="table align-middle border table-row-dashed fs-6 g-5" id="kt_datatable_example">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase">
                            <th>Date</th>
                            <th>User</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Subject Type</th>
                            <th>Subject</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    "use strict";

var KTDatatablesExample = function() {
    var table;
    var datatable;

    // Private functions
    var initDatatable = function() {
        datatable = $(table).DataTable({
            processing: true,
            serverSide: true,
            order: [[0, 'desc']], // Order by date desc by default
            pageLength: 10,
            ajax: {
                url: '{{ route('activity-log.data') }}',
                type: 'GET',
            },
            columns: [
                {
                    data: 'formatted_date',
                    name: 'created_at'
                },
                {
                    data: 'causer_name',
                    name: 'causer_name'
                },
                { data: 'log_name' },
                { data: 'description' },
                { data: 'subject_type' },
                { data: 'subject_name' },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            dom: '<"top"lp>rt<"bottom"lp><"clear">'
        });

        // Handle filter changes
        $('#fromDateFilter, #toDateFilter, #logNameFilter, #userFilter, #subjectTypeFilter').on('change', function() {
            var fromDate = $('#fromDateFilter').val();
            var toDate = $('#toDateFilter').val();
            var logName = $('#logNameFilter').val();
            var userId = $('#userFilter').val();
            var subjectType = $('#subjectTypeFilter').val();

            var url = '{{ route('activity-log.data') }}';
            var params = [];

            if (fromDate) params.push('from_date=' + fromDate);
            if (toDate) params.push('to_date=' + toDate);
            if (logName) params.push('log_name=' + logName);
            if (userId) params.push('causer_id=' + userId);
            if (subjectType) params.push('subject_type=' + subjectType);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            datatable.ajax.url(url).load();
        });

        // Load log names for filter
        $.get('{{ route('activity-log.log-names') }}', function(data) {
            var select = $('#logNameFilter');
            data.forEach(function(logName) {
                select.append(new Option(logName, logName));
            });
        });

        // Load subject types for filter
        $.get('{{ route('activity-log.subject-types') }}', function(data) {
            var select = $('#subjectTypeFilter');
            data.forEach(function(type) {
                select.append(new Option(type, type));
            });
        });
    }

    // Search Datatable
    var handleSearchDatatable = function() {
        const filterSearch = document.querySelector('[data-kt-filter="search"]');
        filterSearch.addEventListener('keyup', function(e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Export buttons
    var exportButtons = function() {
        const documentTitle = 'Activity Log Report';
        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: documentTitle,
                    action: function(e, dt, button, config) {
                        KTApp.showPageLoading();

                        var searchValue = dt.search();
                        var fromDate = $('#fromDateFilter').val();
                        var toDate = $('#toDateFilter').val();
                        var logName = $('#logNameFilter').val();
                        var userId = $('#userFilter').val();
                        var subjectType = $('#subjectTypeFilter').val();

                        var filters = {
                            export: 1,
                            search: { value: searchValue },
                            from_date: fromDate,
                            to_date: toDate,
                            log_name: logName,
                            causer_id: userId,
                            subject_type: subjectType
                        };

                        $.ajax({
                            url: '{{ route('activity-log.data') }}',
                            type: 'GET',
                            data: filters,
                            success: function(response) {
                                var tempDiv = $('<div style="display:none;"></div>');
                                var tempTable = $('<table></table>').appendTo(tempDiv);
                                $('body').append(tempDiv);

                                var tempDT = tempTable.DataTable({
                                    data: response,
                                    columns: [
                                        { data: "formatted_date" },
                                        { data: "causer_name" },
                                        { data: "log_name" },
                                        { data: "description" },
                                        { data: "subject_type" },
                                        { data: "subject_name" }
                                    ],
                                    destroy: true
                                });

                                $.fn.dataTable.ext.buttons.excelHtml5.action.call(
                                    {processing: function(){}, exportOptions: config.exportOptions},
                                    e, tempDT, button, config
                                );

                                tempDT.destroy();
                                tempDiv.remove();
                                KTApp.hidePageLoading();
                            },
                            error: function(xhr, status, error) {
                                console.error('Export error:', error);
                                KTApp.hidePageLoading();
                                Swal.fire({
                                    text: "Failed to export data: " + error,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        });
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle,
                    action: function(e, dt, button, config) {
                        // Similar implementation as excel export
                        // ... (implement PDF export similar to excel)
                    }
                }
            ]
        }).container().appendTo($('#kt_datatable_example_buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);
                target.click();
            });
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
            exportButtons();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTDatatablesExample.init();
});
</script>
@endpush
