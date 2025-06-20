@extends('layouts.dashboard')
@section('menu-title', 'Draft Pindah Stok')


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle rounded border table-row-dashed fs-6 g-5 dataTable no-footer"
                    id="kt_datatable_example">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Kasir</th>
                            <th>Dari Gudang</th>
                            <th>Ke Gudang</th>
                            <th>Tanggal Draft</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Draft Pindah Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <!-- Detail data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('addon-script')
<script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#kt_datatable_example').DataTable({
            "info": true,
            'pageLength': 10,
            "ajax": {
                "url": "{{ route('api.pindah-stok-draft') }}",
                "type": "GET",
                "dataSrc": "",
                "error": function(xhr, error, code) {
                    Swal.fire({
                        title: 'Error Loading Data',
                        text: 'Failed to load draft data. Check console for details.',
                        icon: 'error',
                        confirmButtonText: 'Ok, mengerti!',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            "columns": [{
                    "data": null,
                    "render": function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    "data": "user.name",
                    "defaultContent": "N/A"
                },
                {
                    "data": "from_warehouse.name",
                    "defaultContent": "N/A"
                },
                {
                    "data": "to_warehouse.name",
                    "defaultContent": "N/A"
                },
                {
                    "data": "created_at",
                    "render": function(data, type, row) {
                        if (data) {
                            return new Date(data).toLocaleDateString('id-ID');
                        }
                        return "N/A";
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = '<div class="gap-2 d-flex">';

                        // Detail button
                        actions += '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="' + row.id + '">';
                        actions += '<i class="fas fa-eye"></i> Detail</button>';

                        actions += '<a href="{{ url("pindah-stok-draft") }}/' + row.id + '/edit" class="btn btn-sm btn-warning">';
                        actions += '<i class="fas fa-edit"></i> Edit</a>';

                        actions += '<form action="{{ url("pindah-stok-draft") }}/' + row.id + '/complete" method="POST" style="display: inline;">';
                        actions += '@csrf';
                        actions += '<button type="button" class="btn btn-sm btn-success btn-complete" data-id="' + row.id + '">';
                        actions += '<i class="fas fa-check"></i> Selesaikan</button>';
                        actions += '</form>';

                        actions += '<form action="{{ url("pindah-stok-draft") }}/' + row.id + '" method="POST" style="display: inline;">';
                        actions += '@csrf @method("DELETE")';
                        actions += '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' + row.id + '">';
                        actions += '<i class="fas fa-trash"></i> Hapus</button>';
                        actions += '</form>';

                        actions += '</div>';
                        return actions;
                    }
                }
            ],
        });

        // Debug: Log when DataTable is initialized
        console.log('DataTable initialized for draft send stock');

        // Detail button click
        $(document).on('click', '.btn-detail', function() {
            var id = $(this).data('id');
            console.log('Detail button clicked for ID:', id);
            showDetail(id);
        });

        // Complete button click
        $(document).on('click', '.btn-complete', function() {
            var id = $(this).data('id');
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Konfirmasi Penyelesaian',
                text: 'Apakah Anda yakin ingin menyelesaikan draft ini? Stok akan dipindahkan secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, selesaikan!',
                cancelButtonText: 'Tidak, batal',
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Delete button click
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus draft ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Tidak, batal',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        function showDetail(id) {
            $.ajax({
                url: "{{ url('pindah-stok-draft') }}/" + id,
                type: 'GET',
                success: function(data) {
                    console.log('Detail data received:', data);
                    var tbody = $('#detailTableBody');
                    tbody.empty();

                    data.forEach(function(item) {
                        var row = '<tr>';
                        row += '<td>' + item.product.name + '</td>';
                        row += '<td>' + item.unit.name + '</td>';
                        row += '<td>' + item.quantity + '</td>';
                        row += '</tr>';
                        tbody.append(row);
                    });

                    $('#detailModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Detail AJAX Error:', xhr, status, error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat detail data.',
                        icon: 'error',
                        confirmButtonText: 'Ok, mengerti!',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        }
    });
</script>
@endpush