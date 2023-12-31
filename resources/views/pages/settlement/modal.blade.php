<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data settlement</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('settlement.actionStore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label" for="date">Tanggal</label>
                        <div class="input-group" id="kt_td_picker_date_only" data-td-target-input="nearest"
                            data-td-target-toggle="nearest">
                            <input id="kt_td_picker_date_only_input" type="text" class="form-control"
                                data-td-target="#kt_td_picker_date_only" name="input_date" value="{{ date('Y-m-d') }}">
                            <!-- Set the value to today's date and make it readonly -->
                            <span class="input-group-text" data-td-target="#kt_td_picker_date_only"
                                data-td-toggle="datetimepicker">
                                <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span
                                        class="path2"></span></i>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <input name="mutation_id" type="text" class="form-control" hidden>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="amount">Jumlah</label>
                                <input name="amountData" type="text" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label" for="outstanding">Outstanding</label>
                                <input name="outstandingData" type="text" class="form-control" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-4">
                            <div class="mb-4">
                                <label class="form-label" for="from_warehouse">Ke Cabang</label>
                                <select name="from_warehouse" class="form-select form-select-solid"
                                    data-control="select2" id="fromWarehouseSelect" data-dropdown-parent="#kt_modal_1">
                                    @if ($roles === 'master')
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}"
                                                {{ $warehouse->id == auth()->user()->warehouse_id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ auth()->user()->warehouse_id }}" selected>
                                            {{ auth()->user()->warehouse->name }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-4">
                            <div class="mb-4">
                                <label class="form-label" for="from_treasury">Masuk ke</label>
                                <select name="from_treasury" class="form-select form-select-solid"
                                    data-control="select2" id="fromTreasurySelect" data-dropdown-parent="#kt_modal_1">
                                    @if ($roles === 'master')
                                        <option disabled>Pilih Dana</option>
                                        <option value="Kas Bank 1">Kas Bank 1</option>
                                        <option value="Kas Bank 2">Kas Bank 2</option>
                                        <option value="Kas Besar">Kas Besar</option>
                                        <option value="Kas Kecil" selected>Kas Kecil</option>
                                    @else
                                        <option value="Kas Kecil" selected>Kas Kecil</option>
                                    @endif
                                </select>

                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="mb-4">
                                <label class="form-label" for="output_cashier">Kasir Pemasukan</label>
                                <select name="output_cashier" class="form-select form-select-solid"
                                    data-control="select2" id="toCashierSelect" data-dropdown-parent="#kt_modal_1">
                                    @if ($roles === 'master')
                                        @foreach ($cashiers as $cashier)
                                            <option value="{{ $cashier->id }}"
                                                {{ $cashier->id == auth()->id() ? 'selected' : '' }}>
                                                {{ $cashier->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ auth()->user()->id }}">{{ auth()->user()->name }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="name">Jumlah</label>
                        <input name="amount" type="number" class="form-control" placeholder="Masukan jumlah" />
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="name">Deskripsi</label>
                        <input name="description" type="text" class="form-control"
                            placeholder="Masukan deskripsi customer" />
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
