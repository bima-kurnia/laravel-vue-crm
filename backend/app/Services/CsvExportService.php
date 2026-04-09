<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * Stream a CSV of customers matching the current filters.
     * Applies the same filter logic as CustomerService::list()
     * so exported data always matches what the user sees on screen.
     */
    public function exportCustomers(array $filters): StreamedResponse
    {
        $query = Customer::query();

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name',    'ilike', $term)
                  ->orWhere('email',   'ilike', $term)
                  ->orWhere('company', 'ilike', $term);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['company'])) {
            $query->where('company', 'ilike', '%' . $filters['company'] . '%');
        }

        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $filename = 'customers-' . now()->format('Y-m-d') . '.csv';

        return $this->stream($filename, function () use ($query) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM — makes Excel open the file correctly
            fprintf($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Name', 'Email', 'Phone',
                'Company', 'Status', 'Created At',
            ]);

            // Chunk to avoid loading all rows into memory at once
            $query->chunk(500, function ($customers) use ($out) {
                foreach ($customers as $c) {
                    fputcsv($out, [
                        $c->id,
                        $c->name,
                        $c->email   ?? '',
                        $c->phone   ?? '',
                        $c->company ?? '',
                        $c->status,
                        $c->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($out);
        });
    }

    /**
     * Stream a CSV of deals matching the current filters.
     */
    public function exportDeals(array $filters): StreamedResponse
    {
        $query = Deal::query()->with(['customer:id,name', 'owner:id,name']);

        if (! empty($filters['search'])) {
            $query->where('title', 'ilike', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        if (isset($filters['value_min'])) {
            $query->where('value', '>=', $filters['value_min']);
        }

        if (isset($filters['value_max'])) {
            $query->where('value', '<=', $filters['value_max']);
        }

        if (! empty($filters['close_date_from'])) {
            $query->whereDate('expected_close_date', '>=', $filters['close_date_from']);
        }

        if (! empty($filters['close_date_to'])) {
            $query->whereDate('expected_close_date', '<=', $filters['close_date_to']);
        }

        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $filename = 'deals-' . now()->format('Y-m-d') . '.csv';

        return $this->stream($filename, function () use ($query) {
            $out = fopen('php://output', 'w');

            fprintf($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Title', 'Customer', 'Owner',
                'Value', 'Currency', 'Status', 'Stage',
                'Expected Close Date', 'Created At',
            ]);

            $query->chunk(500, function ($deals) use ($out) {
                foreach ($deals as $d) {
                    fputcsv($out, [
                        $d->id,
                        $d->title,
                        $d->customer?->name ?? '',
                        $d->owner?->name    ?? '',
                        $d->value,
                        $d->currency,
                        $d->status,
                        $d->stage,
                        $d->expected_close_date?->toDateString() ?? '',
                        $d->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($out);
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function stream(string $filename, callable $callback): StreamedResponse
    {
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'X-Accel-Buffering'   => 'no', // disables Nginx proxy buffering
        ]);
    }
}