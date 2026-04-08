<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use BelongsToTenant, HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'data', 'read_at',
    ];

    protected $hidden = ['tenant_id'];

    protected $casts = [
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function getDataAttribute(mixed $value): array
    {
        if (is_array($value))  {
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        return json_decode($value, true) ?? [];
    }

    public function setDataAttribute(mixed $value): void
    {
        $this->attributes['data'] = is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            : (is_string($value) ? $value : '{}');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
