<x-layouts.app title="Detail Log Audit - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('audit-log.index') }}" class="text-xs text-primary hover:text-primary-dark transition-colors ease-out duration-150">
            &larr; Kembali ke Log Audit
        </a>
    </div>

    <div class="flex items-start justify-between mb-5 gap-4">
        <div class="min-w-0">
            <h1 class="text-xl font-semibold text-gray-900 break-all">{{ $auditLog->event }}</h1>
            <p class="text-xs text-gray-500 mt-0.5 font-mono">#{{ $auditLog->id }} · {{ $auditLog->created_at?->format('d M Y H:i:s') }}</p>
        </div>
        <div class="flex gap-2 shrink-0">
            <x-ui.badge :tone="match ($auditLog->severity) {
                'error' => 'danger',
                'warning', 'notice' => 'warning',
                default => 'info',
            }">{{ $auditLog->severity }}</x-ui.badge>
            <x-ui.badge :tone="$auditLog->status === 'failed' || $auditLog->status === 'timed_out' ? 'danger' : 'success'">
                {{ $auditLog->status ?? '-' }}
            </x-ui.badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Umum</p>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Kategori</dt>
                    <dd class="font-medium text-gray-800">{{ $auditLog->category }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Subjek</dt>
                    <dd class="font-medium text-gray-800 text-right break-words">{{ $auditLog->subject ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Durasi</dt>
                    <dd class="font-medium text-gray-800 tabular-nums">{{ $auditLog->duration_ms !== null ? $auditLog->duration_ms.' ms' : '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Aktor</p>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Nama</dt>
                    <dd class="font-medium text-gray-800">{{ $auditLog->actor_name ?? 'Sistem' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">User ID</dt>
                    <dd class="font-medium text-gray-800 font-mono">{{ $auditLog->actor_id ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">IP</dt>
                    <dd class="font-medium text-gray-800 font-mono">{{ $auditLog->ip ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Permintaan / Objek</p>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Request ID</dt>
                    <dd class="font-medium text-gray-800 font-mono break-all text-right">{{ $auditLog->request_id ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="font-medium text-gray-800 font-mono">{{ $auditLog->method ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Objek</dt>
                    <dd class="font-medium text-gray-800 font-mono break-all text-right">
                        @if ($auditLog->auditable_type)
                            {{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    @foreach ([
        'old_values' => 'Nilai Lama',
        'new_values' => 'Nilai Baru',
        'context' => 'Konteks',
    ] as $field => $label)
        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-4">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ $label }}</p>
            @if (! empty($auditLog->{$field}))
                <pre class="text-xs bg-paper text-ink-2 rounded-lg p-3 overflow-x-auto max-h-96 overflow-y-auto">{{ json_encode($auditLog->{$field}, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <p class="text-xs text-gray-400 italic">Tidak ada data.</p>
            @endif
        </div>
    @endforeach

</x-layouts.app>
