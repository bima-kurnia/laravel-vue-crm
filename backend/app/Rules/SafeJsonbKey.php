<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeJsonbKey implements ValidationRule
{
    /**
     * Forbidden patterns in JSONB keys:
     *
     * - Dots (.)        — PostgreSQL path operator: data->'a.b' vs data->'a'->'b'
     * - Dollar signs ($) — MongoDB-style operators leaked into some JSONB libs
     * - Spaces           — Break unquoted path expressions
     * - Null bytes       — Terminate strings early in C-level pg functions
     * - Backticks        — Prevent query template injection in raw expressions
     * - Single/double quotes — Break out of quoted identifiers
     */
    private const FORBIDDEN_PATTERN = '/[\.\$\s\x00`\'"]/u';

    /**
     * Maximum key length — prevents index bloat on GIN-indexed JSONB columns.
     */
    private const MAX_LENGTH = 64;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return; // type validation is handled by the 'array' rule
        }

        foreach ($value as $key => $fieldValue) {
            $key = (string) $key;

            if ($key === '') {
                $fail("Custom field keys cannot be empty.");
                return;
            }

            if (strlen($key) > self::MAX_LENGTH) {
                $fail("Custom field key \"{$key}\" exceeds the maximum length of " . self::MAX_LENGTH . " characters.");
                return;
            }

            if (preg_match(self::FORBIDDEN_PATTERN, $key)) {
                $fail("Custom field key \"{$key}\" contains invalid characters. Keys may not contain dots, dollar signs, spaces, or quotes.");
                return;
            }
        }
    }
}