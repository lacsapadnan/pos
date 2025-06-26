<!--begin::Action=-->
<div class="d-flex align-items-center gap-2">
    @if($row->status == 'draft')
    <!--begin::Edit-->
    <a href="{{ route('gaji.edit', $row) }}" class="btn btn-sm btn-primary">
        <i class="ki-duotone ki-notepad-edit fs-5">
            <i class="path1"></i>
            <i class="path2"></i>
        </i>
        Edit
    </a>
    <!--end::Edit-->

    <!--begin::Calculate-->
    <form action="{{ route('gaji.calculate', $row) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-info">
            <i class="ki-duotone ki-calculator fs-5">
                <i class="path1"></i>
                <i class="path2"></i>
            </i>
            Hitung
        </button>
    </form>
    <!--end::Calculate-->

    <!--begin::Delete-->
    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $row->id }}"
        data-employee="{{ $row->employee->name }}">
        <i class="ki-duotone ki-trash fs-5">
            <i class="path1"></i>
            <i class="path2"></i>
            <i class="path3"></i>
            <i class="path4"></i>
            <i class="path5"></i>
        </i>
        Hapus
    </button>
    <!--end::Delete-->
    @elseif($row->status == 'calculated')
    <!--begin::Approve-->
    <form action="{{ route('gaji.approve', $row) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success">
            <i class="ki-duotone ki-check fs-5">
                <i class="path1"></i>
                <i class="path2"></i>
            </i>
            Setujui
        </button>
    </form>
    <!--end::Approve-->
    @elseif($row->status == 'approved')
    <!--begin::Mark Paid-->
    <form action="{{ route('gaji.mark-paid', $row) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="ki-duotone ki-dollar fs-5">
                <i class="path1"></i>
                <i class="path2"></i>
                <i class="path3"></i>
            </i>
            Tandai Dibayar
        </button>
    </form>
    <!--end::Mark Paid-->
    @endif
</div>
<!--end::Action=-->
