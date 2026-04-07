<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, HasUuids, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'status', 'custom_data',
    ];

    protected $hidden = ['tenant_id'];

    /**
     * Always return an array regardless of what the DB or Eloquent hands us.
     *
     * PostgreSQL JSONB comes back from pdo_pgsql as a JSON-encoded string.
     * Eloquent's model-event pipeline can also call accessors with an already-
     * decoded array in memory. Both cases are handled explicitly here.
     */
    public function getCustomDataAttribute(mixed $value): array
    {
        // Already a PHP array (in-memory Eloquent pipeline)
        if (is_array($value)) {
            return $value;
        }

        // Null / empty string / literal 'null'
        if ($value === null || $value === '' || $value === 'null') {
            return [];
        }

        // Valid JSON string from PostgreSQL — decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Always store a JSON string — never a raw PHP array.
     */
    public function setCustomDataAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['custom_data'] = json_encode(
                $value,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
            return;
        }

        if (is_string($value) && $value !== '') {
            // Already a JSON string (e.g. re-saving without modification) — validate it
            $decoded = json_decode($value, true);
            $this->attributes['custom_data'] = is_array($decoded)
                ? $value          // valid JSON string — store as-is
                : '{}';           // invalid — reset to empty object
            return;
        }

        $this->attributes['custom_data'] = '{}';
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): HasMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
