@extends('layouts.dashboard')

@section('title', 'Item Pendapatan Kas')
@section('menu-title', 'Item Pendapatan Kas')

@push('addon-style')
<link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
    type="text/css" />
@endpush

@section('content')
<div class="mt-5 border-0 card card-p-0 card-flush">
    <div class="gap-2 py-5 card-header align-items-center gap-md-5">
        <div class="card-title">
            <!--begin::Search-->
            <div class="my-1 d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                        class="path2"></span></i>
                <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-14"
                    placeholder="Cari item pendapatan" />
            </div>
            <!--end::Search-->
        </div>
        <div class="gap-5 card-toolbar flex-row-fluid justify-content-end">
            @can('simpan kas')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_1">
                Tambah Item
            </button>
            @endcan
        </div>
    </div>
    <div class="card-body">
        @include('components.alert')
        <div id="kt_datatable_example_wrapper dt-bootstrap4 no-footer" class="datatables_wrapper">
            <div class="table-responsive">
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                            <th class="min-w-100px">Nama</th>
                            <th class="min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 fw-semibold">
                        @foreach ($incomeItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>
                                <div class="gap-2 d-flex">
                                    @can('update kas')
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $item->id }}">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('hapus kas')
                                    <form action="{{ route('kas-income-item.destroy', $item->id) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn">Hapus</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" tabindex="-1" id="editModal{{ $item->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title">Edit Item Pendapatan</h3>
                                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2"
                                            data-bs-dismiss="modal" aria-label="Close">
                                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                        </div>
                                    </div>
                                    <form action="{{ route('kas-income-item.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-10">
                                                <label for="name" class="form-label">Nama Item</label>
                                                <input type="text" class="form-control" name="name"
                                                    value="{{ $item->name }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Item Pendapatan</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="{{ route('kas-income-item.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-10">
                        <label for="name" class="form-label">Nama Item</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
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
                });
            }

            // Hook export buttons

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
                    // exportButtons();
                    handleSearchDatatable();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTDatatablesExample.init();
        });

        // SweetAlert Delete Confirmation
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.delete-form');

                Swal.fire({
                    text: "Apakah Anda yakin ingin menghapus item ini?",
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
        });
</script>
@endpush