<x-layouts.app title="Log Audit - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Log Audit</h1>
            <p class="text-xs text-gray-500 mt-0.5">Jejak otomatis seluruh aktivitas aplikasi: model, antrean, worker, email, dan penjadwalan.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <form method="GET" action="{{ route('audit-log.index') }}" class="px-4 py-3 border-b border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div class="lg:col-span-2">
                <label for="q" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Event, subjek, atau aktor" class="ui-input focus-ring">
            </div>

            <div>
                <label for="category" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Kategori</label>
                <select id="category" name="category" class="ui-select focus-ring">
                    <option value="">Semua</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="event" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Event</label>
                <select id="event" name="event" class="ui-select focus-ring">
                    <option value="">Semua</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Status</label>
                <select id="status" name="status" class="ui-select focus-ring">
                    <option value="">Semua</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="severity" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Severity</label>
                <select id="severity" name="severity" class="ui-select focus-ring">
                    <option value="">Semua</option>
                    @foreach ($severities as $severity)
                        <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ $severity }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-6 flex flex-wrap items-center gap-3">
                <div>
                    <label for="from" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Dari tanggal</label>
                    <input id="from" type="date" name="from" value="{{ request('from') }}" class="ui-input focus-ring">
                </div>
                <div>
                    <label for="to" class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Sampai tanggal</label>
                    <input id="to" type="date" name="to" value="{{ request('to') }}" class="ui-input focus-ring">
                </div>
                <div class="flex gap-2 mt-4">
                    <button type="submit" class="ui-btn ui-btn-primary">Filter</button>
                    <a href="{{ route('audit-log.index') }}" class="ui-btn ui-btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-40">Waktu</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Kategori</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Event</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Subjek</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-40">Aktor</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Status</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Severity</th>
                        <th class="w-16 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100 align-top">
                            <td class="px-3 py-2 text-xs text-gray-500 font-mono tabular-nums whitespace-nowrap">
                                {{ $log->created_at?->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-paper text-ink-2 border border-line font-medium">
                                    {{ $log->category }}
                                </span>
                            </td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-700 break-all">{{ $log->event }}</td>
                            <td class="px-3 py-2 text-xs text-gray-700 break-words max-w-xs">{{ $log->subject }}</td>
                            <td class="px-3 py-2 text-xs text-gray-600">
                                {{ $log->actor_name ?? 'Sistem' }}
                                @if ($log->actor_id)
                                    <span class="block text-[10px] text-gray-400 font-mono">#{{ $log->actor_id }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <x-ui.badge :tone="match ($log->status) {
                                    'success', 'processed', 'sent', 'finished', 'created', 'updated', 'deleted' => 'success',
                                    'failed', 'timed_out', 'locked', 'exception' => 'danger',
                                    'processing', 'queued', 'sending', 'started', 'retry' => 'info',
                                    default => 'neutral',
                                }">{{ $log->status ?? '-' }}</x-ui.badge>
                            </td>
                            <td class="px-3 py-2">
                                <x-ui.badge :tone="match ($log->severity) {
                                    'error' => 'danger',
                                    'warning', 'notice' => 'warning',
                                    default => 'info',
                                }">{{ $log->severity }}</x-ui.badge>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a
                                    href="{{ route('audit-log.show', $log) }}"
                                    class="p-1.5 rounded text-primary/70 hover:text-primary hover:bg-primary/10 transition-colors ease-out duration-150 inline-flex"
                                    title="Lihat detail"
                                    aria-label="Lihat detail log {{ $log->id }}"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h1m4 0h1M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Belum ada log audit</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Log akan muncul otomatis saat aktivitas aplikasi berjalan.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
