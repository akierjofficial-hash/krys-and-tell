<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminAuditService
{
    private const SECRET_KEYS = ['password', 'password_confirmation', 'current_password', 'remember_token', 'token'];

    public function record(
        ?User $actor,
        string $event,
        Model|string|null $target = null,
        ?string $description = null,
        array $before = [],
        array $after = [],
        ?string $reason = null,
        bool $sensitive = false,
        ?bool $succeeded = true,
    ): ActivityLog {
        $request = request();
        $targetType = $target instanceof Model ? $target->getMorphClass() : ($target ?: null);
        $targetId = $target instanceof Model ? (string) $target->getKey() : null;

        return ActivityLog::create([
            'user_id' => $actor?->id,
            'event' => $event,
            'description' => $description,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'route_name' => $request?->route()?->getName(),
            'url' => $request?->fullUrl() ?? '',
            'method' => $request?->method() ?? 'SYSTEM',
            'status_code' => null,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'properties' => null,
            'before_values' => $this->sanitize($before),
            'after_values' => $this->sanitize($after),
            'reason' => $reason,
            'is_sensitive' => $sensitive,
            'succeeded' => $succeeded,
            'created_at' => now(),
        ]);
    }

    private function sanitize(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), self::SECRET_KEYS, true)) {
                unset($values[$key]);
            } elseif (is_array($value)) {
                $values[$key] = $this->sanitize($value);
            }
        }
        return $values;
    }
}
