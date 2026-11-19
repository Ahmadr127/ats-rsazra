<?php

use App\Models\AuditLog;

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Log Enabled
    |--------------------------------------------------------------------------
    |
    | When disabled, the application stops writing new audit rows. Tests set
    | this to false so the suite does not pay for audit writes on every test.
    |
    */

    'enabled' => env('AUDIT_LOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Number of days an audit row is kept before `audit:prune` removes it.
    |
    */

    'retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Redaction & Truncation
    |--------------------------------------------------------------------------
    |
    | Keys matching any of these fragments are replaced with "[redacted]" in
    | old_values / new_values / context. Long strings are truncated so a single
    | event cannot blow up the table.
    |
    */

    'redacted_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'remember_token',
        'api_token',
        'api_key',
        'apikey',
        'secret',
        'client_secret',
        'access_token',
        'refresh_token',
        'credit_card',
        'card_number',
        'cvv',
        'nik',
    ],

    'max_string_length' => (int) env('AUDIT_LOG_MAX_STRING_LENGTH', 5000),

    'max_json_length' => (int) env('AUDIT_LOG_MAX_JSON_LENGTH', 50000),

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Models listed here never produce audit rows (used to avoid recursing on
    | the audit table itself).
    |
    */

    'excluded_models' => [
        AuditLog::class,
    ],

];
