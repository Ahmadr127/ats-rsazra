@props(['applications', 'filters' => []])

<div class="bg-white rounded-xl p-6 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-sm font-semibold text-gray-800">Daftar Pengajuan</h2>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] text-gray-400">
                @if ($applications->isEmpty())
                    Menampilkan 0 pengajuan
                @else
                    Menampilkan {{ $applications->firstItem() }}–{{ $applications->lastItem() }} dari {{ number_format($applications->total()) }} pengajuan
                @endif
            </span>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                @foreach (['date_from', 'date_to', 'unit_id', 'vacancy_id'] as $key)
                    @if (! empty($filters[$key]))
                        <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
                    @endif
                @endforeach
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari nama, posisi, atau unit..."
                    class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 w-56 focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
                <select
                    name="per_page"
                    onchange="this.form.submit()"
                    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-primary/30"
                    title="Jumlah data per halaman"
                >
                    @foreach ([10, 15, 25, 50] as $option)
                        <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? 15) === $option)>{{ $option }} / halaman</option>
                    @endforeach
                </select>
                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-primary text-white font-medium hover:bg-primary/90">Cari</button>
                @if (! empty($filters['search']))
                    <a href="{{ route('dashboard', array_filter($filters, fn ($key) => $key !== 'search', ARRAY_FILTER_USE_KEY)) }}" class="text-xs text-gray-500 hover:text-primary">Reset</a>
                @endif
            </form>
        </div>
    </div>

    @if ($applications->isEmpty())
        <p class="text-sm text-gray-400 text-center py-6">Belum ada data lamaran.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse border border-gray-200">
                <thead>
                    <tr>
                        <th class="text-left font-semibold text-gray-600 bg-gray-50 border border-gray-200 px-3 py-2">Nama</th>
                        <th class="text-left font-semibold text-gray-600 bg-gray-50 border border-gray-200 px-3 py-2">Posisi</th>
                        <th class="text-left font-semibold text-gray-600 bg-gray-50 border border-gray-200 px-3 py-2">Unit</th>
                        <th class="text-left font-semibold text-gray-600 bg-gray-50 border border-gray-200 px-3 py-2">Pipeline Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                        @php
                            $stages = $application->stages;
                            $current = $application->currentStage();
                            $totalStages = $stages->count();
                            $completed = $stages->where('status', \App\Enums\ApplicationStageStatus::Selesai)->count();
                            $percent = $totalStages > 0 ? (int) round(($completed / $totalStages) * 100) : 0;
                            $tone = match ($current?->status) {
                                \App\Enums\ApplicationStageStatus::Gagal => 'bg-red-50 text-red-600',
                                \App\Enums\ApplicationStageStatus::Reserved => 'bg-amber-50 text-amber-700',
                                \App\Enums\ApplicationStageStatus::Aktif => 'bg-primary/10 text-primary',
                                \App\Enums\ApplicationStageStatus::Selesai => 'bg-green-50 text-green-700',
                                default => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="border border-gray-200 px-3 py-2.5 font-medium text-gray-800 whitespace-nowrap">
                                {{ $application->candidate?->nama_lengkap ?? '—' }}
                            </td>
                            <td class="border border-gray-200 px-3 py-2.5 text-gray-600 max-w-[180px] truncate" title="{{ $application->vacancy?->judul_posisi ?? '' }}">
                                {{ $application->vacancy?->judul_posisi ?? '—' }}
                            </td>
                            <td class="border border-gray-200 px-3 py-2.5 text-gray-500 max-w-[140px] truncate">
                                {{ $application->vacancy?->unit?->nama ?? '—' }}
                            </td>
                            <td class="border border-gray-200 px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 shrink-0 bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-full bg-primary/80 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full {{ $tone }} font-medium">
                                        {{ $current?->nama ?? '—' }}
                                    </span>
                                    <span class="text-gray-400 shrink-0">{{ $completed }}/{{ $totalStages }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($applications->hasPages())
            <div class="mt-4 border-t border-gray-100 pt-3">
                {{ $applications->links() }}
            </div>
        @endif
    @endif
</div>
