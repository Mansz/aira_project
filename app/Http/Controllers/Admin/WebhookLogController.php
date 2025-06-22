<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use App\Exports\WebhookLogsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WebhookLogController extends Controller
{
    /**
     * Display a listing of webhook logs
     */
    public function index(Request $request)
    {
        $query = WebhookLog::query();

        // Filter by source
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in event type or payload
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                  ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        $webhookLogs = $query->orderBy('created_at', 'desc')
                            ->paginate(20);

        return view('admin.webhook-logs.index', [
            'webhookLogs' => $webhookLogs,
            'filters' => $request->all()
        ]);
    }

    /**
     * Display the specified webhook log
     */
    public function show(WebhookLog $webhookLog)
    {
        return view('admin.webhook-logs.show', [
            'webhookLog' => $webhookLog
        ]);
    }

    /**
     * Export webhook logs to Excel
     */
    public function export(Request $request)
    {
        $filename = 'webhook-logs-' . now()->format('Y-m-d-His') . '.xlsx';
        
        return Excel::download(new WebhookLogsExport($request->all()), $filename);
    }
}