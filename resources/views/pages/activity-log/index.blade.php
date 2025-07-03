@extends('layouts.dashboard')

@section('title', 'Activity Log')

@section('content')
<div class="card">
    <div class="pt-6 border-0 card-header">
        <div class="card-title">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-1 card-label fw-bold fs-3">Activity Log</span>
                <span class="mt-1 text-muted fw-semibold fs-7">Track user activities and system changes</span>
            </h3>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>Filter
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                    </div>
                    <div class="border-gray-200 separator"></div>
                    <div class="px-7 py-5">
                        <div class="mb-5">
                            <label class="form-label fw-semibold">Log Category:</label>
                            <select class="form-select" id="filter_log_name">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-semibold">User:</label>
                            <select class="form-select" id="filter_user">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-semibold">Model Type:</label>
                            <select class="form-select" id="filter_subject_type">
                                <option value="">All Types</option>
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-semibold">Date Range:</label>
                            <input class="form-control" placeholder="Pick date range" id="filter_date_range" />
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="px-6 btn btn-light btn-active-light-primary fw-semibold me-2"
                                id="reset_filter">Reset</button>
                            <button type="submit" class="px-6 btn btn-primary fw-semibold"
                                id="apply_filter">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="py-4 card-body">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="activity_log_table">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Date & Time</th>
                    <th class="min-w-125px">User</th>
                    <th class="min-w-125px">Activity</th>
                    <th class="min-w-125px">Subject</th>
                    <th class="min-w-125px">Category</th>
                    <th class="min-w-100px text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                <!-- Data will be loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('addon-script')
<script>
    "use strict";

    // Class definition
    var KTActivityLogTable = function() {
        // Shared variables
        var table;
        var datatable;

        // Private functions
        var initDatatable = function() {
            // Init datatable
            datatable = $(table).DataTable({
                "info": false,
                'order': [[0, 'desc']],
                'pageLength': 10,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('activity-log.data') }}",
                data: function(d) {
                    d.log_name = $('#filter_log_name').val();
                    d.causer_id = $('#filter_user').val();
                    d.subject_type = $('#filter_subject_type').val();

                    let dateRange = $('#filter_date_range').val();
                    if (dateRange) {
                        let dates = dateRange.split(' to ');
                        d.from_date = dates[0];
                        d.to_date = dates[1];
                    }
                }
            },
            columns: [
                {
                    data: 'formatted_date',
                    name: 'created_at'
                },
                {
                    data: 'causer_name',
                    name: 'causer_id',
                    render: function(data) {
                        return data || 'System';
                    }
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'subject_name',
                    name: 'subject_id',
                    render: function(data, type, row) {
                        return data || 'N/A';
                    }
                },
                {
                    data: 'log_name',
                    name: 'log_name'
                },
                {
                        data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="flex-shrink-0 d-flex justify-content-end">
                                <a href="/activity-log/${data}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="View Details">
                                <i class="ki-duotone ki-eye fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>
                            @can('hapus activity log')
                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-activity" data-id="${data}" title="Delete">
                                <i class="ki-duotone ki-trash fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </button>
                            @endcan
                        </div>`;
                    }
                }
                ]
            });
        }

        var initFilters = function() {
            // Initialize date range picker
            $("#filter_date_range").daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $("#filter_date_range").on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $("#filter_date_range").on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Load log names for filter
            $.ajax({
                url: "{{ route('activity-log.log-names') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    let options = '<option value="">All Categories</option>';
                    $.each(data, function(index, value) {
                        options += '<option value="' + value + '">' + value + '</option>';
                    });
                    $('#filter_log_name').html(options);
                }
            });

            // Load subject types for filter
            $.ajax({
                url: "{{ route('activity-log.subject-types') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    let options = '<option value="">All Types</option>';
                    $.each(data, function(index, value) {
                        options += '<option value="' + value + '">' + value + '</option>';
                    });
                    $('#filter_subject_type').html(options);
                }
        });

        // Apply filters
        $('#apply_filter').on('click', function() {
                datatable.ajax.reload();
        });

        // Reset filters
        $('#reset_filter').on('click', function() {
            $('#filter_log_name').val('');
            $('#filter_user').val('');
            $('#filter_subject_type').val('');
            $('#filter_date_range').val('');
                datatable.ajax.reload();
        });
        }

        var handleDeleteRows = function() {
        // Delete activity log
        $(document).on('click', '.delete-activity', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                            url: '/activity-log/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                                                    success: function(response) {
                            if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                    response.message,
                                'success'
                            );
                                datatable.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Something went wrong.';
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
        });
        }

        // Public methods
        return {
            init: function() {
                table = document.querySelector('#activity_log_table');

                if (!table) {
                    return;
                }

                initDatatable();
                initFilters();
                handleDeleteRows();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTActivityLogTable.init();
    });
</script>
@endpush
