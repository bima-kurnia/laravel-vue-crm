<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class TenantAwareFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        // Pull the shared context Laravel injected via Log::withContext()
        $extra = $record->extra;
        $context = $record->context;

        $structured = [
            'timestamp'       => $record->datetime->format('Y-m-d\TH:i:s.up'),
            'level'           => strtolower($record->level->name),
            'message'         => $record->message,

            // Tenant / request identity — from withContext()
            'tenant_id'       => $context['tenant_id'] ?? $extra['tenant_id']       ?? null,
            'user_id'         => $context['user_id'] ?? $extra['user_id']         ?? null,
            'user_role'       => $context['user_role'] ?? $extra['user_role']        ?? null,
            'request_id'      => $context['request_id'] ?? $extra['request_id']      ?? null,
            'response_status' => $context['response_status'] ?? $extra['response_status'] ?? null,

            // Request metadata
            'method'          => $context['method'] ?? null,
            'path'            => $context['path'] ?? null,
            'ip'              => $context['ip'] ?? null,

            // Caller-supplied context (everything not in the above keys)
            'context'         => $this->filterKnownKeys($context),

            'channel'         => $record->channel,
            'environment'     => app()->environment(),
        ];

        // Remove nulls to keep log entries lean
        $structured = array_filter($structured, fn($v) => $v !== null);

        return json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }

    private function filterKnownKeys(array $context): array
    {
        $known = [
            'tenant_id', 'user_id', 'user_role', 'request_id',
            'response_status', 'method', 'path', 'ip',
        ];

        $filtered = array_diff_key($context, array_flip($known));

        return empty($filtered) ? [] : $filtered;
    }
}