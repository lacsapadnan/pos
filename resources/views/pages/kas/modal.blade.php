<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah data kas</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form action="{{ route('kas.store') }}" method="post">
                    @csrf
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
                    <div class="mb-4">
                        <label class="form-label" for="invoice">Tipe</label>
                        <select name="type" class="form-select form-select-solid" data-control="select2" id="typeSelect" data-dropdown-parent="#kt_modal_1">
                            <option readonly>Pilih Tipe</option>
                            <option value="Kas Masuk">Kas Masuk</option>
                            <option value="Kas Keluar">Kas Keluar</option>
                        </select>
                    </div>
                    <div id="otherSelectContainer" class="mb-4" style="display: none;">
                        <label class="form-label" for="otherSelect">Keperluan</label>
                        <select id="otherSelect" name="other" class="form-select form-select-solid"
                            data-control="select2" data-dropdown-parent="#kt_modal_1">
                            <option readonly>Pilih Keperluan</option>
                            <option value="">Select an option</option> <!-- Placeholder option -->
                        </select>
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
