<?php

namespace App\Services\Concerns;

trait CastsJsonb
{
    /**
     * PostgreSQL's pdo_pgsql driver does not automatically encode PHP arrays
     * as JSON strings when binding to JSONB columns. This explicitly serialises
     * any array values that target a JSONB column before Eloquent builds the
     * UPDATE or INSERT statement.
     *
     * Call this on $data before any create() or update() that includes
     * JSONB columns.
     *
     * @param  array<string>  $columns  The JSONB column names present in $data
     */
    protected function encodeJsonbColumns(array $data, array $columns): array
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $data)) {
                $data[$column] = is_array($data[$column])
                    ? json_encode($data[$column], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
                    : ($data[$column] ?? '{}');
            }
        }

        return $data;
    }
}