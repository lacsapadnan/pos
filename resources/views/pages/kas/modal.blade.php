<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Tambah data kas</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form id="kas-form" action="{{ route('simpan-kas') }}" method="post">
                    @csrf
                    <input type="hidden" id="method-field" name="_method" value="">
                    <input type="hidden" id="kas-id" name="kas_id" value="">
                    <div class="mb-4">
                        <label class="form-label" for="date">Tanggal</label>
                        <div class="input-group" id="kt_td_picker_date_only" data-td-target-input="nearest"
                            data-td-target-toggle="nearest">
                            <input id="kt_td_picker_date_only_input" type="text" class="form-control"
                                data-td-target="#kt_td_picker_date_only" name="date" value="{{ date('Y-m-d') }}"
                                readonly>
                            <!-- Set the value to today's date and make it readonly -->
                            <span class="input-group-text" data-td-target="#kt_td_picker_date_only"
                                data-td-toggle="datetimepicker">
                                <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span
                                        class="path2"></span></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="invoice">Faktur</label>
                        <input name="invoice" class="form-control" type="text" value="{{ $invoice }}" readonly>
                    </div>

                    @if(auth()->user()->hasRole('master') && isset($warehouses) && count($warehouses) > 0)
                    <div class="mb-4">
                        <label class="form-label" for="warehouse_id">Cabang</label>
                        <select name="warehouse_id" class="form-select form-select-solid" data-control="select2"
                            data-dropdown-parent="#kt_modal_1">
                            <option value="">Pilih Cabang</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label" for="type">Tipe</label>
                        <select name="type" class="form-select form-select-solid" data-control="select2" id="typeSelect"
                            data-dropdown-parent="#kt_modal_1">
                            <option value="">Pilih Tipe</option>
                            <option value="Kas Masuk">Kas Masuk</option>
                            <option value="Kas Keluar">Kas Keluar</option>
                        </select>
                    </div>

                    <div id="incomeItemContainer" class="mb-4" style="display: none;">
                        <label class="form-label" for="kas_income_item_id">Item Pendapatan</label>
                        <select id="kas_income_item_id" name="kas_income_item_id" class="form-select form-select-solid"
                            data-control="select2" data-dropdown-parent="#kt_modal_1">
                            <option value="">Pilih Item Pendapatan</option>
                            @foreach($incomeItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="expenseItemContainer" class="mb-4" style="display: none;">
                        <label class="form-label" for="kas_expense_item_id">Item Pengeluaran</label>
                        <select id="kas_expense_item_id" name="kas_expense_item_id"
                            class="form-select form-select-solid" data-control="select2"
                            data-dropdown-parent="#kt_modal_1">
                            <option value="">Pilih Item Pengeluaran</option>
                            @foreach($expenseItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="amount">Jumlah</label>
                        <input name="amount" type="number" class="form-control" placeholder="Masukan jumlah" />
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="description">Deskripsi</label>
                        <input name="description" type="text" class="form-control" placeholder="Masukan deskripsi" />
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>