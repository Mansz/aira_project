<?php

namespace App\Exports;

use App\Models\WebhookLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WebhookLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Apply filters and return the query
     */
    public function query()
    {
        $query = WebhookLog::query();

        // Apply filters
        if (isset($this->filters['source'])) {
            $query->where('source', $this->filters['source']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        if (isset($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                  ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Column headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Source',
            'Event Type',
            'Status',
            'IP Address',
            'Created At',
            'Processed At',
            'Payload',
            'Response'
        ];
    }

    /**
     * Map the data for export
     */
    public function map($webhookLog): array
    {
        return [
            $webhookLog->id,
            $webhookLog->source,
            $webhookLog->event_type,
            $webhookLog->status,
            $webhookLog->ip_address,
            $webhookLog->created_at->format('Y-m-d H:i:s'),
            $webhookLog->processed_at ? $webhookLog->processed_at->format('Y-m-d H:i:s') : 'Not processed',
            json_encode($webhookLog->payload, JSON_PRETTY_PRINT),
            json_encode($webhookLog->response, JSON_PRETTY_PRINT)
        ];
    }
}