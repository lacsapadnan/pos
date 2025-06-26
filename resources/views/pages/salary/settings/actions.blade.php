<!--begin::Action=-->
<div class="gap-2 d-flex align-items-center">
    <!--begin::Edit-->
    <a href="{{ route('salary-settings.edit', $row) }}" class="btn btn-sm btn-primary">
        <i class="ki-duotone ki-notepad-edit fs-5">
            <i class="path1"></i>
            <i class="path2"></i>
        </i>
        Edit
    </a>
    <!--end::Edit-->

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
</div>
<!--end::Action=-->
