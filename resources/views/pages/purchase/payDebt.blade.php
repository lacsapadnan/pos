@extends('layouts.dashboard')

@section('title', 'Pembayaran Hutang')
@section('menu-title', 'Pembayaran Hutang')

@push('addon-style')
    <link href="{{ URL::asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        ::-webkit-scrollbar-thumb {
            -webkit-border-radius: 10px;
            border-radius: 10px;
            background: rgba(192, 192, 192, 0.3);
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
            background-color: #818B99;
        }

        .dataTables_scrollBody {
            transform: rotateX(180deg) !important;
        }

        .dataTables_scrollBody::-webkit-scrollbar {
            height: 16px !important;
            ;
        }

        .dataTables_scrollBody table {
            transform: rotateX(180deg) !important;
            ;
        }
    </style>
@endpush

@section('content')
    @include('components.alert');
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <div class="mt-3">
            <form id="form1">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="inputEmail3" class="col-form-label">Tanggal Terima</label>
                            <div class="input-group" id="kt_td_picker_date_only" data-td-target-input="nearest"
                                data-td-target-toggle="nearest">
                                <input id="kt_td_picker_date_only_input" type="text" class="form-control"
                                    data-td-target="#kt_td_picker_date_only" name="reciept_date" value="{{ date('Y-m-d') }}"
                                    disabled />
                                <span class="input-group-text" data-td-target="#kt_td_picker_date_only"
                                    data-td-toggle="datetimepicker">
                                    <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span
                                            class="path2"></span></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="inputEmail3" class="col-form-label">No. Order</label>
                            <input id="order_number" type="text" name="order_number" class="form-control"
                                value="{{ $debt->orderNumber }}" readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="inputEmail3" class="col-form-label">No. Faktur Supplier</label>
                            <input id="invoice" type="text" name="invoice" class="form-control"
                                placeholder="Masukan nomor faktur" value="{{ $debt->invoice }}" readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="inputEmail3" class="col-form-label">Supplier</label>
                            <input id="invoice" type="text" name="invoice" class="form-control"
                                placeholder="Masukan nomor faktur" value="{{ $debt->supplier->name }}" readonly />
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="gap-2 py-5 card-header align-items-center gap-md-5">
            <div class="card-title">
                <!--begin::Search-->
                <div class="my-1 d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-4"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-filter="search"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari data retur"
                        id="searchInput">
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
                            <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase">
                                <th></th>
                                <th class="min-w-100px">Nama Barang</th>
                                <th>Jumlah Retur</th>
                                <th>Unit</th>
                                <th>Total Harga Retur</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-900 fw-semibold">
                            @forelse ($purchaseReturs as $purchaseRetur)
                                @foreach ($purchaseRetur->details ?? [] as $details)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox"
                                                    value="{{ $details->id }}" />
                                            </div>
                                        </td>
                                        <td>{{ $details->product->name }}</td>
                                        <td>{{ $details->qty }}</td>
                                        <td>{{ $details->unit->name }}</td>
                                        <td>{{ number_format($details->price) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>No purchase returns found</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4 row">
            <div class="col-md-12">
                <form action="{{ route('bayar-hutang') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="total_hutang" class="col-form-label">Total Hutang</label>
                        <input id="total_hutang" type="text" name="total_hutang" class="form-control"
                            value="{{ number_format($debt->grand_total - $debt->pay) }}" readonly />
                    </div>
                    <div class="mb-3">
                        <label for="retur" class="col-form-label">Retur</label>
                        <input id="retur_value" type="text" name="retur" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label for="total_after_retur" class="col-form-label">Total Hutang setelah retur</label>
                        <input id="total_after_retur" type="text" name="total_after_retur" class="form-control"
                            readonly />
                    </div>
                    <div class="mb-3">
                        <label for="bayar_hutang" class="col-form-label">Bayar Hutang</label>
                        <input id="bayar_hutang" type="number" name="bayar_hutang" class="form-control" />
                    </div>
                    <div id="selected_returs"></div>
                    <input type="hidden" name="debt_id" value="{{ $debt->id }}">
                    <div class="w-full mb-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('addon-script')
    <script src="{{ URL::asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        $("#kt_datatable_example").DataTable();
    </script>
    <script>
        $(document).ready(function() {
            let totalHutang = parseFloat('{{ $debt->grand_total - $debt->pay }}'.replace(/,/g, ''));

            function calculateRetur() {
                let totalRetur = 0;
                let selectedReturs = $('#selected_returs');

                // Clear any existing hidden inputs
                selectedReturs.empty();

                // Loop through each checked checkbox
                $('#kt_datatable_example input[type="checkbox"]:checked').each(function() {
                    let price = parseFloat($(this).closest('tr').find('td:nth-child(5)').text().replace(
                        /,/g, ''));
                    let detailsId = $(this).val();

                    totalRetur += price;

                    // Add hidden input for each selected item
                    selectedReturs.append(
                        `<input type="hidden" name="selected_returs[]" value="${detailsId}">`);
                });

                // Update the retur input
                $('#retur_value').val(totalRetur.toLocaleString());

                // Calculate total debt after retur
                let totalAfterRetur = totalHutang - totalRetur;
                $('#total_after_retur').val(totalAfterRetur.toLocaleString());
            }

            // Add event listener to checkboxes
            $('#kt_datatable_example').on('change', 'input[type="checkbox"]', function() {
                calculateRetur();
            });
        });
    </script>
@endpush
