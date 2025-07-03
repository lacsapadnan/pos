<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:baca activity log');
        $this->middleware('permission:hapus activity log')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $users = User::all();

        return view('pages.activity-log.index', compact('users'));
    }

    public function data(Request $request)
    {
        $query = Activity::with('causer', 'subject');

        // Apply search if provided
        if (!empty($request->search) && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('description', 'LIKE', "%{$searchValue}%")
                    ->orWhere('log_name', 'LIKE', "%{$searchValue}%")
                    ->orWhereHas('causer', function ($q) use ($searchValue) {
                        $q->where('name', 'LIKE', "%{$searchValue}%");
                    })
                    ->orWhere('subject_type', 'LIKE', "%{$searchValue}%");
            });
        }

        // Filter by log name (category)
        if ($request->has('log_name') && $request->log_name) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by causer (user)
        if ($request->has('causer_id') && $request->causer_id) {
            $query->where('causer_id', $request->causer_id);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by subject type (model)
        if ($request->has('subject_type') && $request->subject_type) {
            $query->where('subject_type', 'LIKE', '%' . $request->subject_type . '%');
        }

        // If export parameter is set, return all data without pagination
        if ($request->has('export') && $request->export) {
            $activities = $query->get();
            return response()->json($activities);
        }

        return DataTables::of($query)
            ->addColumn('formatted_date', function ($activity) {
                return Carbon::parse($activity->created_at)->format('d M Y H:i:s');
            })
            ->addColumn('causer_name', function ($activity) {
                return $activity->causer ? $activity->causer->name : 'System';
            })
            ->addColumn('subject_name', function ($activity) {
                return $this->getSubjectName($activity);
            })
            ->addColumn('action', function ($activity) {
                return view('pages.activity-log.partials.action-buttons', compact('activity'))->render();
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->filterColumn('causer_name', function ($query, $keyword) {
                $query->whereHas('causer', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $activity = Activity::with('causer', 'subject')->findOrFail($id);

        $activity->formatted_properties = $this->formatProperties($activity->properties);
        $activity->causer_name = $activity->causer ? $activity->causer->name : 'System';
        $activity->subject_name = $this->getSubjectName($activity);

        return view('pages.activity-log.show', compact('activity'));
    }

    public function destroy($id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $activity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Activity log deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete activity log: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLogNames()
    {
        $logNames = Activity::select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name')
            ->toArray();

        return response()->json($logNames);
    }

    public function getSubjectTypes()
    {
        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                // Convert full namespace to just the class name
                $parts = explode('\\', $type);
                return end($parts);
            })
            ->toArray();

        return response()->json($subjectTypes);
    }

    /**
     * Format properties for better display
     */
    private function formatProperties($properties)
    {
        if (!$properties) {
            return [];
        }

        $result = [];

        // Handle attributes and old values
        if ($properties->has('attributes')) {
            $result['new_values'] = $properties->get('attributes');
        }

        if ($properties->has('old')) {
            $result['old_values'] = $properties->get('old');
        }

        // Add any custom properties
        foreach ($properties as $key => $value) {
            if (!in_array($key, ['attributes', 'old'])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get a readable name for the subject
     */
    private function getSubjectName($activity)
    {
        if (!$activity->subject) {
            return 'N/A';
        }

        // Try common name fields
        $nameFields = ['name', 'title', 'order_number', 'invoice', 'email', 'advance_number'];

        foreach ($nameFields as $field) {
            if (isset($activity->subject->$field)) {
                return $activity->subject->$field;
            }
        }

        // Fallback to ID
        return get_class($activity->subject) . ' #' . $activity->subject->id;
    }
}
