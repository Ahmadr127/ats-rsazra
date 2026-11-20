{{-- Ringkasan jadwal wawancara + form ubah jadwal (opsional). --}}
{{-- Variables: $application, $lowongan, $currentStage, $eligibleInterviewers, $allowReschedule (optional). --}}

@php
    $isWawancaraUser = $currentStage->key === \App\Support\InterviewStageMap::USER;
    $allowReschedule = $allowReschedule ?? false;
@endphp

<div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
    <h2 class="text-sm font-semibold text-gray-800 mb-2">Jadwal Wawancara</h2>
    <dl class="space-y-1">
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Waktu</dt>
            <dd class="text-xs text-gray-800">{{ $currentStage->jadwal?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Lokasi</dt>
            <dd class="text-xs text-gray-800">{{ $currentStage->lokasi ?: '-' }}</dd>
        </div>
        @if ($isWawancaraUser && $currentStage->interviewer)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Pewawancara</dt>
                <dd class="text-xs text-gray-800">{{ $currentStage->interviewer->name }}</dd>
            </div>
        @endif
    </dl>
</div>

@if ($allowReschedule && $isWawancaraUser)
    @permission('interview.reschedule')
        <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Ubah Jadwal / Pewawancara
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <form action="{{ route('lowongan.wawancara.jadwal.update', [$lowongan, $application]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal & Waktu <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="jadwal" required
                                value="{{ $currentStage->jadwal?->format('Y-m-d\TH:i') }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                            <input type="text" name="lokasi" required value="{{ $currentStage->lokasi }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Pewawancara</label>
                            <select name="interviewer_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                                <option value="">Tetap sama</option>
                                @foreach ($eligibleInterviewers as $interviewer)
                                    <option value="{{ $interviewer->id }}" @if ($currentStage->interviewer_id == $interviewer->id) selected @endif>
                                        {{ $interviewer->name }} ({{ $interviewer->role?->label ?? 'Tanpa Peran' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors ease-out duration-150 cursor-pointer">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    @endpermission
@endif
