<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Central writer for the audit_logs event stream.
 *
 * Every listener funnels through record(); the writer is fail-safe so an audit
 * problem can never break the application request or queue job.
 */
final class AuditLogger
{
    private static bool $writing = false;

    /** @var array<string, float> */
    private static array $jobTimers = [];

    /** @var array<int, float> */
    private static array $mailTimers = [];

    /**
     * Template key of the mailable currently being processed by the queue, so
     * mail events can be linked back to the template that produced them.
     */
    private static ?string $mailableContext = null;

    public static function enabled(): bool
    {
        return (bool) config('audit.enabled', true) && ! self::$writing;
    }

    /**
     * Write one audit row.
     *
     * @param  array<string, mixed>  $data
     */
    public static function record(string $category, string $event, array $data = []): ?AuditLog
    {
        if (! self::enabled()) {
            return null;
        }

        self::$writing = true;

        try {
            return AuditLog::withoutEvents(fn () => AuditLog::create([
                'category' => Str::limit($category, 50, ''),
                'event' => Str::limit($event, 255, ''),
                'status' => isset($data['status']) ? Str::limit((string) $data['status'], 50, '') : null,
                'severity' => Str::limit((string) ($data['severity'] ?? 'info'), 20, ''),
                'auditable_type' => isset($data['auditable_type']) ? Str::limit((string) $data['auditable_type'], 191, '') : null,
                'auditable_id' => self::numericId($data['auditable_id'] ?? null),
                'subject' => isset($data['subject']) ? Str::limit((string) $data['subject'], 500, '') : null,
                'actor_id' => self::numericId($data['actor_id'] ?? self::actorId()),
                'actor_name' => isset($data['actor_name'])
                    ? Str::limit((string) $data['actor_name'], 191, '')
                    : Str::limit((string) (self::actorName() ?? ''), 191, ''),
                'ip' => isset($data['ip']) ? Str::limit((string) $data['ip'], 45, '') : Str::limit((string) (self::ip() ?? ''), 45, ''),
                'request_id' => isset($data['request_id']) ? Str::limit((string) $data['request_id'], 191, '') : self::requestId(),
                'method' => isset($data['method']) ? Str::limit((string) $data['method'], 10, '') : null,
                'url' => isset($data['url']) ? Str::limit((string) $data['url'], 500, '') : null,
                'old_values' => self::jsonable($data['old_values'] ?? null),
                'new_values' => self::jsonable($data['new_values'] ?? null),
                'context' => self::jsonable($data['context'] ?? null),
                'duration_ms' => isset($data['duration_ms']) ? (int) $data['duration_ms'] : null,
                'created_at' => now(),
            ]));
        } catch (Throwable $e) {
            Log::error('Audit log write failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return null;
        } finally {
            self::$writing = false;
        }
    }

    /**
     * Record an Eloquent model change.
     *
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function model(string $action, Model $model, ?array $old = null, ?array $new = null, ?string $status = null, string $severity = 'info'): ?AuditLog
    {
        return self::record('model', 'eloquent.'.$action.': '.get_class($model), [
            'status' => $status ?? $action,
            'severity' => $severity,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'subject' => self::modelLabel($model),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public static function startJobTimer(string $key): void
    {
        self::$jobTimers[$key] = microtime(true);
    }

    public static function jobDuration(string $key): ?int
    {
        if (! isset(self::$jobTimers[$key])) {
            return null;
        }

        $duration = (int) round((microtime(true) - self::$jobTimers[$key]) * 1000);
        unset(self::$jobTimers[$key]);

        return $duration;
    }

    public static function startMailTimer(int $key): void
    {
        self::$mailTimers[$key] = microtime(true);
    }

    public static function mailDuration(int $key): ?int
    {
        if (! isset(self::$mailTimers[$key])) {
            return null;
        }

        $duration = (int) round((microtime(true) - self::$mailTimers[$key]) * 1000);
        unset(self::$mailTimers[$key]);

        return $duration;
    }

    public static function setMailableContext(?string $key): void
    {
        self::$mailableContext = $key;
    }

    public static function mailableContext(): ?string
    {
        return self::$mailableContext;
    }

    private static function numericId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private static function actorId(): ?int
    {
        $id = Auth::id();

        return is_numeric($id) ? (int) $id : null;
    }

    private static function actorName(): ?string
    {
        return Auth::user()?->name;
    }

    private static function ip(): ?string
    {
        return request()?->ip();
    }

    private static function requestId(): ?string
    {
        return app()->bound('request.id') ? (string) app('request.id') : null;
    }

    private static function modelLabel(Model $model): string
    {
        $key = $model->getKey();
        $label = class_basename($model).($key !== null ? ' #'.$key : '');

        $attributes = $model->getAttributes();
        $name = $attributes['name'] ?? $attributes['username'] ?? $attributes['label'] ?? null;

        if (is_string($name) && $name !== '') {
            $label .= ' · '.$name;
        }

        return $label;
    }

    /**
     * Normalize any value for JSON storage: redact sensitive keys, truncate
     * strings, and convert objects/models into plain arrays.
     */
    private static function normalize(mixed $value, ?string $key = null, int $depth = 0): mixed
    {
        if ($key !== null && self::isSensitive($key)) {
            return '[redacted]';
        }

        if ($depth > 5) {
            return '[max depth]';
        }

        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return self::truncate($value);
        }

        if ($value instanceof Model) {
            return [
                'model' => get_class($value),
                'id' => $value->getKey(),
            ];
        }

        if ($value instanceof Throwable) {
            return [
                'class' => get_class($value),
                'message' => self::truncate($value->getMessage()),
                'code' => $value->getCode(),
            ];
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }

        if ($value instanceof \UnitEnum) {
            return $value instanceof \BackedEnum ? $value->value : $value->name;
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $itemKey => $item) {
                $normalized[$itemKey] = self::normalize(
                    $item,
                    is_string($itemKey) ? $itemKey : null,
                    $depth + 1
                );
            }

            return $normalized;
        }

        if (is_object($value)) {
            if ($value instanceof \JsonSerializable) {
                return self::normalize($value->jsonSerialize(), null, $depth + 1);
            }

            if (method_exists($value, 'toArray')) {
                return self::normalize($value->toArray(), null, $depth + 1);
            }

            if ($value instanceof \Stringable || method_exists($value, '__toString')) {
                return self::truncate((string) $value);
            }

            return ['class' => get_class($value)];
        }

        if (is_resource($value)) {
            return '[resource]';
        }

        return null;
    }

    private static function jsonable(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $normalized = self::normalize($value);

        if ($normalized === null || $normalized === []) {
            return null;
        }

        $encoded = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($encoded === false) {
            return ['invalid' => true];
        }

        $max = (int) config('audit.max_json_length', 50000);

        if (strlen($encoded) > $max) {
            return [
                'truncated' => true,
                'size' => strlen($encoded),
                'preview' => Str::limit($encoded, $max, ''),
            ];
        }

        return $normalized;
    }

    private static function isSensitive(string $key): bool
    {
        $key = strtolower($key);

        foreach ((array) config('audit.redacted_fields', []) as $field) {
            $field = strtolower((string) $field);

            if ($field !== '' && str_contains($key, $field)) {
                return true;
            }
        }

        return false;
    }

    private static function truncate(string $value): string
    {
        return Str::limit($value, (int) config('audit.max_string_length', 5000), '');
    }
}
