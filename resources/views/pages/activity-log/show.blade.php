@extends('layouts.dashboard')

@section('title', 'Activity Log Details')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-1 card-label fw-bold fs-3">Activity Log Details</span>
                <span class="mt-1 text-muted fw-semibold fs-7">Detailed information about this activity</span>
            </h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('activity-log.index') }}" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-arrow-left fs-2"></i>Back to List
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-6 row">
            <div class="col-lg-6">
                <div class="mb-8 text-gray-600 fw-semibold fs-6">Basic Information</div>

                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">Date & Time</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{
                            \Carbon\Carbon::parse($activity->created_at)->format('d M Y H:i:s') }}</span>
                    </div>
                </div>

                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">User</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{ $activity->causer_name }}</span>
                    </div>
                </div>

                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">Activity</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{ $activity->description }}</span>
                    </div>
                </div>

                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">Category</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{ $activity->log_name }}</span>
                    </div>
                </div>

                @if($activity->subject)
                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">Subject Type</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{ class_basename($activity->subject_type) }}</span>
                    </div>
                </div>

                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">Subject ID</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{ $activity->subject_id }}</span>
                    </div>
                </div>

                <div class="mb-6 row">
                    <label class="col-lg-4 fw-semibold text-muted">Subject Name</label>
                    <div class="col-lg-8">
                        <span class="text-gray-800 fw-bold fs-6">{{ $activity->subject_name }}</span>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-6">
                <div class="mb-8 text-gray-600 fw-semibold fs-6">Changed Properties</div>

                @if(isset($activity->formatted_properties['old_values']) &&
                count($activity->formatted_properties['old_values']) > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th>Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activity->formatted_properties['old_values'] as $key => $oldValue)
                            @php
                            $newValue = $activity->formatted_properties['new_values'][$key] ?? null;
                            $oldDisplayValue = is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue) :
                            $oldValue;
                            $newDisplayValue = is_array($newValue) || is_object($newValue) ? json_encode($newValue) :
                            $newValue;

                            // Format boolean values
                            if (is_bool($oldValue)) {
                            $oldDisplayValue = $oldValue ? 'Yes' : 'No';
                            }
                            if (is_bool($newValue)) {
                            $newDisplayValue = $newValue ? 'Yes' : 'No';
                            }

                            // Handle null values
                            $oldDisplayValue = $oldDisplayValue ?? 'NULL';
                            $newDisplayValue = $newDisplayValue ?? 'NULL';
                            @endphp

                            @if($oldDisplayValue != $newDisplayValue)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                <td>
                                    <span class="text-danger">{{ $oldDisplayValue }}</span>
                                </td>
                                <td>
                                    <span class="text-success">{{ $newDisplayValue }}</span>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif(isset($activity->formatted_properties['new_values']) &&
                count($activity->formatted_properties['new_values']) > 0 &&
                !isset($activity->formatted_properties['old_values']))
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activity->formatted_properties['new_values'] as $key => $value)
                            @php
                            $displayValue = is_array($value) || is_object($value) ? json_encode($value) : $value;

                            // Format boolean values
                            if (is_bool($value)) {
                            $displayValue = $value ? 'Yes' : 'No';
                            }

                            // Handle null values
                            $displayValue = $displayValue ?? 'NULL';
                            @endphp

                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                <td>{{ $displayValue }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-primary">
                    No property changes were recorded for this activity.
                </div>
                @endif

                @if(count($activity->formatted_properties) > 0 &&
                (array_key_exists('old_values', $activity->formatted_properties) === false &&
                array_key_exists('new_values', $activity->formatted_properties) === false))
                <div class="mt-8 mb-8 text-gray-600 fw-semibold fs-6">Additional Properties</div>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th>Property</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activity->formatted_properties as $key => $value)
                            @if(!in_array($key, ['old_values', 'new_values']))
                            @php
                            $displayValue = is_array($value) || is_object($value) ? json_encode($value) : $value;

                            // Format boolean values
                            if (is_bool($value)) {
                            $displayValue = $value ? 'Yes' : 'No';
                            }

                            // Handle null values
                            $displayValue = $displayValue ?? 'NULL';
                            @endphp

                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                <td>{{ $displayValue }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-end">
            @can('hapus activity log')
            <form action="{{ route('activity-log.destroy', $activity->id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this activity log?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
