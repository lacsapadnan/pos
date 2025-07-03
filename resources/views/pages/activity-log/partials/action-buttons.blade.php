<div class="flex-shrink-0 d-flex justify-content-end">
    <a href="/activity-log/{{ $activity->id }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
        title="View Details">
        <i class="ki-duotone ki-eye fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </a>
    @can('hapus activity log')
    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-activity"
        data-id="{{ $activity->id }}" title="Delete">
        <i class="ki-duotone ki-trash fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </button>
    @endcan
</div>
